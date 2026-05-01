<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Dto\UserListRequestDto;
use App\Repository\User\UserListRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class UserListService
{
    public function __construct(
        private UserListRepository $userListRepository,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function filter(UserListRequestDto $query): array
    {
        $result = $this->userListRepository->findByFilter($query);

        $normalized = $this->normalizer->normalize($result['users'], context: ['groups' => ['user:list']]);
        $data = is_array($normalized) ? $normalized : [];

        return [
            'data' => $data,
            'pagination' => [
                'page' => $query->page,
                'perPage' => $query->perPage,
                'total' => $result['total'],
            ],
        ];
    }
}
