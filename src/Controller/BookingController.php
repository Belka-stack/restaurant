<?php

namespace App\Controller;

use App\Entity\Booking;
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
                required: ['guestNumber', 'orderDate', 'orderHour', 'restaurant', 'user'],
                properties: [
                    new OA\Property(property: 'guestNumber', type: 'integer', example: 4),
                    new OA\Property(property: 'orderDate', type: 'string', format: 'date', example: '2025-08-01'),
                    new OA\Property(property: 'orderHour', type: 'string', format: 'time', example: '19:00:00'),
                    new OA\Property(property: 'allergy', type: 'string', example: 'gluten'),
                    new OA\Property(property: 'restaurant', type: 'integer', example: 3),
                    new OA\Property(property: 'user', type: 'integer', example: 12),
                ]
            )
                ),
            responses: [
                new OA\Response(response: 201, description: 'Réservation créée'),
                new OA\Response(response: 400, description: 'Données invalides')
            ]
    )]

    public function new(Request $request): JsonResponse
    {
        $booking = $this->serializer->deserialize($request->getContent(), Booking::class, 'json');
        $booking->setUuid(Uuid::v4());
        $booking->setCreatedAt(new DateTime());

        $this->manager->persist($booking);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($booking, 'json');
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

        return new JsonResponse(
            $this->serializer->serialize($booking, 'json'),
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
            new OA\Response(response: 404, description: 'Réservation non trouvée')
        ]
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $booking = $this->repository->find($id);

        if (!$booking) {
            return new JsonResponse(['message' => "Réservation introuvable"], Response::HTTP_NOT_FOUND);
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
            new OA\Response(response: 404, description: 'Réservation non trouvée')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $booking = $this->repository->find($id);

        if (!$booking) {
            return new JsonResponse(['message' => 'Réservation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($booking);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}

