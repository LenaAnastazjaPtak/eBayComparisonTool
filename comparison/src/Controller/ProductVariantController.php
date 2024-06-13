<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Repository\ProductVariantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProductVariantController extends ApiController
{
    public function all(ProductVariantRepository $productVariantRepository): JsonResponse
    {
        $products = $productVariantRepository->transformAll();

        return $this->respond($products);
    }

    public function create(Request $request, ProductVariantRepository $productVariantRepository, EntityManagerInterface $em): JsonResponse
    {
        $request = $this->transformJsonBody($request);

        if (!$request) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        if (!$request->get('variantCode')) {
            return $this->respondValidationError('Please provide a product variant code!');
        }

        if (!$request->get('color')) {
            return $this->respondValidationError('Please provide a color!');
        }

        if (!$request->get('material')) {
            return $this->respondValidationError('Please provide a material!');
        }

        if (!$request->get('productId')) {
            return $this->respondValidationError('Please provide a product id!');
        }

        $productVariant = new ProductVariant();
        $productVariant->setVariantCode($request->get('variantCode'));
        $productVariant->setColor($request->get('color'));
        $productVariant->setMaterial($request->get('material'));

        $product = $em->getRepository(Product::class)->find((int)$request->get('productId'));
        $productVariant->setProduct($product);

        $em->persist($productVariant);
        $em->flush();

        return $this->respondCreated($productVariantRepository->transform($productVariant));
    }

    public function one(ProductVariantRepository $productVariantRepository, $id, $productVariantCode): JsonResponse
    {
        $productVariant = $productVariantRepository->findOneBy(['id' => $id, 'variantCode' => $productVariantCode]);

        if (!$productVariant) {
            return $this->respondNotFound();
        }

        $productVariant = $productVariantRepository->transform($productVariant);

        return $this->respond($productVariant);
    }
}
