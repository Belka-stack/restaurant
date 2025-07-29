<?php

namespace App\Controller;

use App\Entity\Picture;
use OpenApi\Attributes as OA;
use App\Repository\PictureRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/picture', name: 'app_api_picture_')]
#[OA\Tag(name: 'Picture')]
final class PictureController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private PictureRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    #[Route(name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/picture',
        summary: 'Créer une image liée à un restaurant',
        security: [['X-AUTH-TOKEN' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titre', 'slug', 'restaurant'],
                properties: [
                    new OA\Property(property: 'titre', type: 'string', example: 'Photo terrasse été'),
                    new OA\Property(property: 'slug', type: 'string', example: 'photo-terrasse-ete'),
                    new OA\Property(property: 'restaurant', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Image créée'),
            new OA\Response(response: 400, description: 'Requête invalide')
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $picture = $this->serializer->deserialize($request->getContent(), Picture::class, 'json');
        $picture->setUuid(Uuid::v4()->toRfc4122());
        $picture->setCreatedAt(new DateTime());

        $this->manager->persist($picture);
        $this->manager->flush();

        $location = $this->urlGenerator->generate('app_api_picture_show', ['id' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $responseData = $this->serializer->serialize($picture, 'json');

        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/picture/{id}',
        summary: 'Afficher une image par ID',
        security: [['X-AUTH-TOKEN' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Image trouvée'),
            new OA\Response(response: 404, description: 'Image non trouvée')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $picture = $this->repository->find($id);

        if (!$picture) {
            return $this->json(['message' => 'Image non trouvée'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            $this->serializer->serialize($picture, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/picture/{id}',
        summary: 'Modifier une image',
        security: [['X-AUTH-TOKEN' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'titre', type: 'string'),
                    new OA\Property(property: 'slug', type: 'string')
                ]
            )
        ),
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Image mise à jour'),
            new OA\Response(response: 404, description: 'Image non trouvée')
        ]
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $picture = $this->repository->find($id);

        if (!$picture) {
            return $this->json(['message' => 'Image non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Picture::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $picture
            ]
        );

        $picture->setUpdatedAt(new DateTime());
        $this->manager->flush();

        return $this->json(['message' => 'Image mise à jour']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/picture/{id}',
        summary: 'Supprimer une image',
        security: [['X-AUTH-TOKEN' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Image supprimée'),
            new OA\Response(response: 404, description: 'Image non trouvée')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $picture = $this->repository->find($id);

        if (!$picture) {
            return $this->json(['message' => 'Image non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($picture);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
