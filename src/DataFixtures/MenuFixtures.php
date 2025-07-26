<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\Menu;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\RestaurantFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MenuFixtures extends Fixture implements DependentFixtureInterface
{
    public const MENU_NB_TUPLES = 10;

    public function load(ObjectManager $manager): void
    {
        // On récupère tous les restaurants

        $restaurants = $manager->getRepository(Restaurant::class)->findAll();

        if (empty($restaurants)) {
            throw new \RuntimeException('Aucune restaurant trouvé.Veuillez créer des restaurants.');
        }

        $faker = Factory::create('fr_FR');
        
        for ($i = 1; $i <= self::MENU_NB_TUPLES; $i++) {

            /** @var Restaurant $restaurant */
            $restaurant = $this->getReference("restaurant" . random_int(1, 5), Restaurant::class);

            $menu = new Menu();
            $menu->setUuid(Uuid::v4()->toRfc4122())
                ->setTitle($faker->sentence(3))
                ->setDescription($faker->paragraph(3))
                ->setPrice($faker->numberBetween(10, 50))
                ->setCreatedAt(new DateTime())
                ->setUpdatedAt(null)
                ->setRestaurant($restaurant);

                $manager->persist($menu);

                // Ajout de la référence pour l’utiliser ailleurs
                $this->addReference("menu" . $i, $menu);
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
