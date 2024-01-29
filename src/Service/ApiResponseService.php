<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponseService
{
    public function createApiResponse(array $data, int $statusCode = JsonResponse::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }

    public function createApiError(string $errorMessage, int $statusCode = JsonResponse::HTTP_BAD_REQUEST): JsonResponse
    {
        $response = [
            'error' => [
                'message' => $errorMessage,
            ],
        ];

        return new JsonResponse($response, $statusCode);
    }
}
