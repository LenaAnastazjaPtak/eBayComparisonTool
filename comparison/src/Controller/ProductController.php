<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends ApiController
{
    public function all(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->transformAll();

        return $this->respond($products);
    }

    public function create(Request $request, ProductRepository $productRepository, EntityManagerInterface $em): JsonResponse
    {
        $request = $this->transformJsonBody($request);

        if (!$request) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        if (!$request->get('productCode')) {
            return $this->respondValidationError('Please provide a product code!');
        }

        if (!$request->get('name')) {
            return $this->respondValidationError('Please provide a name!');
        }

        if (!$request->get('description')) {
            return $this->respondValidationError('Please provide a description!');
        }

        if (!$request->get('priceNetto')) {
            return $this->respondValidationError('Please provide a price in netto!');
        }

        $product = new Product();
        $product->setProductCode($request->get('productCode'));
        $product->setName($request->get('name'));
        $product->setDescription($request->get('description'));
        $product->setPriceNetto($request->get('priceNetto'));

        $em->persist($product);
        $em->flush();

        return $this->respondCreated($productRepository->transform($product));
    }

    public function one(ProductRepository $productRepository, $id, $productCode): JsonResponse
    {
        $product = $productRepository->findOneBy(['id' => $id, 'productCode' => $productCode]);

        if (!$product) {
            return $this->respondNotFound();

        }

        $product = $productRepository->transform($product);

        return $this->respond($product);
    }
}
