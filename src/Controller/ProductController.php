<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\ApiResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/product')]
class ProductController extends AbstractController
{
    private $apiResponseService;

    public function __construct(ApiResponseService $apiResponseService)
    {
        $this->apiResponseService = $apiResponseService;
    }

    #[Route('/', name: 'product_get', methods:['GET'])]
    public function index(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $repository = $entityManager->getRepository(Product::class);
    
        $skuFilter = $request->query->get('sku');
        $name = $request->query->get('name');
        $page = $request->query->getInt('page', 1);
        $itemsPerPage = $request->query->getInt('limit', 10);
    
        $queryBuilder = $repository->createQueryBuilder('p');
    
        if ($skuFilter) {
            $queryBuilder
                ->andWhere('p.sku LIKE :sku')
                ->setParameter('sku', "%$skuFilter%")
            ;
        }

        if ($name) {
            $queryBuilder
                ->andWhere('p.productName LIKE :name')
                ->setParameter('name', "%$name%")
            ;
        }
    
        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);
    
        $products = $paginator->getIterator()->getArrayCopy();
    
        $jsonProducts = $serializer->serialize($products, 'json');
    
        $response = [
            'data' => json_decode($jsonProducts, true),
            'meta' => [
                'totalItems' => $paginator->count(),
                'currentPage' => $page,
                'itemsPerPage' => $itemsPerPage,
            ],
        ];

        return $this->apiResponseService->createApiResponse($response, JsonResponse::HTTP_OK);
    }

    #[Route('/create', name: 'product_create', methods:['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer): Response
    {
        $data = json_decode($request->getContent(), true);
        try {
            $createdProducts = [];
    
            foreach ($data as $productData) {
                $product = new Product();
                $product->setSku($productData['sku']);
                $product->setProductName($productData['productName']);
                $product->setDescription($productData['description']);
    
                $entityManager->persist($product);
                $createdProducts[] = $product;
            }
    
            $entityManager->flush();
    
            $jsonProducts = $serializer->serialize($createdProducts, 'json', [
                AbstractNormalizer::GROUPS => ['product'],
            ]);
    
            $response = [
                'message' => 'Products created successfully',
                'data' => json_decode($jsonProducts, true),
            ];

            return $this->apiResponseService->createApiResponse($response, JsonResponse::HTTP_CREATED);   
        } catch (\Throwable $th) {
            return $this->apiResponseService->createApiError($th->getMessage(), JsonResponse::HTTP_CONFLICT);
        }
    }
}
