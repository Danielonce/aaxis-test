<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\SerializerInterface;

class ProductService
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function getProducts($skuFilter, $name, $page, $itemsPerPage): array
    {
        $repository = $this->entityManager->getRepository(Product::class);

        $queryBuilder = $repository->createQueryBuilder('p');

        if ($skuFilter) {
            $queryBuilder
                ->andWhere('p.sku LIKE :sku')
                ->setParameter('sku', "%$skuFilter%");
        }

        if ($name) {
            $queryBuilder
                ->andWhere('p.productName LIKE :name')
                ->setParameter('name', "%$name%");
        }

        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $products = iterator_to_array($paginator->getIterator());

        $jsonProducts = $this->serializer->serialize($products, 'json');

        $payload = [
            'data' => json_decode($jsonProducts, true),
            'meta' => [
                'totalItems' => $paginator->count(),
                'currentPage' => $page,
                'itemsPerPage' => $itemsPerPage,
            ],
        ];

        return $this->serializeProducts($payload);
    }

    public function createProducts(array $data): array
    {
        $createdProducts = [];

        foreach ($data as $productData) {
            $product = new Product();
            $product->setSku($productData['sku']);
            $product->setProductName($productData['productName']);
            $product->setDescription($productData['description']);
            $product->setCreatedAt(new \DateTime());

            $this->entityManager->persist($product);
            $createdProducts[] = $product;
        }

        $this->entityManager->flush();

        return $this->serializeProducts($createdProducts);
    }

    public function updateProducts(array $data): array
    {
        $updatedProducts = [];

        foreach ($data as $productData) {
            if (!isset($productData['sku'])) {
                throw new \InvalidArgumentException('SKU is required for each product');
            }

            $sku = $productData['sku'];
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['sku' => $sku]);

            if (!$product) {
                throw new \InvalidArgumentException("Product with SKU $sku not found");
            }

            if (isset($productData['productName'])) {
                $product->setProductName($productData['productName']);
            }

            if (isset($productData['description'])) {
                $product->setDescription($productData['description']);
            }
            $product->setUpdatedAt(new \DateTime());

            $this->entityManager->persist($product);
            $updatedProducts[] = $product;
        }
        $this->entityManager->flush();

        return $this->serializeProducts($updatedProducts);
    }

    private function serializeProducts(array $products): array
    {
        return json_decode($this->serializer->serialize($products, 'json'), true);
    }
}
