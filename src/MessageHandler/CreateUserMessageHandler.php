<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Document\User;
use App\Message\CreateUserMessage;
use App\Service\User\UserIdentityHashGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateUserMessageHandler
{
    public function __construct(
        private DocumentManager $documentManager,
        private UserIdentityHashGenerator $userIdentityHashGenerator,
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
        $user->setIdentityHash($this->userIdentityHashGenerator->generate(
            $message->firstName,
            $message->lastName,
            $message->phoneNumbers,
        ));

        $this->documentManager->persist($user);
        $this->documentManager->flush();
    }
}
