<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $category = new Category()
                    ->setUuid(Uuid::v4()->toRfc4122())
                    ->setTitle("Catégorie $i")
                    ->setCreatedAt((new \DateTime()));

            $manager->persist($category);
        }

        $manager->flush();
    }
}
