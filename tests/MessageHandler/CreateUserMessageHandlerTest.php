<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Document\User;
use App\Message\CreateUserMessage;
use App\MessageHandler\CreateUserMessageHandler;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\TestCase;

final class CreateUserMessageHandlerTest extends TestCase
{
    public function testPersistsUserWithAllFieldsFromMessage(): void
    {
        $message = new CreateUserMessage(
            id: '019ddfe4-f531-78d6-a038-f6943439d78b',
            firstName: 'John',
            lastName: 'Doe',
            phoneNumbers: ['+380971234567', '+380631234567'],
            requestIp: '8.8.8.8',
            countryCode: 'US',
            countryName: 'United States',
        );

        $documentManager = $this->createMock(DocumentManager::class);

        $documentManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (mixed $document) use ($message): bool {
                if (!$document instanceof User) {
                    return false;
                }

                return $document->getId() === $message->id
                    && $document->getFirstName() === $message->firstName
                    && $document->getLastName() === $message->lastName
                    && $document->getPhoneNumbers() === $message->phoneNumbers
                    && $document->getRequestIp() === $message->requestIp
                    && $document->getCountryCode() === $message->countryCode
                    && $document->getCountryName() === $message->countryName;
            }));

        $documentManager->expects($this->once())->method('flush');

        $handler = new CreateUserMessageHandler($documentManager);
        $handler($message);
    }
}
