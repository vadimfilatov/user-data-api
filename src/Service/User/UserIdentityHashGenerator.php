<?php

declare(strict_types=1);

namespace App\Service\User;

final class UserIdentityHashGenerator
{
    public function generate(string $firstName, string $lastName, array $phoneNumbers): string
    {
        $normalizedFirstName = mb_strtolower(trim($firstName));
        $normalizedLastName = mb_strtolower(trim($lastName));

        $normalizedPhones = array_map(
            static fn (string $phone): string => preg_replace('/\s+/', '', trim($phone)) ?? trim($phone),
            $phoneNumbers,
        );

        $normalizedPhones = array_values(array_unique($normalizedPhones));
        sort($normalizedPhones, SORT_STRING);

        $payload = implode('|', [
            $normalizedFirstName,
            $normalizedLastName,
            implode(',', $normalizedPhones),
        ]);

        return hash('sha256', $payload);
    }
}
