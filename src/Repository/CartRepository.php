<?php

namespace App\Repository;

use App\Entity\Cart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    /**
     * Find cart by ID or create a new one if not found
     *
     * @param int $cartId
     * @return Cart
     */
    public function findOrCreate(int $cartId): Cart
    {
        if ($cartId === 0) {
            // Create a new cart when cartId is 0
            $cart = new Cart();
            $this->getEntityManager()->persist($cart);
            $this->getEntityManager()->flush();
            return $cart;
        }
        
        $cart = $this->find($cartId);
        
        if (!$cart) {
            $cart = new Cart();
            $this->getEntityManager()->persist($cart);
            $this->getEntityManager()->flush();
        }
        
        return $cart;
    }
}
