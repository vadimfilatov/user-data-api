<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserListRequestDto
{
    public function __construct(
        #[Assert\Uuid]
        public ?string $id = null,

        #[Assert\Choice(choices: ['id', 'lastName'])]
        public string $sortBy = 'id',

        #[Assert\Choice(choices: ['asc', 'desc'])]
        public string $order = 'desc',

        #[Assert\Positive]
        public int $page = 1,

        #[Assert\Range(min: 1, max: 200)]
        public int $perPage = 20,
    ) {
    }
}
