<?php

namespace App\Controller;

use DateTime;
use OpenApi\Attributes as OA;
use App\Entity\Category;
use Symfony\Component\Uid\Uuid;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\{JsonResponse, Response, Request};
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Les routes comportent des attributs permettant faire des test sur le Bundle Nelmio à l'url suivante : https://127.0.0.1:8000/api/doc afin d'améliorer la documentation. Un template Twig a été générer specifique via la commande : composer require twig asset
#[Route('/api/category', name: 'app_api_category_')]
// Documantation API: Attributs à la route /category
#[OA\Tag(name: 'Category')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager, 
        private CategoryRepository $repository,
        private SerializerInterface $serializer
        ){}

    #[Route('/new', name: 'new', methods: ['POST'])]
    // Documantation API: Attributs à la route /new
    #[OA\Post(
        path: '/api/category/new',
        summary: 'Créer une nouvelle catégorie',
        security: [ ['X-AUTH-TOKEN' => []] ], //security={{"Bearer":{}}} sur chaque méthode annotée OpenAPI (cela permet à Swagger/Nelmio d’exiger un token Bearer pour tester ces routes)
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Fast Food')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Catégorie créée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'uuid', type: 'string'),
                    ]
                )
            )
        ]
    )]
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
    // Documantation API: Attributs à la route /show
    #[OA\Get(
        path: '/api/category/{id}',
        summary: 'Afficher une catégorie par son ID',
        security: [ ['X-AUTH-TOKEN' => []] ],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détails de la catégorie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'uuid', type: 'string'),
                        new OA\Property(property: 'title', type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Catégorie non trouvée')
        ]
    )]
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
    // Documantation API: Attributs à la route /edit
    #[OA\Put(
        path: '/api/category/{id}',
        summary: 'Modifier une catégorie',
        security: [ ['X-AUTH-TOKEN' => []] ],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Updated Title')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Catégorie mise à jour'),
            new OA\Response(response: 404, description: 'Catégorie non trouvée')
        ]
    )]
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
    // Documantation API: Attributs à la route /delete
    #[OA\Delete(
        path: '/api/category/{id}',
        summary: 'Supprimer une catégorie',
        security: [ ['X-AUTH-TOKEN' => []] ],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Catégorie supprimée'),
            new OA\Response(response: 404, description: 'Catégorie non trouvée')
        ]
    )]
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
