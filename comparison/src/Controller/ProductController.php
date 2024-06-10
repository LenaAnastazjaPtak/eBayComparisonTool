<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function all(EntityManagerInterface $entityManager): JsonResponse
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        $data = [];
        foreach ($products as $product) {
            $data[$product->getId()] = [
                'code' => $product->getProductCode(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPriceNetto(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/product/{id}/{productCode}', name: 'app_product')]
    public function one(EntityManagerInterface $entityManager, $id, $productCode): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $id, 'productCode' => $productCode]);

        $data = [
            'id' => $product->getId(),
            'code' => $product->getProductCode(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPriceNetto(),
        ];

        return new JsonResponse($data);
    }
}
