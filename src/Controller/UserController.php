<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateUserRequestDto;
use App\Dto\UserListRequestDto;
use App\Message\CreateUserMessage;
use App\Service\Geo\IpLocaleService;
use App\Service\Ip\ClientIpService;
use App\Service\User\UserDuplicateChecker;
use App\Service\User\UserListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
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
    public function list(
        #[MapQueryString] UserListRequestDto $userListRequestDto,
        UserListService $userListService,
    ): JsonResponse
    {
        return new JsonResponse($userListService->filter($userListRequestDto), Response::HTTP_OK);
    }

    #[Route('', name: 'api_user_create', methods: ['POST'])]
    public function create(
        Request $request,
        #[MapRequestPayload] CreateUserRequestDto $createUserRequestDto,
        UserDuplicateChecker $userDuplicateChecker,
        ClientIpService $clientIpService,
        IpLocaleService $ipLocaleService,
        MessageBusInterface $messageBus,
    ): JsonResponse
    {
        if ($userDuplicateChecker->isDuplicate($createUserRequestDto->firstName, $createUserRequestDto->lastName, $createUserRequestDto->phoneNumbers)) {
            return new JsonResponse(['message' => 'User with same data already exists'], Response::HTTP_CONFLICT);
        }

        $id = Uuid::v7()->toRfc4122();
        $requestIp = $clientIpService->getClientIp($request);
        $countryInfo = $ipLocaleService->getCountryInfo($requestIp);

        $messageBus->dispatch(new CreateUserMessage(
            id: $id,
            firstName: $createUserRequestDto->firstName,
            lastName: $createUserRequestDto->lastName,
            phoneNumbers: $createUserRequestDto->phoneNumbers,
            requestIp: $requestIp,
            countryCode: $countryInfo->countryCode,
            countryName: $countryInfo->countryName,
        ));

        return new JsonResponse(
            [
                'message' => 'User accepted',
                'id' => $id,
            ],
            Response::HTTP_ACCEPTED,
        );
    }
}
