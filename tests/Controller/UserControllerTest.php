<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Message\CreateUserMessage;
use App\Service\Geo\IpLocaleService;
use App\Service\Ip\ClientIpService;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(\App\Controller\UserController::class)]
final class UserControllerTest extends WebTestCase
{
    public function testCreateUserReturnsAcceptedAndDispatchesMessage(): void
    {
        $client = static::createClient();

        $logger = $this->createStub(LoggerInterface::class);
        $ipLocaleService = new IpLocaleService(
            new MockHttpClient([
                new MockResponse('{"country_code":"US","country":"United States"}', ['http_code' => 200]),
            ]),
            $logger,
        );

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (mixed $message): bool {
                return $message instanceof CreateUserMessage
                    && $message->firstName === 'John'
                    && $message->lastName === 'Doe'
                    && $message->phoneNumbers === ['+380971234567']
                    && $message->requestIp === '8.8.8.8'
                    && $message->countryCode === 'US'
                    && $message->countryName === 'United States';
            }))
            ->willReturnCallback(static fn (object $message): Envelope => new Envelope($message));

        static::getContainer()->set(ClientIpService::class, new ClientIpService());
        static::getContainer()->set(IpLocaleService::class, $ipLocaleService);
        static::getContainer()->set(MessageBusInterface::class, $bus);

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => '8.8.8.8'],
            content: json_encode([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'phoneNumbers' => ['+380971234567'],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(202);

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('User accepted', $payload['message']);
        self::assertMatchesRegularExpression('/^[0-9a-fA-F-]{36}$/', $payload['id']);
    }

    public function testCreateUserReturnsValidationErrorAsJson(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => '8.8.8.8'],
            content: json_encode([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'phoneNumbers' => [],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Validation failed.', $payload['message']);
        self::assertArrayHasKey('errors', $payload);
        self::assertNotEmpty($payload['errors']);
    }

    public function testListUsersReturnsValidContract(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/users?sortBy=id&order=desc&page=1&perPage=20');

        self::assertResponseStatusCodeSame(200);

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($payload);
        self::assertArrayHasKey('data', $payload);
        self::assertArrayHasKey('pagination', $payload);

        self::assertIsArray($payload['data']);

        self::assertIsArray($payload['pagination']);
        self::assertArrayHasKey('page', $payload['pagination']);
        self::assertArrayHasKey('perPage', $payload['pagination']);
        self::assertArrayHasKey('total', $payload['pagination']);

        self::assertSame(1, $payload['pagination']['page']);
        self::assertSame(20, $payload['pagination']['perPage']);
        self::assertIsInt($payload['pagination']['total']);
        self::assertGreaterThanOrEqual(0, $payload['pagination']['total']);

        foreach ($payload['data'] as $row) {
            self::assertIsArray($row);
            self::assertArrayHasKey('id', $row);
            self::assertArrayHasKey('firstName', $row);
            self::assertArrayHasKey('lastName', $row);
            self::assertArrayHasKey('phoneNumbers', $row);
            self::assertArrayHasKey('requestIp', $row);
            self::assertArrayHasKey('countryCode', $row);
            self::assertArrayHasKey('countryName', $row);
        }
    }
}
