<?php

namespace App\Controller;

use App\Service\ApiResponseService;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/product')]
class ProductController extends AbstractController
{
    private $apiResponseService;
    private $productService;

    public function __construct(ApiResponseService $apiResponseService, ProductService $productService)
    {
        $this->apiResponseService = $apiResponseService;
        $this->productService = $productService;
    }

    #[Route('/', name: 'product_get', methods:['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $skuFilter = $request->query->get('sku');
        $name = $request->query->get('name');
        $page = $request->query->getInt('page', 1);
        $itemsPerPage = $request->query->getInt('limit', 10);

        $response = $this->productService->getProducts($skuFilter, $name, $page, $itemsPerPage);

        return $this->apiResponseService->createApiResponse($response, JsonResponse::HTTP_OK);
    }

    #[Route('/create', name: 'product_create', methods:['POST'])]
    public function create(Request $request, SerializerInterface $serializer): Response
    {
        $data = json_decode($request->getContent(), true);
        
        try {
            $createdProducts = $this->productService->createProducts($data);

            $response = [
                'message' => 'Products created successfully',
                'data' => $createdProducts,
            ];

            return $this->apiResponseService->createApiResponse($response, JsonResponse::HTTP_CREATED);
        } catch (\Throwable $th) {
            return $this->apiResponseService->createApiError($th->getMessage(), JsonResponse::HTTP_CONFLICT);
        }
    }

    #[Route('/update', name: 'product_update', methods: ['PATCH'])]
    public function update(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || empty($data)) {
            return $this->apiResponseService->createApiError('Invalid JSON payload', JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $updatedProducts = $this->productService->updateProducts($data);

            $response = [
                'message' => 'Products updated successfully',
                'data' => $updatedProducts,
            ];

            return $this->apiResponseService->createApiResponse($response, JsonResponse::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return $this->apiResponseService->createApiError($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
