<?php

namespace App\Controller;

use DateTime;
use App\Entity\Category;
use Symfony\Component\Uid\Uuid;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/category', name: 'app_api_category_')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager, 
        private CategoryRepository $repository)
    {}

    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(): Response
    {
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json');
        $category->setTitle('Plats principaux');
        $category->setUuid(Uuid::v4()->toRfc4122());
        $category->setCreatedAt(new DateTime());

        $this->manager->persist($category);
        $this->manager->flush();

        return $this->json([
            'message' => "Category created with ID: {$category->getId()}",
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $category = $this->repository->find($id);

        if (!$category) {
            throw $this->createNotFoundException("No Category found for ID {$id}");
        }

        return $this->json([
            'id' => $category->getId(),
            'uuid' => $category->getUuid(),
            'title' => $category->getTitle(),
            'createdAt' => $category->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $category->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id): Response
    {
        $category = $this->repository->find($id);

        if (!$category) {
            throw $this->createNotFoundException("No Category found for ID {$id}");
        }

        $category->setTitle('Catégorie modifiée');
        $category->setUpdatedAt(new DateTime());

        $this->manager->flush();

        return $this->redirectToRoute('app_api_category_show', ['id' => $category->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $category = $this->repository->find($id);

        if (!$category) {
            throw $this->createNotFoundException("No Category found for ID {$id}");
        }

        $this->manager->remove($category);
        $this->manager->flush();

        return $this->json([
            'message' => "Category deleted successfully",
        ], Response::HTTP_NO_CONTENT);
    }
}
