<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Menu;
use App\Entity\Category;
use App\Entity\MenuCategory;
use App\DataFixtures\MenuFixtures;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MenuCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Crée 10 relations Menu <-> Category

        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 10; $i++) {

            // Randomise les couples menu / category pour plus de réalisme

            $menuId = $faker->numberBetween(1, MenuFixtures::MENU_NB_TUPLES);
            $categoryId = $faker->numberBetween(1, CategoryFixtures::CATEGORY_NB_TUPLES ?? 10);

            /** @var Menu $menu */
            $menu = $this->getReference("menu" . $menuId, Menu::class);

            /** @var Category $category */
            $category = $this->getReference("category" . $categoryId, Category::class );

            $menuCategory = new MenuCategory();
            $menuCategory->setMenu($menu);
            $menuCategory->setCategory($category);

            $manager->persist($menuCategory);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            MenuFixtures::class,
            CategoryFixtures::class    
        ];
    }
}