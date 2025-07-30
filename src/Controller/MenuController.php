<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Restaurant;
use OpenApi\Attributes as OA;
use App\Repository\MenuRepository;
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

#[Route('/api/menu', name: 'app_api_menu_')]
#[OA\Tag(name: 'menu')]
final class MenuController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private MenuRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    )
    {}

    #[Route(name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/menu',
        summary: 'Créer un menu',
        security: [ ['X-AUTH-TOKEN' => []] ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties:  [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'restaurant', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'menu créé'),
            new OA\Response(response: 400, description: 'Requête invalide'),
        ]

    )]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(),true);

        // 1. Récupérer le restaurant par son ID
        $restaurant = $this->manager->getRepository(Restaurant::class)->find($data['restaurant']);

        if (!$restaurant) {
            return $this->json(['message' => 'Restaurant non trouvé'], Response::HTTP_BAD_REQUEST);
        }

        // 2. Créer manuellement l'objet menu
        
        $menu = new Menu();
        $menu = $this->serializer->deserialize($request->getContent(), Menu::class, 'json');
        $menu->setTitle($data['title'] ?? null);
        $menu->setDescription($data['description'] ?? null);
        $menu->setUuid(Uuid::v4()->toRfc4122());
        $menu->setCreatedAt(new DateTime());
        $menu->setRestaurant($restaurant);

        $this->manager->persist($menu);
        $this->manager->flush();

        $location = $this->urlGenerator->generate('app_api_menu_show', ['id' => $menu->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $responseData = $this->serializer->serialize($menu, 'json', ['groups' => ['menu:read'] ]);

        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/menu/{id}',
        summary: 'Afficher un menu',
        security: [ ['X-AUTH-TOKEN' => []] ],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: "Menu trouvé"),
            new OA\Response(response: 404, description: "Menu. non trouvé"),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $menu = $this->repository->find($id);

        if (!$menu) {
            return $this->json(['message' => 'Menu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->serialize($menu, 'json', ['groups' => ['menu:read']]),
        Response::HTTP_OK,
        [],
        true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/menu/{id}',
        summary: 'Modifier un menu',
        security: [ ['X-AUTH-TOKEN' => []] ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'restaurant', type: 'string')
                ]
            )
        ),
        parameters: [
            new OA\Parameter(name: 'id', in:'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Menu mis à jour'),
            new OA\Response(response: 404, description: 'Menu non trouvé')
        ]

    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $menu = $this->repository->find($id);

        if (!$menu) {
            return $this->json(['message' => 'Menu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Menu::class,
            'json',
            ['object_to_populate' => $menu]
        );

        $menu->setUpdatedAt(new DateTime());
        $this->manager->flush();

        return $this->json(['message' => 'Menu mis à jour']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/menu/{id}',
        summary: 'Supprimer un menu',
        security: [ ['X-AUTH-TOKEN' => []] ],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description:'Menu supprimé'),
            new OA\Response(response: 404, description:'Menu non trouvé')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $menu = $this->repository->find($id);

        if (!$menu) {
            return $this->json(['message' => 'menu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($menu);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
