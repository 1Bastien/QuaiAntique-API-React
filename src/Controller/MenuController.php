<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Menu;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MenuController extends AbstractController
{
    // Create Menu
    #[Route('/api/menus', name: 'createMenu', methods: ['POST'])]
    public function createMenu(Request $request, EntityManagerInterface $entityManagerInterface, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer): JsonResponse
    {
        $menu = $serializer->deserialize($request->getContent(), Menu::class, 'json');
        $entityManagerInterface->persist($menu);
        $entityManagerInterface->flush();

        $jsonMenu = $serializer->serialize($menu, 'json', []);

        $location = $urlGenerator->generate('readMenu', ['id' => $menu->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);

        return new JsonResponse($jsonMenu, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    // Read Menus
    #[Route('/api/menus', name: 'readMenus', methods: ['GET'])]
    public function readMenus(MenuRepository $menuRepository, SerializerInterface $serializer): JsonResponse
    {
        $menuList = $menuRepository->findAll();

        $jsonMenu = $serializer->serialize($menuList, 'json');
        return new JsonResponse($jsonMenu, Response::HTTP_OK, [], true);
    }

    // Read Menu
    #[Route('/api/menus/{id}', name: 'readMenu', methods: ['GET'])]
    public function readMenu(Menu $menu, SerializerInterface $serializer): JsonResponse
    {
        $jsonMenu = $serializer->serialize($menu, 'json');
        return new JsonResponse($jsonMenu, Response::HTTP_OK, ['accept' => 'json'], true);
    }
    
    //Update Menu
    #[Route('/api/menus/{id}', name: 'updateMenu', methods: ['PUT'])]
    public function updateMenu(Request $request, SerializerInterface $serializer, Menu $currentMenu, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $updatedMenu = $serializer->deserialize($request->getContent(), 
                Menu::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentMenu]);
        
        $entityManagerInterface->persist($updatedMenu);
        $entityManagerInterface->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    //Delete Menu
    #[Route('/api/menus/{id}', name: 'deleteMenu', methods: ['DELETE'])]
    public function deleteMenu(Menu $menu, EntityManager $entityManager): JsonResponse
    {
        $entityManager->remove($menu);
        $entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
