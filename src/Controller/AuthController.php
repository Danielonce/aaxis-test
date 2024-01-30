<?php

namespace App\Controller;

use App\Service\ApiResponseService;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth')]
class AuthController extends AbstractController
{
    private $authService;
    private $apiResponseService;

    public function __construct(AuthService $authService, ApiResponseService $apiResponseService)
    {
        $this->authService = $authService;
        $this->apiResponseService = $apiResponseService;
    }

    #[Route('/register', name: 'auth_register', methods:['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->authService->registerUser($data['email'], $data['password']);

        $response = [
            'message' => 'User with email ' . $user->getEmail() . ' registered successfully',
        ];

        return $this->apiResponseService->createApiResponse($response, JsonResponse::HTTP_CREATED);
    }

    #[Route('/login', name: 'auth_login', methods:['POST'])]
    public function login(): JsonResponse
    {
        $response = $this->authService->getAuthenticatedUserData();

        return $this->apiResponseService->createApiResponse($response, JsonResponse::HTTP_OK);
    }
}
