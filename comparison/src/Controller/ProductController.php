<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends ApiController
{
    public function all(EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
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

        if (!$request->get('name')) {
            return $this->respondValidationError('Please provide a name!');
        }

        $movie = new Product();
        $movie->setProductCode($request->get('productCode'));
        $movie->setName($request->get('name'));
        $movie->setDescription($request->get('description'));
        $movie->setPriceNetto($request->get('priceNetto'));

        $em->persist($movie);
        $em->flush();

        return $this->respondCreated($productRepository->transform($movie));
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
