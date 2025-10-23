<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/cart', name: 'api_cart_')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private SessionInterface $session
    ) {}

    #[Route('', name: 'get', methods: ['GET'])]
    public function getCart(): JsonResponse
    {
        $cart = $this->getOrCreateCartFromSession();
        $cartSummary = $this->cartService->getCartSummary($cart);
        
        return $this->json($cartSummary);
    }

    #[Route('/items', name: 'add_item', methods: ['POST'])]
    public function addItemToCart(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['productId']) || !is_numeric($data['productId'])) {
            return $this->json(['error' => 'Invalid productId'], Response::HTTP_BAD_REQUEST);
        }

        $cart = $this->getOrCreateCartFromSession();

        try {
            $this->cartService->addProduct($cart, (int)$data['productId']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $cartSummary = $this->cartService->getCartSummary($cart);
        
        return $this->json($cartSummary, Response::HTTP_CREATED);
    }

    #[Route('/items/{productId}', name: 'remove_item', methods: ['DELETE'])]
    public function removeItemFromCart(int $productId): JsonResponse
    {
        $cartId = $this->session->get('cart_id');
        
        if (!$cartId) {
            return $this->json(['error' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        }

        $cart = $this->cartService->getOrCreateCart($cartId);
        $this->cartService->removeProduct($cart, $productId);

        $cartSummary = $this->cartService->getCartSummary($cart);
        
        return $this->json($cartSummary);
    }

    private function getOrCreateCartFromSession(): \App\Entity\Cart
    {
        $cartId = $this->session->get('cart_id');
        
        if (!$cartId) {
            // Create a new cart and store its ID in session
            $cart = $this->cartService->getOrCreateCart(0);
            $this->session->set('cart_id', $cart->getId());
            return $cart;
        }
        
        return $this->cartService->getOrCreateCart($cartId);
    }
}
