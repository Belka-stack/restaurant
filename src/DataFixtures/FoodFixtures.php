<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\Food;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class FoodFixtures extends Fixture
{
    public const FOOD_NB_TUPLES = 10;

    public function load(ObjectManager $manager): void
    { 
        $faker = Factory::create('fr_FR');
        
        for ($i = 1; $i <= self::FOOD_NB_TUPLES; $i++){
            $food = (new Food())
            ->setUuid(Uuid::v4()->toRfc4122())
            ->setTitle($faker->words(nb: 3, asText: true))
            ->setDescription($faker->sentence(10))
            ->setPrice($faker->randomFloat(2, 8, 25))
            ->setCreatedAt(new DateTime());
        
            $manager->persist($food);

            // Ajout de la référence pour d'autres fixtures
            $this->addReference("food" . $i, $food);

        }
        

        $manager->flush();
    }
}
