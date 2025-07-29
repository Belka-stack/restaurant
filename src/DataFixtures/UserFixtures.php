<?php

namespace App\DataFixtures;

use DateTime;
use Exception;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher )
    {

    }

    public const USER_NB_TUPLES = 20;
    
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= self::USER_NB_TUPLES; $i++) {

            $user = (new User())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setGuestNumber($faker->numberBetween(0,10))
                ->setEmail($faker->unique()->safeEmail())
                ->setApiToken('123456abcdef123456abcdef123456abcdef')
                ->setCreatedAt(new DateTime())
                ->setUuid(Uuid::v4()->toRfc4122());

            
            // Mot de passe fixe
            $password = 'password';

            $user->setPassword($this->passwordHasher->hashPassword($user, $password));

            $manager->persist($user);

            // Ajout de la référence pour l'utilser dans d'autres fixtures

            $this->addReference("user" . $i, $user);

        }

        $manager->flush();
    }
}
