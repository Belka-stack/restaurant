<?php

namespace App\Controller;

use DateTime;
use OpenApi\Attributes as OA;
use App\Entity\Food;
use Symfony\Component\Uid\Uuid;
use App\Repository\FoodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};

// Les routes comportent des attributs permettant faire des test sur le Bundle Nelmio à l'url suivante : https://127.0.0.1:8000/api/doc afin d'améliorer la documentation. Un template Twig a été générer specifique via la commande : composer require twig asset

#[Route('/api/food', name: 'app_api_food_')]
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
    // Documantation API: Attributs à la route /new
    #[OA\Post(
    path: '/api/food/new',
    summary: 'Créer une nouvelle ressource Food',
    security: [ ['X-AUTH-TOKEN' => []] ], //security={{"Bearer":{}}} sur chaque méthode annotée OpenAPI (cela permet à Swagger/Nelmio d’exiger un token Bearer pour tester ces routes)
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['title'],
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'price', type: 'number', format: 'float'),
                new OA\Property(property: 'category_id', type: 'integer')
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Ressource créée'),
        new OA\Response(response: 400, description: 'Requête invalide')
    ]
    )]

    public function new(Request $request): JsonResponse
    {
        $food = $this->serializer->deserialize($request->getContent(), Food::class, 'json');
        $food->setUuid(Uuid::v4()->toRfc4122());
        $food->setCreatedAt(new DateTime());

        $this->manager->persist($food);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($food, 'json');
        $location = $this->urlGenerator->generate('app_api_food_show', ['id' => $food->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(
            $responseData,
            Response::HTTP_CREATED,
            ['Location' => $location],
            true // données déjà en JSON
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    // Documantation API: Attributs à la route /show
    #[OA\Get(
    path: '/api/food/{id}',
    summary: 'Afficher une ressource Food par ID',
    security: [ ['X-AUTH-TOKEN' => []] ],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
    ],
    responses: [
        new OA\Response(response: 200, description: 'Ressource trouvée'),
        new OA\Response(response: 404, description: 'Ressource non trouvée')
    ]
    )]

    public function show(int $id): JsonResponse
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if (!$food) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($food, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    // Documantation API: Attributs à la route /edit
    #[OA\Put(
    path: '/api/food/{id}',
    summary: 'Modifier une ressource Food',
    security: [ ['X-AUTH-TOKEN' => []] ],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'price', type: 'number', format: 'float'),
                new OA\Property(property: 'category_id', type: 'integer')
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Ressource modifiée'),
        new OA\Response(response: 404, description: 'Ressource non trouvée')
    ]
    )]

    public function edit(int $id, Request $request): JsonResponse
    {
        $food = $this->repository->find($id);

        if (!$food) {
            throw $this->createNotFoundException("Aucune ressource Food trouvée pour l'ID {$id}");
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Food::class,
            'json',
            ['object_to_populate' => $food]
        );


        $food->setUpdatedAt(new DateTime());

        $this->manager->flush();

        return new JsonResponse(['message' => 'Food updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    // Documantation API: Attributs à la route /delete
    #[OA\Delete(
    path: '/api/food/{id}',
    summary: 'Supprimer une ressource Food',
    security: [ ['X-AUTH-TOKEN' => []] ],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
    ],
    responses: [
        new OA\Response(response: 204, description: 'Ressource supprimée'),
        new OA\Response(response: 404, description: 'Ressource non trouvée')
    ]
    )]

    public function delete(int $id): Response
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if (!$food) {
            throw $this->createNotFoundException("No Food found for ID {$id}");
        }

        $this->manager->remove($food);
        $this->manager->flush();

        return new JsonResponse(['message' => "Food resource deleted"], Response::HTTP_NO_CONTENT);
    }
}
