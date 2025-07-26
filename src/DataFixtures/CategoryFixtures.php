<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\Category;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_NB_TUPLES = 10;
    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        for ($i = 1; $i <= self::CATEGORY_NB_TUPLES; $i++) {
            $category = new Category()
                    ->setUuid(Uuid::v4()->toRfc4122())
                    ->setTitle(ucfirst($faker->words(nb: 2, asText: true)))
                    ->setCreatedAt((new DateTime()));

            $manager->persist($category);

            // Ajout de la référence pour d'autres fixtures
            $this->addReference("category" . $i, $category);
        }

        $manager->flush();

    }
}
