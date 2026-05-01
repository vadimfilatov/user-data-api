<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Document\User;
use App\Dto\UserListRequestDto;
use Doctrine\ODM\MongoDB\DocumentManager;

final readonly class UserListRepository
{
    public function __construct(
        private DocumentManager $documentManager,
    ) {
    }

    public function findByFilter(UserListRequestDto $query): array
    {
        $qb = $this->documentManager->createQueryBuilder(User::class);

        if ($query->id) {
            $qb->field('id')->equals($query->id);
        }

        $total = (int) (clone $qb)
            ->count()
            ->getQuery()
            ->execute();

        $skip = ($query->page - 1) * $query->perPage;
        $users = $qb
            ->sort($query->sortBy, $query->order)
            ->skip($skip)
            ->limit($query->perPage)
            ->getQuery()
            ->execute();

        return [
            'users' => $users,
            'total' => $total,
        ];
    }
}
