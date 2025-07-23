<?php

namespace App\Controller;

use DateTime;
use OpenApi\Attributes as OA;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};

// Les routes comportent des attributs permettant faire des test sur le Bundle Nelmio à l'url suivante : https://127.0.0.1:8000/api/doc afin d'améliorer la documentation. Un template Twig a été générer specifique via la commande : composer require twig asset

#[Route('/api/restaurant', name: 'app_api_restaurant_')]
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
    // Documantation API: Attributs à la route /new
    #[OA\Post(
    path: '/api/restaurant',
    summary: 'Créer un nouveau restaurant',
    security: [ ['X-AUTH-TOKEN' => []] ], //security={{"Bearer":{}}} sur chaque méthode annotée OpenAPI (cela permet à Swagger/Nelmio d’exiger un token Bearer pour tester ces routes)
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'address', type: 'string'),
                new OA\Property(property: 'postalCode', type: 'string'),
                new OA\Property(property: 'city', type: 'string'),
                new OA\Property(property: 'country', type: 'string')
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Restaurant créé'),
        new OA\Response(response: 400, description: 'Données invalides')
    ]
    )]

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
    // Documantation API: Attributs à la route /show
    #[OA\Get(
    path: '/api/restaurant/{id}',
    summary: 'Afficher un restaurant par ID',
    security: [ ['X-AUTH-TOKEN' => []] ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        )
    ],
    responses: [
        new OA\Response(response: 200, description: 'Détails du restaurant'),
        new OA\Response(response: 404, description: 'Restaurant non trouvé')
    ]
    )]

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
    // Documantation API: Attributs à la route /edit
    #[OA\Put(
    path: '/api/restaurant/{id}',
    summary: 'Modifier un restaurant existant',
    security: [ ['X-AUTH-TOKEN' => []] ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'address', type: 'string'),
                new OA\Property(property: 'postalCode', type: 'string'),
                new OA\Property(property: 'city', type: 'string'),
                new OA\Property(property: 'country', type: 'string')
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Restaurant mis à jour'),
        new OA\Response(response: 404, description: 'Restaurant non trouvé')
    ]
    )]

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
    // Documantation API: Attributs à la route /delete
    #[OA\Delete(
    path: '/api/restaurant/{id}',
    summary: 'Supprimer un restaurant par ID',
    security: [ ['X-AUTH-TOKEN' => []] ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        )
    ],
    responses: [
        new OA\Response(response: 204, description: 'Restaurant supprimé'),
        new OA\Response(response: 404, description: 'Restaurant non trouvé')
    ]
    )]

    public function delete(int $id): JsonResponse
    {
        $restaurant = $this-> repository->findOneBy(['id' => $id]);
        if (!$restaurant) {
            throw $this->createNotFoundException("No Restaurant found for {$id} id");
        }

        $this->manager->remove($restaurant);
        $this->manager->flush();

        return new JsonResponse(['message' => "Food resource deleted"], Response::HTTP_NO_CONTENT);
    }

}
