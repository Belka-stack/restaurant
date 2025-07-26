<?php

namespace App\DataFixtures;

use DateTime;
use Exception;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class RestaurantFixtures extends Fixture
{
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $restaurant = (new Restaurant())
                ->setUuid(Uuid::v4()->toRfc4122())
                ->setName("Restaurant {$i}")
                ->setDescription("Description du restaurant $i")
                ->setAmOpeningTime(['11:30', '14:00'])
                ->setPmOpeningTime(['18:30', '22:00'])
                ->setMaxGuest(random_int(20, 100))
                ->setCreatedAt(new DateTime());

            $manager->persist($restaurant);
            $this->addReference("restaurant" . $i, $restaurant);
            
        }

        $manager->flush();
    }
}
