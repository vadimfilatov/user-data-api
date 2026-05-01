<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Document\User;
use App\Dto\UserListRequestDto;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class UserListService
{
    public function __construct(
        private DocumentManager $documentManager,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function filter(UserListRequestDto $query): array
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

        $normalized = $this->normalizer->normalize($users, context: ['groups' => ['user:list']]);
        $data = is_array($normalized) ? $normalized : [];

        return [
            'data' => $data,
            'pagination' => [
                'page' => $query->page,
                'perPage' => $query->perPage,
                'total' => $total,
            ]
        ];
    }
}
