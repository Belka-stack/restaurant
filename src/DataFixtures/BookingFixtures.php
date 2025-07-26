<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\User;
use App\Entity\Booking;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use App\DataFixtures\UserFixtures;
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

            /** @var Restaurant $restaurant */
            $restaurant = $this->getReference("restaurant" . random_int(1, 5), Restaurant::class);

            /** @var User $user */
            $user = $this->getReference("user" . random_int(1, 20), User::class );

            $booking = (new Booking())
                ->setUuid(Uuid::v4()->toRfc4122())
                ->setGuestNumber(random_int(1, 6))
                ->setOrderDate(new \DateTime("+".random_int(0, 10)." days"))
                ->setOrderHour((new \DateTime())->setTime(random_int(12, 21), 0))
                ->setAllergy(random_int(0, 1) ? 'Arachides' : null)
                ->setCreatedAt(new DateTime())
                ->setRestaurant($restaurant)
                ->setUser($user);
                
            $manager->persist($booking);
        }
        

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class, // Doit exister avant la création de la data BookingFixtures
            UserFixtures::class // Doit exister avant la création de la data BookingFixtures
        ];
    }
}
