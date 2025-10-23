<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Find products by product type ID
     *
     * @param int $productTypeId
     * @return Product[]
     */
    public function findByProductTypeId(int $productTypeId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.model', 'm')
            ->join('m.productType', 'pt')
            ->where('pt.id = :productTypeId')
            ->setParameter('productTypeId', $productTypeId)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
