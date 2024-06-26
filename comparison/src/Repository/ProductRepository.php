<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function transform(Product $product): array
    {
        // Budowanie tablicy z danymi produktu
        $productData = [
            'id' => (int)$product->getId(),
            'code' => (string)$product->getProductCode(),
            'name' => (string)$product->getName(),
            'description' => (string)$product->getDescription(),
            'price' => (float)$product->getPriceNetto(),
            'variants' => []
        ];

        // Iteracja po wariantach produktu
        foreach ($product->getProductVariants() as $variant) {
            $variantData = [
                'id' => $variant->getId(),
                'variantCode' => $variant->getVariantCode(),
                'color' => $variant->getColor(),
                'material' => $variant->getMaterial(),
                'variantFiles' => []
            ];

            // Iteracja po plikach wariantów
            foreach ($variant->getProductVariantFiles() as $variantFile) {
                if (!$variantFile->isToDelete()) {
                    $variantFileData = [
                        'id' => $variantFile->getId(),
                        'filename' => $variantFile->getFile()->getFilepath()
                    ];
                    $variantData['variantFiles'][] = $variantFileData;
                }

                $productData['variants'][] = $variantData;
            }
        }

        // Zwrócenie danych jako JSON
        return $productData;
    }

//    public function transform(object $product): array
//    {
//        return [
//            'id' => (int)$product->getId(),
//            'code' => (string)$product->getProductCode(),
//            'name' => (string)$product->getName(),
//            'description' => (string)$product->getDescription(),
//            'price' => (float)$product->getPriceNetto()
//        ];
//    }

    public function transformAll(): array
    {
        $products = $this->findAll();
        $productsArray = [];

        foreach ($products as $product) {
            $productsArray[] = $this->transform($product);
        }

        return $productsArray;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
