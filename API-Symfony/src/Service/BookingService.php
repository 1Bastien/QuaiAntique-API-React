<?php

namespace App\Service;

use App\Enum\RushType;
use App\Repository\BookingRepository;
use App\Repository\RestaurantRepository;
use DateTimeImmutable;

class BookingService
{
    private BookingRepository $bookingRepository;

    private RestaurantRepository $restaurantRepository;

    public function __construct(BookingRepository $bookingRepository, RestaurantRepository $restaurantRepository)
    {
        $this->bookingRepository = $bookingRepository;
        $this->restaurantRepository = $restaurantRepository;
    }

    public function getRemainingPlaces(RushType $rushType, DateTimeImmutable $date): int
    {
        $bookingsRush = $this->bookingRepository->findByRush($date->setTime($rushType->getStart(), 0), $date->setTime($rushType->getEnd(), 0));

        $totalGuestsBook = array_reduce($bookingsRush, function ($previous, $booking) {
            return $previous + $booking->getNbGuests();
        }, 0);

        $seatingCapacity = $this->restaurantRepository->findOneBy(['activated' => true])->getSeatingCapacity();

        return $seatingCapacity - $totalGuestsBook;
    }

    public function isBookingPossible(int $nbGuests, int $remainingPlaces): bool 
    {
        return $nbGuests <= $remainingPlaces;
    }
}