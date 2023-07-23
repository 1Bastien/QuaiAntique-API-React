<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\RushType;
use App\Service\BookingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookingController extends AbstractController
{
    #[Route('/api/booking/{date}', name: 'readReminigPlacesToday', methods: ['GET'])]
    public function getReminingPlacesToday(string $date, BookingService $bookingService, SerializerInterface $serializer): JsonResponse
    {
        $dateTime = new \DateTimeImmutable($date);

        $remainingPlacesLunch = $bookingService->getRemainingPlaces(RushType::LUNCH, $dateTime);
        $remainingPlacesDinner = $bookingService->getRemainingPlaces(RushType::DINNER, $dateTime);

        $remainingPlaces = ['Lunch' => $remainingPlacesLunch, 'Dinner' => $remainingPlacesDinner];

        $jsonRemainingPlaces = $serializer->serialize($remainingPlaces, 'json');

        return new JsonResponse($jsonRemainingPlaces, Response::HTTP_OK, [], true);
    }

    // Create Booking
    #[Route('/api/booking', name: 'createBooking', methods: ['POST'])]
    public function createBooking(Request $request, EntityManagerInterface $entityManagerInterface, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, BookingService $bookingService, ValidatorInterface $validator): JsonResponse
    {
        $booking = $serializer->deserialize($request->getContent(), Booking::class, 'json');

        $errors = $validator->validate($booking);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $dateTime = $booking->getDate();
        $nbGuests = $booking->getNbGuests();
        $remainingPlaces = $bookingService->getRemainingPlaces(RushType::fromDateTime($dateTime), $dateTime);

        if (!$bookingService->isBookingPossible($nbGuests, $remainingPlaces)) {

            $entityManagerInterface->persist($booking);
            $entityManagerInterface->flush();

            $jsonBooking = $serializer->serialize($booking, 'json', []);

            $location = $urlGenerator->generate('readBooking', ['id' => $booking->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
            
            return new JsonResponse($jsonBooking, Response::HTTP_CREATED, ['Location' => $location], true);
        } else {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "Réservation impossible");
        }
    }
}
