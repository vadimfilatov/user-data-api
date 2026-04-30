<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateUserRequestDto;
use App\Message\CreateUserMessage;
use App\Service\Ip\ClientIpService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/users')]
final class UserController extends AbstractController
{
    #[Route('', name: 'api_users_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return new JsonResponse([]);
    }

    #[Route('', name: 'api_user_create', methods: ['POST'])]
    public function create(
        Request $request,
        #[MapRequestPayload] CreateUserRequestDto $createUserRequestDto,
        ClientIpService $clientIpService,
        MessageBusInterface $messageBus,
    ): JsonResponse
    {
        $id = Uuid::v7()->toRfc4122();

        $messageBus->dispatch(new CreateUserMessage(
            id: $id,
            firstName: $createUserRequestDto->firstName,
            lastName: $createUserRequestDto->lastName,
            phoneNumbers: $createUserRequestDto->phoneNumbers,
            requestIp: $clientIpService->getClientIp($request),
        ));

        return new JsonResponse(
            [
                'message' => 'User created',
                'id' => $id
            ], Response::HTTP_ACCEPTED);
    }
}
