<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ApiResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/auth')]
class AuthController extends AbstractController
{
    private $apiResponseService;
    private $entityManager;

    public function __construct(ApiResponseService $apiResponseService, EntityManagerInterface $entityManager)
    {
        $this->apiResponseService = $apiResponseService;
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'auth_register', methods:['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $response = [
            'message' => 'User registered successfully',
            'user' => ['email' => $user->getEmail()],
        ];

        return $this->apiResponseService->createApiResponse($response, JsonResponse::HTTP_CREATED);
    }

    #[Route('/login', name: 'auth_login', methods:['POST'])]
    public function login(): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        $response = [
            'message' => 'User authenticated successfully',
            'user' => ['email' => $user->getEmail()],
        ];

        return $this->apiResponseService->createApiResponse($response, JsonResponse::HTTP_OK);
    }
}
