<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\MenuRepository;

class MenuController extends AbstractController
{
    // Read MenusAvailable
    #[Route('/api/menusAvailable', name: 'readMenusAvailable', methods: ['GET'])]
    public function readMenusAvailable(MenuRepository $menuRepository, SerializerInterface $serializer): JsonResponse
    {
        $menusList = $menuRepository->findBy(['available' => true]);

        $jsonMenus = $serializer->serialize($menusList, 'json');
        return new JsonResponse($jsonMenus, Response::HTTP_OK, [], true);
    }
}
