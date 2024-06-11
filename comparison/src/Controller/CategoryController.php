<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends ApiController
{
    public function all(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->transformAll();

        return $this->respond($categories);
    }

    public function create(Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $em): JsonResponse
    {
        $request = $this->transformJsonBody($request);

        if (!$request) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        if (!$request->get('name')) {
            return $this->respondValidationError('Please provide a name!');
        }

        if (!$request->get('description')) {
            return $this->respondValidationError('Please provide a description!');
        }

        $category = new Category();
        $category->setName($request->get('name'));
        $category->setDescription($request->get('description'));

        $em->persist($category);
        $em->flush();

        return $this->respondCreated($categoryRepository->transform($category));
    }

    public function one(CategoryRepository $categoryRepository, $id): JsonResponse
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);

        if (!$category) {
            return $this->respondNotFound();
        }

        $category = $categoryRepository->transform($category);

        return $this->respond($category);
    }
}
