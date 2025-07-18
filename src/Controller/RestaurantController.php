<?php

namespace App\Controller;

use DateTime;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};

#[Route('api/restaurant', name: 'app_api_restaurant_')]
final class RestaurantController extends AbstractController
{   
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {}

    #[Route(name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $restaurant = $this->serializer->deserialize($request->getContent(), Restaurant::class, 'json');
        $restaurant->setCreatedAt(new DateTime());
        $restaurant->setUuid(Uuid::v4()->toRfc4122());

        $this->manager->persist($restaurant);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($restaurant, 'json');
        $location = $this->urlGenerator->generate('app_api_restaurant_show', ['id' => $restaurant->getId()], urlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse(
            $responseData,
            Response::HTTP_CREATED,
            ["Location" => $location]
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if (!$restaurant) {

            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($restaurant, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if (!$restaurant) {
            throw $this->createNotFoundExeception("No Restaurant found for {$id} id");
        }

        // Hydrate partiellement l'objet avec les nouvelles données JSON

        $this->serializer->deserialize(
            $request->getContent(),
            Restaurant::class,
            'json',
            ['object_to_populate' => $restaurant]
        );

        $restaurant->setUpdatedAt(new DateTime());

        $this->manager->flush();

        return new JsonResponse(['message' => "Restaurant updated"], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods:['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $restaurant = $this-> repository->findOneBy(['id' => $id]);
        if (!$restaurant) {
            throw $this->createNotFoundException("No Restaurant found for {$id} id");
        }

        $this->manager->remove($restaurant);
        $this->manager->flush();

        return new JsonResponse(['message' => "Food resource deleted"], Response::HTTP_NOT_CONTENT);
    }

}
