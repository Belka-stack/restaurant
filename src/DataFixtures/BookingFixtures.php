<?php

namespace App\DataFixtures;

use App\Entity\Booking;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\RestaurantFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BookingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Récupère tous les restaurant existants

        $restaurants = $manager->getRepository(Restaurant::class)->findAll();

        if (empty($restaurants)) {
            throw new \RuntimeException("Aucun restaurant trouvé.Crée d'abord des restaurants.");
        }

        for ($i = 1; $i <= 10; $i++) {
            $booking = (new Booking())
                ->setUuid(Uuid::v4()->toRfc4122())
                ->setGuestNumber(random_int(1, 6))
                ->setOrderDate(new \DateTime("+".random_int(0, 10)." days"))
                ->setOrderHour((new \DateTime())->setTime(random_int(12, 21), 0))
                ->setAllergy(random_int(0, 1) ? 'Arachides' : null)
                ->setCreatedAt(new \DateTime())
                ->setRestaurant($restaurants[array_rand($restaurants)]);

            $manager->persist($booking);
        }
        

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class,
        ];
    }
}
