<?php

namespace App\Controller;

use DateTime;
use App\Entity\Food;
use Symfony\Component\Uid\Uuid;
use App\Repository\FoodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/food', name: 'app_api_food_')]
final class FoodController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private FoodRepository $repository){}

    #[Route('/new', methods: ['POST'])]
    public function new(): Response
    {
        $food = new Food();
        $food->setTitle('Ravioles au foie gras');
        $food->setDescription("Délicieuses ravioles du Dauphiné avec sauce au foie gras.");
        $food->setPrice(25);
        $food->setUuid(Uuid::v4()->toRfc4122());
        $food->setCreatedAt(new DateTime());

        $this->manager->persist($food);
        $this->manager->flush();


        return $this->json([
            'message' => "Food resource created with ID: {$food->getId()}",
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if (!$food) {
            throw $this->createNotFoundException("No Food found for ID {$id}");
        }

        return $this->json([
            'title' => $food->getTitle(),
            'description' => $food->getDescription(),
            'price' => $food->getPrice(),
            'uuid' => $food->getUuid(),
        ]);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id): Response
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if (!$food) {
            throw $this->createNotFoundException("No Food found for ID {$id}");
        }

        $food->setTitle('Updated food title');
        $food->setUpdatedAt(new DateTime());

        $this->manager->flush();

        return $this->redirectToRoute('app_api_food_show', ['id' => $food->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if (!$food) {
            throw $this->createNotFoundException("No Food found for ID {$id}");
        }

        $this->manager->remove($food);
        $this->manager->flush();

        return $this->json([
            'message' => "Food resource deleted successfully"
        ], Response::HTTP_NO_CONTENT);
    }
}
