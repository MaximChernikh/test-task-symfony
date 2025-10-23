<?php

namespace App\Dto;

class ProductDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $price,
        public ModelDto $model
    ) {}
}
