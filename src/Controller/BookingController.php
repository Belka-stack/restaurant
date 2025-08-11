<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Restaurant;
use App\Entity\User;
use OpenApi\Attributes as OA;
use App\Repository\BookingRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

#[Route('/api/booking', name: 'app_api_booking_')]
#[OA\Tag(name: 'Booking')]

final class BookingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private BookingRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    )
    {}

    #[Route(name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/booking',
        summary: 'Créer une réservation',
        security: [ ['X-AUTH-TOKEN' => []] ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['guestNumber', 'orderDate', 'orderHour', 'restaurant'], // user est optionnel, donc non requis ici
                properties: [
                    new OA\Property(property: 'guestNumber', type: 'integer', example: 4),
                    new OA\Property(property: 'orderDate', type: 'string', format: 'date', example: '2025-08-01'),
                    new OA\Property(property: 'orderHour', type: 'string', format: 'time', example: '19:00:00'),
                    new OA\Property(property: 'allergy', type: 'string', example: 'gluten'),
                    new OA\Property(property: 'restaurant', type: 'integer', example: 3),
                    new OA\Property(property: 'user', type: 'integer', example: 12, description: 'ID de l’utilisateur (optionnel, utilisable uniquement par un admin)'
                    ),
                ]
            )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Réservation créée'),
                new OA\Response(response: 400, description: 'Données invalides')
            ]
    )]

    public function new(Request $request, #[CurrentUser] User $currentUser): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // --- DÉBUT DES VALIDATIONS DE CHAMPS REQUIS ---
        if (!isset($data['guestNumber'])) {
            return new JsonResponse(['error' => 'Champ guestNumber requis'], Response::HTTP_BAD_REQUEST);
        }
        if (!isset($data['orderDate'])) {
            return new JsonResponse(['error' => 'Champ orderDate requis'], Response::HTTP_BAD_REQUEST);
        }
        if (!isset($data['orderHour'])) {
            return new JsonResponse(['error' => 'Champ orderHour requis'], Response::HTTP_BAD_REQUEST);
        }
        if (!isset($data['restaurant'])) {
            return new JsonResponse(['error' => 'Champ restaurant requis'], Response::HTTP_BAD_REQUEST);
        }
        // --- FIN DES VALIDATIONS DE CHAMPS REQUIS ---

        $restaurant = $this->manager->getRepository(Restaurant::class)->find($data['restaurant']);
        if (!$restaurant) {
            return new JsonResponse(['error' => 'Restaurant non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // --- DÉBUT DE LA LOGIQUE CORRIGÉE POUR L'UTILISATEUR ---
        // On initialise la variable $user avec l'utilisateur connecté par défaut.
        $user = $currentUser;

        // Si l'utilisateur est un ADMIN et qu'un ID utilisateur est spécifié, on l'utilise.
        if ($this->isGranted('ROLE_ADMIN') && isset($data['user'])) {
            $userForBooking = $this->manager->getRepository(User::class)->find($data['user']);
        
            // Si l'utilisateur spécifié par l'admin n'existe pas, on renvoie une erreur.
            if (!$userForBooking) {
                return new JsonResponse(['error' => 'Utilisateur spécifié non trouvé'], Response::HTTP_NOT_FOUND);
            } 
            
            // On remplace l'utilisateur par défaut par celui spécifié par l'admin.
            $user = $userForBooking;
        }
        // Le code continue, et la variable $user est maintenant toujours définie.
        // --- FIN DE LA LOGIQUE CORRIGÉE ---

        // Créer une nouvelle réservation
        $booking = new Booking();
        $booking->setUuid(Uuid::v4()->toRfc4122());
        $booking->setCreatedAt(new DateTime());
        $booking->setGuestNumber($data['guestNumber']);
        $booking->setOrderDate(new \DateTime($data['orderDate']));
        $booking->setOrderHour(new \DateTime($data['orderHour']));
        $booking->setAllergy($data['allergy'] ?? null);
        $booking->setRestaurant($restaurant);
        $booking->setUser($user);

        $this->manager->persist($booking);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($booking, 'json', ['groups' => ['booking:read']]);
        $location = $this->urlGenerator->generate('app_api_booking_show', ['id' => $booking->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location ], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/booking/{id}',
        summary: 'Afficher une réservation par ID',
        security: [ ['X-AUTH-TOKEN' => []] ],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],

        responses: [
            new OA\Response(response: 200, description: 'Réservation trouvée'),
            new OA\Response(response: 404, description: 'Non trouvée')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $booking = $this->repository->find($id);
        if (!$booking) {
            return new JsonResponse(['message' => "Réservation introuvable"], Response::HTTP_NOT_FOUND);
        }
        $responseData = $this->serializer->serialize($booking, 'json', ['groups' => ['booking:read']]);
        return new JsonResponse(
            $responseData,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/booking/{id}',
        summary: 'Modifier une réservation',
        security: [ ['X-AUTH-TOKEN' => []] ],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'guestNumber', type: 'integer'),
                    new OA\Property(property: 'orderDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'orderHour', type: 'string', format: 'time'),
                    new OA\Property(property: 'allergy', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Réservation mise à jour'),
            new OA\Response(response: 403, description: 'Non autorisé à modifier cette réservation'),
            new OA\Response(response: 404, description: 'Réservation non trouvée')
        ]
    )]
    public function edit(int $id, Request $request, #[CurrentUser] User $currentUser): JsonResponse
    {
        $booking = $this->repository->find($id);

        if (!$booking) {
            return new JsonResponse(['message' => "Réservation introuvable"], Response::HTTP_NOT_FOUND);
        }

        // Vérification des droits
        if (!$this->isGranted('ROLE_ADMIN') && $booking->getUser()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['message' => 'Accès refusé : vous ne pouvez modifier que vos propres réservations'], Response::HTTP_FORBIDDEN);
        }

        $this->serializer->deserialize($request->getContent(), Booking::class, 'json', [
            'object_to_populate' => $booking
        ]);

        $booking->setUpdatedAt(new DateTime());
        $this->manager->flush();

        return new JsonResponse(['message' => 'Réservation mise à jour']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/booking/{id}',
        summary: 'Supprimer une réservation',
        security: [ ['X-AUTH-TOKEN' => []] ],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Réservation suppriméé'),
            new OA\Response(response: 403, description: 'Non autorisé à supprimer cette réservation'),
            new OA\Response(response: 404, description: 'Réservation non trouvée')
        ]
    )]
    public function delete(int $id, #[CurrentUser] User $currentUser): JsonResponse
    {
        $booking = $this->repository->find($id);

        if (!$booking) {
            return new JsonResponse(['message' => 'Réservation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifications des droits
        if (!$this->isGranted('ROLE_ADMIN') && $booking->getUser()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['message' => 'Accès refusé : vous ne pouvez supprimer que vos propres réservations'], Response::HTTP_FORBIDDEN);
        }

        $this->manager->remove($booking);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
