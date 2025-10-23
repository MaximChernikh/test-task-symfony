<?php

namespace App\Dto;

class CartItemDto
{
    public function __construct(
        public int $id,
        public int $quantity,
        public ProductDto $product
    ) {}
}
