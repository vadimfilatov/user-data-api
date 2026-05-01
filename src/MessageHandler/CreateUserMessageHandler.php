<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Document\User;
use App\Message\CreateUserMessage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateUserMessageHandler
{
    public function __construct(
        private DocumentManager $documentManager
    ) {
    }

    public function __invoke(CreateUserMessage $message): void
    {
        $user = new User();
        $user->setId($message->id);
        $user->setFirstName($message->firstName);
        $user->setLastName($message->lastName);
        $user->setPhoneNumbers($message->phoneNumbers);
        $user->setRequestIp($message->requestIp);
        $user->setCountryCode($message->countryCode);
        $user->setCountryName($message->countryName);

        $this->documentManager->persist($user);
        $this->documentManager->flush();
    }
}
