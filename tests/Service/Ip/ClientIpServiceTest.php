<?php

declare(strict_types=1);

namespace App\Tests\Service\Ip;

use App\Service\Ip\ClientIpService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class ClientIpServiceTest extends TestCase
{
    public function testReturnsClientIpFromRequest(): void
    {
        $request = new Request(server: ['REMOTE_ADDR' => '203.0.113.10']);

        $service = new ClientIpService();

        self::assertSame('203.0.113.10', $service->getClientIp($request));
    }

    public function testThrowsExceptionWhenIpIsInvalid(): void
    {
        $request = new Request(server: ['REMOTE_ADDR' => 'invalid-ip']);

        $service = new ClientIpService();

        $this->expectException(BadRequestHttpException::class);

        $service->getClientIp($request);
    }
}
