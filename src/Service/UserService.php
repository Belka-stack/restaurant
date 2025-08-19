<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;


class UserService
{
    public function __construct(private EntityManagerInterface $manager,private BookingRepository $bookingRepository)
    {}
    /**
     * Supprime un utilsateur avec vérification des droits et vérification des réservation liées.
     */
    public function deleteUser(User $user): void
    {

        // Supprime toutes les réservations liées à l'utilisateur

        $bookings = $this->bookingRepository->findBy(['user' => $user]);
        foreach ($bookings as $booking) {
            $this->manager->remove($booking);
        }
        

        // Supprimer l'utilsateur

        $this->manager->remove($user);
        $this->manager->flush();
    }
}