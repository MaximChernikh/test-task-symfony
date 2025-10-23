<?php

namespace App\Dto;

class ManufacturerDto
{
    public function __construct(
        public int $id,
        public string $name
    ) {}
}
