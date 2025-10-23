<?php

namespace App\Dto;

class CartDto
{
    public function __construct(
        public int $id,
        public int $totalItems,
        public string $totalCost,
        public array $items
    ) {}
}
