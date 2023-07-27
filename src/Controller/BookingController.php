<?php

namespace App\Controller;

use App\Entity\Booking;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\RushType;
use App\Repository\BookingRepository;
use App\Repository\CustomerRepository;
use App\Service\BookingService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookingController extends AbstractController
{
    // Scearch Avaibilities
    #[Route('/api/booking/{date}', name: 'readReminigPlacesToday', methods: ['GET'])]
    public function getReminingPlaces(string $date, BookingService $bookingService, SerializerInterface $serializer): JsonResponse
    {
        $dateTime = new DateTimeImmutable($date);

        $remainingPlacesLunch = $bookingService->getRemainingPlaces(RushType::LUNCH, $dateTime);
        $remainingPlacesDinner = $bookingService->getRemainingPlaces(RushType::DINNER, $dateTime);

        $remainingPlaces = ['lunch' => $remainingPlacesLunch, 'dinner' => $remainingPlacesDinner];

        $jsonRemainingPlaces = $serializer->serialize($remainingPlaces, 'json', ['groups' => 'getBooking']);

        return new JsonResponse($jsonRemainingPlaces, Response::HTTP_OK, [], true);
    }

    // Create Booking
    #[Route('/api/booking', name: 'createBooking', methods: ['POST'])]
    public function createBooking(Request $request, EntityManagerInterface $entityManagerInterface, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, BookingService $bookingService, ValidatorInterface $validator, CustomerRepository $customerRepository): JsonResponse
    {
        $jsonDecode = json_decode($request->getContent());

        $dateTimeImmutable = new DateTimeImmutable($jsonDecode->date);
        $customer = $customerRepository->findOneBy(['email' => $jsonDecode->customer]);
        
        $booking = new Booking;
        $booking
        ->setDate($dateTimeImmutable)
        ->setCustomer($customer)
        ->setNbGuests($jsonDecode->nbGuests)
        ->setNameOfBooking($jsonDecode->nameOfBooking)
        ->setEmail($jsonDecode->email);
        
        if ($customer !== null ) {
            $booking->setEmail($customer->getEmail());
        }

        $errors = $validator->validate($booking);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $remainingPlaces = $bookingService->getRemainingPlaces(RushType::fromDateTime($dateTimeImmutable), $dateTimeImmutable);

        if ($bookingService->isBookingPossible($jsonDecode->nbGuests, $remainingPlaces)) {

            $entityManagerInterface->persist($booking);
            $entityManagerInterface->flush();

            $jsonBooking = $serializer->serialize($booking, 'json', ['groups' => 'getBooking']);
            
            return new JsonResponse($jsonBooking, Response::HTTP_CREATED, [], true);
        }

        throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "Réservation impossible");
    }

    //Update Booking
    #[Route('/api/booking/{id}', name: 'updateBooking', methods: ['PUT'])]
    public function updateBooking(Request $request, SerializerInterface $serializer, Booking $currentBooking, EntityManagerInterface $entityManagerInterface, JWTEncoderInterface $jwt, CustomerRepository $customerRepository, BookingRepository $bookingRepository, String $id): JsonResponse
    {
        $tokenJwt = $jwt->decode(substr($request->headers->get('Authorization'), 7));

        $currentCustomer = $customerRepository->findOneByEmail($tokenJwt['username']);
        $booking = $bookingRepository->findOneBy(['customer' => $currentCustomer, 'id' => $id]);

        if (!$booking) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "Réservation introuvable");
        }
        
        $updatedBooking = $serializer->deserialize(
            $request->getContent(),
            Booking::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBooking]
        );

        $entityManagerInterface->persist($updatedBooking);
        $entityManagerInterface->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    //Delete Booking
    #[Route('/api/booking/{id}', name: 'deletebooking', methods: ['DELETE'])]
    public function deleteBooking(Booking $booking, EntityManagerInterface $entityManager, Request $request, JWTEncoderInterface $jwt, CustomerRepository $customerRepository, BookingRepository $bookingRepository, string $id): JsonResponse
    {
        $tokenJwt = $jwt->decode(substr($request->headers->get('Authorization'), 7));

        $currentCustomer = $customerRepository->findOneByEmail($tokenJwt['username']);
        $booking = $bookingRepository->findOneBy(['customer' => $currentCustomer, 'id' => $id]);

        if (!$booking) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "Réservation introuvable");
        }
        
        $entityManager->remove($booking);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
