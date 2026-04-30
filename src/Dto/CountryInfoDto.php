<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class CountryInfoDto
{
    public function __construct(
        public ?string $countryCode,
        public ?string $countryName,
    ) {
    }
}
