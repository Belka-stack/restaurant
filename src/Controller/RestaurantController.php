<?php

namespace App\Controller;

use DateTime;
use Symfony\Component\Uid\Uuid;
use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/restaurant', name: 'app_api_restaurant_')]
final class RestaurantController extends AbstractController
{   
    public function __construct(private EntityManagerInterface $manager,private RestaurantRepository $repository)
    {

    }

    #[Route(name: 'new', methods: ['POST'])]
    public function new(): Response
    {
        $restaurant = new Restaurant();
        $restaurant->setName('Quai Antique');
        $restaurant->setMaxGuest(50);
        $restaurant->setDescription('Cette qualité et ce goût par le chef Arnaud MICHANT.');
        $restaurant->setUuid(Uuid::v4()->toRfc4122());
        $restaurant->setCreatedAt(new DateTime());

        $this->manager->persist($restaurant);
        $this->manager->flush();

        return $this->json(
            ['message' => "Restaurant resource created with {$restaurant->getId()} id"],
            Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if (!$restaurant) {
            throw $this->createNotFoundException("No Restaurant found for {$id} id");
        }

        return $this->json(
            ['message' =>"A Restaurant was found : {$restaurant->getName()} for {$restaurant->getId()} id"]
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id): Response
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if (!$restaurant) {
            throw $this->createNotFoundExeception("No Restaurant found for {$id} id");
        }

        $restaurant->setName('Restaurant name updated');
        $this->manager->flush();

        return $this->redirectToRoute('app_api_restaurant_show', ['id' => $restaurant->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods:['DELETE'])]
    public function delete(int $id): Response
    {
        $restaurant = $this-> repository->findOneBy(['id' => $id]);
        if (!$restaurant) {
            throw $this->createNotFoundException("No Restaurant found for {$id} id");
        }

        $this->manager->remove($restaurant);
        $this->manager->flush();

        return $this->json(['message' => "Restaurant resource deleted"], Response::HTTP_NO_CONTENT);
    }

}
