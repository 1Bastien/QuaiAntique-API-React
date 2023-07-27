<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\RestaurantRepository;

class RestaurantController extends AbstractController
{
    // Read Restaurant
    #[Route('/api/restaurant', name: 'readRestaurant', methods: ['GET'])]
    public function readRestaurant(RestaurantRepository $restaurantRepository, SerializerInterface $serializer): JsonResponse
    {
        $restaurant = $restaurantRepository->findOneBy(['activated' => true]);

        $jsonRestaurant = $serializer->serialize($restaurant, 'json');
        return new JsonResponse($jsonRestaurant, Response::HTTP_OK, [], true);
    }
}
