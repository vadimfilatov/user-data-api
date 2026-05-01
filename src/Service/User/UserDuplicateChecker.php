<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;

final readonly class UserDuplicateChecker
{
    public function __construct(
        private DocumentManager $documentManager,
        private UserIdentityHashGenerator $identityHashGenerator,
    ) {
    }

    public function isDuplicate(string $firstName, string $lastName, array $phoneNumbers): bool
    {
        $identityHash = $this->identityHashGenerator->generate($firstName, $lastName, $phoneNumbers);

        $count = (int) $this->documentManager
            ->createQueryBuilder(User::class)
            ->field('identityHash')->equals($identityHash)
            ->count()
            ->getQuery()
            ->execute();

        return $count > 0;
    }
}
