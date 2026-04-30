<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateUserRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 100)]
        public readonly string $firstName,

        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 100)]
        public readonly string $lastName,

        #[Assert\NotNull]
        #[Assert\Type('array')]
        #[Assert\Count(min: 1, minMessage: 'At least one phone number is required.')]
        #[Assert\All([
            new Assert\NotBlank(),
            new Assert\Regex(
                pattern: '/^\+[1-9]\d{7,14}$/',
                message: 'Phone number must be in format: +380971234567.',
            ),
        ])]
        public readonly array $phoneNumbers,
    ) {
    }
}
