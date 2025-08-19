<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Booking;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class BookingService {
    public function __construct(private EntityManagerInterface $manager)
    {}


    /**
     * Crée une réservation après validation des règles métier.
     */

    public function createBooking(
        Restaurant $restaurant,
        User $user,
        int $guestNumber, 
        \DateTimeInterface $orderDate,
        \DateTimeInterface $orderHour
        ): Booking
    {
        // Vérifier si le restaurant est ouvert à la d  te demandée

        if (!$this->isRestaurantOpen($restaurant, $orderHour)) {
            throw new \Exception("Le restaurant est fermé à ce créneau.");
        }

        // Vérifier la capcité maximale
        if ($guestNumber > $restaurant->getMaxGuest()) {
            throw new \Exception("Nombre de convives supérieur à la capacité maximale.");
        }

        // Création de la réservation

        $booking = new Booking();
        $booking->setUuid(Uuid::v4());
        $booking->setRestaurant($restaurant);
        $booking->setUser($user);
        $booking->setGuestNumber($guestNumber);
        $booking->setOrderDate($orderDate);
        $booking->setOrderHour($orderHour);
        $booking->setCreatedAt(new \DateTime());
        

        $this->manager->persist($booking);
        $this->manager->flush();

        return $booking;
    }

    /**
     * Vérifie si un restaurant est ouvert à une date donnée
     */

    private function isRestaurantOpen(Restaurant $restaurant, \DateTimeInterface $date): bool

    {
        $hourMinute = $date->format('H:i');

        $amTimes = $restaurant->getAmOpeningTime();
        $pmTimes = $restaurant->getPmOpeningTime();


        $isOpenMorning = is_array($amTimes) 
        && count($amTimes) === 2
        && $hourMinute <= $amTimes[0]
        && $hourMinute < $amTimes[1];
    
        $isOpenAfternoon = is_array($pmTimes)
        && count($pmTimes) === 2 
        && $hourMinute >= $pmTimes[0] 
        && $hourMinute < $pmTimes[1];

        return $isOpenMorning || $isOpenAfternoon;

    }
}