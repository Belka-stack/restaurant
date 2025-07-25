<?php

namespace App\DataFixtures;

use App\Entity\Food;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class FoodFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    { 
        for ($i = 1; $i <= 10; $i++){
            $food = (new Food())
            ->setUuid(Uuid::v4()->toRfc4122())
            ->setTitle("Plat $i")
            ->setDescription("Une description savoureuse pour le plat numéro $i.")
            ->setPrice(mt_rand(8, 25) + 0.99)
            ->setCreatedAt(new \DateTime());
        
            $manager->persist($food);

        }
        

        $manager->flush();
    }
}
