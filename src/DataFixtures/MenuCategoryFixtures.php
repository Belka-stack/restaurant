<?php

namespace App\DataFixtures;

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

        for ($i = 1; $i <= 10; $i++) {
            $menu = $this->getReference("menu" . $i, Menu::class);
            $category = $this->getReference("category" . $i, Category::class );

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
