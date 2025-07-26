<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Booking;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use App\DataFixtures\UserFixtures;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\RestaurantFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BookingFixtures extends Fixture implements DependentFixtureInterface
{
    public const BOOKING_NB_TUPLES = 10;

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= self::BOOKING_NB_TUPLES; $i++) {

            /** @var Restaurant $restaurant */
            $restaurant = $this->getReference(
                "restaurant" . $faker->numberBetween(1, RestaurantFixtures::RESTAURANT_NB_TUPLES),
                Restaurant::class
            );

            /** @var User $user */
            $user = $this->getReference("user" . $faker->numberBetween(1, UserFixtures::USER_NB_TUPLES),
            User::class

            );

            $booking = (new Booking())
                ->setUuid(Uuid::v4()->toRfc4122())
                ->setGuestNumber($faker->numberBetween(1, 6))
                ->setOrderDate($faker->dateTimeBetween('+1 days', '+15 days'))
                ->setOrderHour($faker->dateTimeBetween('12:00', '21:00'))
                ->setAllergy($faker->boolean(30) ? $faker->word() : null)
                ->setCreatedAt(new DateTime())
                ->setRestaurant($restaurant)
                ->setUser($user);
                
            $manager->persist($booking);
        }
        

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class, // Doit exister avant la création de la data BookingFixtures
            UserFixtures::class // Doit exister avant la création de la data BookingFixtures
        ];
    }
}
