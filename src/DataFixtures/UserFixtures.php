<?php

namespace App\DataFixtures;

use DateTime;
use Exception;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher )
    {

    }
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 20; $i++) {

            $user = (new User())
                ->setFirstName("Firstname $i")
                ->setLastName("Lastname $i")
                ->setGuestNumber(random_int(0,10))
                ->setEmail("email.$i@studi.fr")
                ->setCreatedAt(new DateTime())
                ->setUuid(Uuid::v4()->toRfc4122());

            $user->setPassword($this->passwordHasher->hashPassword($user, "password$i"));
            $manager->persist($user);

            // Ajout de la référence pour l'utilser dans d'autres fixtures

            $this->addReference("user" . $i, $user);



        }
        $manager->flush();
    }
}
