<?php

namespace App\DataFixtures;

use Faker\Factory;
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
    public const FOODCATEGORY_NB_TUPLES = 10;
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        for ($i = 1; $i <= self::FOODCATEGORY_NB_TUPLES; $i++) {

            $foodId = $faker->numberBetween(1, FoodFixtures::FOOD_NB_TUPLES);
            $categoryId = $faker->numberBetween(1, CategoryFixtures::CATEGORY_NB_TUPLES);


            /** @var Food $food */
            $food = $this->getReference("food" . $foodId, Food::class);

            /** @var Category $category  */
            $category = $this->getReference("category" . $categoryId, Category::class);

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
