<?php

namespace App\Controller;

use DateTime;
use App\Entity\Category;
use Symfony\Component\Uid\Uuid;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\{JsonResponse, Response, Request};
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/category', name: 'app_api_category_')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager, 
        private CategoryRepository $repository,
        private SerializerInterface $serializer
        ){}

    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json');
        $category->setUuid(Uuid::v4()->toRfc4122());
        $category->setCreatedAt(new DateTime());

        $this->manager->persist($category);
        $this->manager->flush();

        return $this->json([
            'message' =>'Category created successfully',
            'id' => $category->getId(),
            'uuid' => $category->getUuid(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $category = $this->repository->find($id);

        if (!$category) {
            return $this->json(['error' => "No Category found for ID {$id}"], Response::HTTP_NOT_FOUND);
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
    public function edit(int $id, Request $request): JsonResponse
    {
        $category = $this->repository->find($id);

        if (!$category) {
            return $this->json(['error' => "No Category found for ID {$id}"], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $category->setTitle($data['title']);
        }

        $category->setUpdatedAt(new DateTime());

        $this->manager->flush();

        return $this->json([
        'message' => "Category updated successfully",
        'id' => $category->getId()
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $category = $this->repository->find($id);

        if (!$category) {
            return $this->json(['error' => "No Category found for ID {$id}"], Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($category);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
