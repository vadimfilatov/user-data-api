<?php

declare(strict_types=1);

namespace App\Util;

use Symfony\Component\Uid\Uuid;

final class UuidGenerator
{
    private function __construct()
    {
    }

    public static function v7(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
