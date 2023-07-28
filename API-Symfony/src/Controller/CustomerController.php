<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomerController extends AbstractController
{
    // Create Customer
    #[Route('/api/customer', name: 'createCustomer', methods: ['POST'])]
    public function createCustomer(Request $request, EntityManagerInterface $entityManagerInterface, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setPassword($userPasswordHasher->hashPassword($customer, $customer->getPassword()));
        
        $errors = $validator->validate($customer);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $entityManagerInterface->persist($customer);
        $entityManagerInterface->flush();

        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'getCustomer']);

        $location = $urlGenerator->generate('readCustomer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    // Read Customer
    #[Route('/api/customer/{id}', name: 'readCustomer', methods: ['GET'])]
    public function readCustomer(Customer $customer, SerializerInterface $serializer, Request $request, JWTEncoderInterface $jwt, CustomerRepository $customerRepository, int $id): JsonResponse
    {
        $tokenJwt = $jwt->decode(substr($request->headers->get('Authorization'), 7));
        $email = $tokenJwt['username'];
        $idCurrentCustomer = $customerRepository->findOneByEmail($email)->getId();

        if ($id !== $idCurrentCustomer) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "Accès aux données refusé");
        }

        $jsonCustomer = $serializer->serialize($customer, 'json',['groups' => 'getCustomer']);

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    //Update Customer
    #[Route('/api/customer/{id}', name: 'updateCustomer', methods: ['PUT'])]
    public function updateCustomer(Request $request, SerializerInterface $serializer, Customer $currentCustomer, EntityManagerInterface $entityManagerInterface, JWTEncoderInterface $jwt, CustomerRepository $customerRepository, int $id): JsonResponse
    {
        $tokenJwt = $jwt->decode(substr($request->headers->get('Authorization'), 7));
        $email = $tokenJwt['username'];
        $idCurrentCustomer = $customerRepository->findOneByEmail($email)->getId();

        if ($id !== $idCurrentCustomer) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "Accès aux données refusé");
        }
        
        $updatedCustomer = $serializer->deserialize(
            $request->getContent(),
            Customer::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCustomer]
        );

        $entityManagerInterface->persist($updatedCustomer);
        $entityManagerInterface->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    //Delete Customer
    #[Route('/api/customer/{id}', name: 'deleteCustomer', methods: ['DELETE'])]
    public function deleteCustomer(Customer $customer, EntityManager $entityManager, JWTEncoderInterface $jwt, CustomerRepository $customerRepository, int $id, Request $request): JsonResponse
    {
        $tokenJwt = $jwt->decode(substr($request->headers->get('Authorization'), 7));
        $email = $tokenJwt['username'];
        $idCustomer = $customerRepository->findOneByEmail($email)->getId();

        if ($id !== $idCustomer) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "Accès aux données refusé");
        }
        
        $entityManager->remove($customer);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
