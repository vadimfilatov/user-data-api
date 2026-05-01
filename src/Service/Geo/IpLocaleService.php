<?php

declare(strict_types=1);

namespace App\Service\Geo;

use App\Dto\CountryInfoDto;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class IpLocaleService
{
    private const string IP_LOCATE_URL = 'https://www.iplocate.io/api/lookup/';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function getCountryInfo(string $ip): CountryInfoDto
    {
        try {
            $response = $this->httpClient->request('GET', self::IP_LOCATE_URL . rawurlencode($ip), [
                'timeout' => 5.0,
            ]);

            $data = $response->toArray();

            $countryCode = $this->extractString($data['country_code'] ?? null);
            $countryName = $this->extractString($data['country'] ?? null);

            return new CountryInfoDto($countryCode, $countryName);
        } catch (\Throwable $exception) {
            $this->logger->warning('Cannot get country info', ['exception' => $exception]);

            return new CountryInfoDto(null, null);
        }
    }

    private function extractString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
