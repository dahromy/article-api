<?php

namespace App\Controller;

use App\Document\User;
use App\OpenApi\UserSchema;
use App\Service\UserServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes\Property;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {
    }

    #[OA\Post(
        path: '/api/users',
        summary: 'Create a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: UserSchema::class, groups: ['user:write']))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created successfully',
                content: new OA\JsonContent(ref: new Model(type: UserSchema::class, groups: ['user:read']))
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input'
            ),
            new OA\Response(
                response: 500,
                description: 'Unable to create user'
            )
        ]
    )]
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->createUser($data['email'], $data['password']);
            return $this->json(['id' => $user->getId(), 'email' => $user->getEmail()], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Unable to create user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[OA\Post(
        path: '/api/users/{id}/change-password',
        summary: 'Change user password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [new Property(property: 'newPassword', type: 'string')],
                type: 'object',
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password updated successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input'
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),
            new OA\Response(
                response: 500,
                description: 'Unable to update password'
            )
        ],
    )]
    #[Route('/{id}/change-password', methods: ['POST'])]
    public function changePassword(Request $request, ?User $user = null): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['newPassword'])) {
            return $this->json(['error' => 'New password is required'], Response::HTTP_BAD_REQUEST);
        }

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->userService->updateUserPassword($user, $data['newPassword']);
            return $this->json(['message' => 'Password updated successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Unable to update password'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}