<?php

declare(strict_types=1);

namespace App\Message;

final readonly class CreateUserMessage
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public array $phoneNumbers,
        public string $requestIp,
    ) {
    }
}
