<?php

namespace App\DataFixtures;

use App\Entity\Food;
use App\Entity\Category;
use App\Entity\FoodCategory;
use App\DataFixtures\FoodFixtures;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class FoodCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Crée 10 relations Food <-> Category
        for ($i = 1; $i <= 10; $i++) {
            $food = $this->getReference("food" . $i, Food::class);
            $category = $this->getReference("category" . $i, Category::class);

            $foodCategory = new FoodCategory();
            $foodCategory->setFood($food);
            $foodCategory->setCategory($category);

            $manager->persist($foodCategory);
        }


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            FoodFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
