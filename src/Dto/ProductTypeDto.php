<?php

namespace App\Dto;

class ProductTypeDto
{
    public function __construct(
        public int $id,
        public string $name
    ) {}
}
