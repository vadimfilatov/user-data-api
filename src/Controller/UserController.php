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
    #[OA\Get(path: '/api/users', summary: 'User list', tags: ['Users'])]
    #[OA\Parameter(name: 'id', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Parameter(name: 'sortBy', in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'id', enum: ['id', 'lastName']))]
    #[OA\Parameter(name: 'order', in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'desc', enum: ['asc', 'desc']))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1, minimum: 1))]
    #[OA\Parameter(name: 'perPage', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20, maximum: 200, minimum: 1))]
    #[OA\Response(
        response: 200,
        description: 'User list',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: User::class, groups: ['user:list'])),
                ),
                new OA\Property(
                    property: 'pagination',
                    properties: [
                        new OA\Property(property: 'page', type: 'integer'),
                        new OA\Property(property: 'perPage', type: 'integer'),
                        new OA\Property(property: 'total', type: 'integer'),
                        new OA\Property(property: 'totalPages', type: 'integer'),
                    ],
                    type: 'object',
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: 400,
        description: 'Validation or request error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation failed.'),
                new OA\Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'field', type: 'string'),
                            new OA\Property(property: 'message', type: 'string'),
                        ],
                        type: 'object',
                    ),
                    nullable: true,
                ),
            ],
        ),
    )]
    #[Route('', name: 'api_users_list', methods: ['GET'])]
    public function list(
        #[MapQueryString] UserListRequestDto $userListRequestDto,
        UserListService $userListService,
    ): JsonResponse
    {
        return new JsonResponse($userListService->filter($userListRequestDto), Response::HTTP_OK);
    }

    #[OA\Post(
        path: '/api/users',
        summary: 'Create user',
        tags: ['Users'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['firstName', 'lastName', 'phoneNumbers'],
            properties: [
                new OA\Property(property: 'firstName', type: 'string', example: 'John', maxLength: 100),
                new OA\Property(property: 'lastName', type: 'string', example: 'Doe', maxLength: 100),
                new OA\Property(
                    property: 'phoneNumbers',
                    type: 'array',
                    items: new OA\Items(type: 'string', example: '+380971234567'),
                    minItems: 1,
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: 202,
        description: 'User accepted',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'User accepted'),
                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
            ],
        ),
    )]
    #[OA\Response(
        response: 400,
        description: 'Validation or request error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation failed.'),
                new OA\Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'field', type: 'string'),
                            new OA\Property(property: 'message', type: 'string'),
                        ],
                        type: 'object',
                    ),
                    nullable: true,
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: 409,
        description: 'Duplicate user',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'User with same data already exists'),
            ],
        ),
    )]
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
