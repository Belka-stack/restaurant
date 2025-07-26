<?php

namespace App\DataFixtures;

use DateTime;
use Exception;
use Faker\Factory;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class RestaurantFixtures extends Fixture
{
    public const RESTAURANT_NB_TUPLES = 5;

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        for ($i = 1; $i <= self::RESTAURANT_NB_TUPLES; $i++) {
            $restaurant = (new Restaurant())
                ->setUuid(Uuid::v4()->toRfc4122())
                ->setName($faker->company())
                ->setDescription($faker->sentence())
                ->setAmOpeningTime(['11:30', '14:00'])
                ->setPmOpeningTime(['18:30', '22:00'])
                ->setMaxGuest($faker->numberBetween(20, 100))
                ->setCreatedAt(new DateTime());

            $manager->persist($restaurant);
            $this->addReference("restaurant" . $i, $restaurant);
            
        }

        $manager->flush();
    }
}
