<?php

declare(strict_types=1);

namespace App\Tests\Service\Geo;

use App\Service\Geo\IpLocaleService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class IpLocaleServiceTest extends TestCase
{
    public function testReturnsCountryInfoWhenLookupSucceeds(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"country_code":"US","country":"United States"}', ['http_code' => 200]),
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');

        $service = new IpLocaleService($httpClient, $logger);

        $result = $service->getCountryInfo('8.8.8.8');

        self::assertSame('US', $result->countryCode);
        self::assertSame('United States', $result->countryName);
    }

    public function testReturnsNullsAndLogsWarningWhenLookupFails(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('Service unavailable', ['http_code' => 503]),
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $service = new IpLocaleService($httpClient, $logger);

        $result = $service->getCountryInfo('8.8.4.4');

        self::assertNull($result->countryCode);
        self::assertNull($result->countryName);
    }
}
