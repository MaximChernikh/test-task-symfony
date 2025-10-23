<?php

namespace App\Service;

use App\Dto\CartDto;
use App\Dto\CartItemDto;
use App\Dto\ManufacturerDto;
use App\Dto\ModelDto;
use App\Dto\ProductDto;
use App\Dto\ProductTypeDto;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    public function __construct(
        private CartRepository $cartRepository,
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function getOrCreateCart(int $cartId): Cart
    {
        return $this->cartRepository->findOrCreate($cartId);
    }

    public function addProduct(Cart $cart, int $productId): void
    {
        $product = $this->productRepository->find($productId);
        if (!$product) {
            throw new \InvalidArgumentException('Product not found');
        }

        // Check if product already exists in cart
        $existingCartItem = null;
        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getProduct()->getId() === $productId) {
                $existingCartItem = $cartItem;
                break;
            }
        }

        if ($existingCartItem) {
            // Increment quantity if product already exists
            $existingCartItem->setQuantity($existingCartItem->getQuantity() + 1);
        } else {
            // Create new cart item
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);
            $cart->addCartItem($cartItem);
        }

        $this->entityManager->flush();
    }

    public function removeProduct(Cart $cart, int $productId): void
    {
        $cartItemToRemove = null;
        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getProduct()->getId() === $productId) {
                $cartItemToRemove = $cartItem;
                break;
            }
        }

        if ($cartItemToRemove) {
            $cart->removeCartItem($cartItemToRemove);
            $this->entityManager->remove($cartItemToRemove);
            $this->entityManager->flush();
        }
    }

    public function getCartSummary(Cart $cart): CartDto
    {
        $totalItems = 0;
        $totalCost = '0.00';
        $items = [];

        foreach ($cart->getCartItems() as $cartItem) {
            $totalItems += $cartItem->getQuantity();
            $itemCost = bcmul($cartItem->getProduct()->getPrice(), (string)$cartItem->getQuantity(), 2);
            $totalCost = bcadd($totalCost, $itemCost, 2);

            $product = $cartItem->getProduct();
            $model = $product->getModel();
            $manufacturer = $model->getManufacturer();
            $productType = $model->getProductType();

            $items[] = new CartItemDto(
                $cartItem->getId(),
                $cartItem->getQuantity(),
                new ProductDto(
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
                )
            );
        }

        return new CartDto(
            $cart->getId(),
            $totalItems,
            $totalCost,
            $items
        );
    }
}
