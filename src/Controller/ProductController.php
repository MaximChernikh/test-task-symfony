<?php

namespace App\Controller;

use App\Dto\ManufacturerDto;
use App\Dto\ModelDto;
use App\Dto\ProductDto;
use App\Dto\ProductTypeDto;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products', name: 'api_products_')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductTypeRepository $productTypeRepository
    ) {}

    #[Route('/by-type/{typeId}', name: 'by_type', methods: ['GET'])]
    public function getProductsByType(int $typeId): JsonResponse
    {
        // Check if product type exists
        $productType = $this->productTypeRepository->find($typeId);
        if (!$productType) {
            return $this->json(['error' => 'Product type not found'], Response::HTTP_NOT_FOUND);
        }

        $products = $this->productRepository->findByProductTypeId($typeId);

        $productDtos = [];
        foreach ($products as $product) {
            $model = $product->getModel();
            $manufacturer = $model->getManufacturer();
            $productType = $model->getProductType();

            $productDtos[] = new ProductDto(
                $product->getId(),
                $product->getName(),
                $product->getPrice(),
                new ModelDto(
                    $model->getId(),
                    $model->getName(),
                    new ManufacturerDto(
                        $manufacturer->getId(),
                        $manufacturer->getName()
                    ),
                    new ProductTypeDto(
                        $productType->getId(),
                        $productType->getName()
                    )
                )
            );
        }

        return $this->json($productDtos);
    }
}
