<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/api/users')]
final class UserController extends AbstractController
{
    #[Required]
    public DocumentManager $documentManager;

    #[Route('', name: 'api_users_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return new JsonResponse([]);
    }

    #[Route('', name: 'api_user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return new JsonResponse([]);
    }
}
