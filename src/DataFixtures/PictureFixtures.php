<?php

namespace App\DataFixtures;

use DateTime;
use Exception;
use Faker\Factory;
use App\Entity\Picture;
use App\Entity\Restaurant;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\RestaurantFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    public const PICTURE_NB_TUPLES = 20;

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        for ($i = 1; $i <= self::PICTURE_NB_TUPLES; $i++){
            /** @var Restaurant $restaurant */
            $restaurant = $this->getReference("restaurant" . random_int(1, 5), Restaurant::class);
            
            $picture = (new Picture())
                ->setTitre($faker->words(nb: 3, asText: true))
                ->setSlug($faker->slug())
                ->setRestaurant($restaurant)
                ->setCreatedAt(new DateTime());

            $manager->persist($picture);
        }


        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [RestaurantFixtures::class];
    }
}
