<?php

namespace App\Dto;

class ModelDto
{
    public function __construct(
        public int $id,
        public string $name,
        public ManufacturerDto $manufacturer,
        public ProductTypeDto $productType
    ) {}
}
