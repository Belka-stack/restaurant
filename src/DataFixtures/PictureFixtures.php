<?php

namespace App\DataFixtures;

use DateTime;
use Exception;
use App\Entity\Picture;
use App\Entity\Restaurant;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\RestaurantFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 20; $i++){
            /** @var Restaurant $restaurant */
            $restaurant = $this->getReference("restaurant" . random_int(1, 5), Restaurant::class);
            
            $picture = (new Picture())
                ->setTitre("Article n°$i")
                ->setSlug("slug-article-title")
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
