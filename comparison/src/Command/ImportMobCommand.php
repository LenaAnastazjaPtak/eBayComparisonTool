<?php

namespace App\Command;

use App\Entity\File;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\ProductVariantFile;
use App\Service\SupplierService;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ImportMobCommand extends Command
{
    private EntityManagerInterface $em;
    private SupplierService $supplierService;

    public function __construct(
        EntityManagerInterface           $em,
        SupplierService                  $supplierService,
        private readonly KernelInterface $kernel)
    {
        $this->em = $em;
        $this->supplierService = $supplierService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('import:mob')
            ->setDescription('Importuje produkty, warianty, zdjęcia oraz ceny produktów MOB');
    }

    /**
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', "2G");
        set_time_limit(0);
        $em = $this->em;
        $time = new DateTime('now');
        $output->writeln("Start: " . $time->format("d-m-Y H:i:s"));

        $output->writeln('Rozpoczynam import produktów');
        $conn = $em->getConnection();
        $conn->beginTransaction();
        $this->supplierService->connectToMidoceanForFiles(['prodinfo_PL.xml']);

        $files = ['prodinfo_PL.xml'];
        foreach ($files as $file) {
            $output->writeln('Przetwarzanie pliku: ' . $file);
            $xml = simplexml_load_file($this->kernel->getProjectDir() . '/public/import/mob/' . $file);

            $conn->commit();
            $conn->beginTransaction();

            $progress = new ProgressBar($output, count($xml->PRODUCTS[0]->PRODUCT));
            $progress->setFormat('debug');
            foreach ($xml->PRODUCTS[0]->PRODUCT as $midProduct) {
                $progress->advance();
                $product = $em->getRepository(Product::class)
                    ->findOneBy([
                        'productCode' => $midProduct->PRODUCT_BASE_NUMBER . ""
                    ]);

                if (!$product) {
                    $product = new Product();
                    $product = $this->updateProduct($product, $midProduct);

                    $em->persist($product);
                }

                /** @var ProductVariant $variant */
                $variant = $em->getRepository(ProductVariant::class)
                    ->findOneBy(['variantCode' => $midProduct->PRODUCT_NUMBER . ""]);
                if (!$variant) {
                    $variant = new ProductVariant();
                    $variant =
                        $this->updateProductVariant($em, $variant, $product, $midProduct, false,);
                    $em->persist($variant);
                }
            }
            $progress->finish();
            $output->writeln('');
        }
        $output->writeln('>>Flush started');
        $em->flush();
        $output->writeln('>>Flush ended');
        $output->writeln('Zakończono import produktów z systemu MidOceanBrands');

        $output->writeln('Rozpoczynam import cen produktów z systemu MidOceanBrands');
        $array_data = $this->supplierService->connectToMidoceanCurl('urlPricelist');
        $progress = new ProgressBar($output, count($array_data["PRODUCTS"]["PRODUCT"]));
        $progress->setFormat('debug');

        foreach ($array_data["PRODUCTS"]["PRODUCT"] as $product) {
            $progress->advance();
            $this->setPrice($em, $product);
        }
        $progress->finish();
        $output->writeln('>>Flush started');
        $em->flush();
        $output->writeln('>>Flush ended');

//        $this->deleteOldPhotos($em, $output);
//        $output->writeln('>>Flush started');
//        $em->flush();
//        $output->writeln('>>Flush ended');
        $conn->commit();
        $output->writeln('Zakończono import cen produktów z systemu MidOceanBrands');

        return 1;
    }

    private function updateProduct(
        Product                    $product,
        SimpleXMLElement|bool|null $midProduct): Product
    {
        $product->setName($midProduct->SHORT_DESCRIPTION . ' ' . $midProduct->PRODUCT_NAME);
        $product->setProductCode($midProduct->PRODUCT_BASE_NUMBER . "");
        $product->setDescription($midProduct->LONG_DESCRIPTION);
        $product->setWeight(explode(',', $midProduct->NET_WEIGHT)[1]);
        $product->setPriceNetto(0);

        return $product;
    }

    private function updateProductVariant(
        EntityManagerInterface     $em,
        ProductVariant             $variant,
        ?Product                   $product,
        SimpleXMLElement|bool|null $midProduct,
        bool                       $ifVariantExistBefore): ProductVariant
    {
        $allPhotos = $this->em->getRepository(ProductVariantFile::class)->findBy([
            'productVariant' => $variant,
            'toDelete' => false,
        ]);

        foreach ($allPhotos as $photo) {
            $photo->setToDelete(true);
        }

        $variant->setVariantCode(($midProduct->PRODUCT_NUMBER));
        $variant->setColor($midProduct->COLOR_DESCRIPTION);
        $variant->setMaterial($midProduct->MATERIAL_TYPE);

        if (!$ifVariantExistBefore) {
            if (File::checkIfUrlExists($midProduct->IMAGE_URL)) {
                $file = new File($midProduct->IMAGE_URL . "");
                $em->persist($file);

                $productVariantFile = new ProductVariantFile();
                $productVariantFile
                    ->setProductVariant($variant)
                    ->setFile($file)
                    ->setToDelete(false);
                $variant->addProductVariantFile($productVariantFile);

                $em->persist($productVariantFile);
            }
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(ProductVariantFile::class, 'p')
//            ->join('p.file', 'f')
            ->andWhere('p.productVariant = :productVariant')
            ->setParameter('productVariant', $variant->getId());

        $result = $qb->getQuery()->getResult();
        if (!empty($result)) {
            /** @var ProductVariantFile $photo */
            foreach ($result as $photo) {
                $photo->setToDelete(false);
            }
        }

        $variant->setProduct($product);
        $em->persist($variant);

        return $variant;
    }

    private function deleteOldPhotos(
        EntityManagerInterface $em,
        OutputInterface        $output): void
    {
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from(ProductVariantFile::class, 'p')
            ->join('p.file', 'f')
            ->where('p.toDelete = :toDelete')
            ->setParameter('toDelete', 1);
        $result = $qb->getQuery()->getResult();
        foreach ($result as $pictureToDelete) {
            $em->remove($pictureToDelete);
            $em->remove($pictureToDelete->getFile());
            $pictureToDelete->getFile()->deleteFile();
            $output->writeln("Usuwanie " . $pictureToDelete->getFile()->getFilename());
        }
    }

    private function setPrice(EntityManagerInterface $em, mixed $product): void
    {
        /** @var ProductVariant $variant */
        $variant = $em->getRepository(ProductVariant::class)
            ->findOneBy(['variantCode' => $product["PRODUCT_NUMBER"]]);

        if ($variant) {
            $price = floatval(str_replace(',', '.',
                str_replace('.', '', $product["PRICE"])));
            $variant->getProduct()->setPriceNetto($price);
        }
    }
}
