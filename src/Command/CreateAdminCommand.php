<?php

namespace App\Command;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un utiulsateur admin',
)]

class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe de l\'admin')
            ->addArgument('firstname', InputArgument::OPTIONAL, 'Prénom de l\'admin', 'Admin')
            ->addArgument('lastname', InputArgument::OPTIONAL, 'Nom de l\'admin', 'User');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $firstname = $input->getArgument('firstname');
        $lastname = $input->getArgument('lastname');

        // Vérifie si l'admin existe déjà

        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $io->error("Un utilsateur avec l'email {$email} existe déjà.");
            return Command::FAILURE;
        }

        $admin = new User();
        $admin->setEmail($email);
        $admin->setFirstName($firstname);
        $admin->setLastName($lastname);
        $admin->setCreatedAt(new \DateTime());
        $admin->setApiToken(bin2hex(random_bytes(32)));
        $admin->setUuid(Uuid::v4());
        $admin->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setPassword($hashedPassword);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success("Administrateur {$email} créé avec succès !");
        return Command::SUCCESS;
    }
}