<?php

namespace App\Controller;

use DateTime;
use App\Entity\Food;
use Symfony\Component\Uid\Uuid;
use App\Repository\FoodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};

#[Route('api/food', name: 'app_api_food_')]
final class FoodController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager, 
        private FoodRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
        )
        {}

    #[Route('/new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $food = $this->serializer->deserialize($request->getContent(), Food::class, 'json');
        $food->setUuid(Uuid::v4()->toRfc4122());
        $food->setCreatedAt(new DateTime());

        $this->manager->persist($food);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($food, 'json');
        $locatin = $this->urlGenerator->generate('app_api_food_show', ['id' => $food->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(
            $responseData,
            Response::HTTP_CREATED,
            ['Location' => $location],
            true // données déjà en JSON
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if (!$food) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $responseData = $this-serializer->serialize($food, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
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
