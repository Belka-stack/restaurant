<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Category;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $category = new Category()
                    ->setUuid(Uuid::v4()->toRfc4122())
                    ->setTitle("Catégorie $i")
                    ->setCreatedAt((new DateTime()));

            $manager->persist($category);

            // Ajout de la référence pour d'autres fixtures
            $this->addReference("category" . $i, $category);
        }

        $manager->flush();

    }
}
