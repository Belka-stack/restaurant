<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\RestaurantFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MenuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // On récupère tous les restaurants

        $restaurants = $manager->getRepository(Restaurant::class)->findAll();

        if (empty($restaurants)) {
            throw new \RuntimeException('Aucune restaurant trouvé.Veuillez créer des restaurants.');
        }
        for ($i = 1; $i <= 10; $i++) {
            $menu = new Menu();
            $menu->setUuid(Uuid::v4()->toRfc4122())
                ->setTitle("Menu #$i")
                ->setDescription("Description du menu #$i avec des plats variés.")
                ->setPrice(random_int(10, 50))
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(null)
                ->setRestaurant($restaurants[array_rand($restaurants)]);

                $manager->persist($menu);
        }
        

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class,
        ];
    }
}
