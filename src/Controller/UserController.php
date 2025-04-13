<?php

namespace App\Controller;

use App\Document\User;
use App\DTO\ChangePasswordDTO;
use App\DTO\CreateUserDTO;
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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[OA\Post(
        path: '/api/users',
        summary: 'Create a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateUserDTO::class))
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
        try {
            $createUserDTO = $this->serializer->deserialize(
                $request->getContent(),
                CreateUserDTO::class,
                'json'
            );
            
            $violations = $this->validator->validate($createUserDTO);
            
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                
                return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            }
            
            $user = $this->userService->createUser(
                $createUserDTO->getEmail(), 
                $createUserDTO->getPassword(),
                $createUserDTO->getRoles()
            );
            
            return $this->json([
                'id' => $user->getId(),
                'email' => $user->getEmail()
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return $this->json(['error' => 'Unable to create user: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[OA\Post(
        path: '/api/users/{id}/change-password',
        summary: 'Change user password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: ChangePasswordDTO::class))
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
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $changePasswordDTO = $this->serializer->deserialize(
                $request->getContent(),
                ChangePasswordDTO::class,
                'json'
            );
            
            $violations = $this->validator->validate($changePasswordDTO);
            
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                
                return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            }
            
            $this->userService->changePassword(
                $user, 
                $changePasswordDTO->getCurrentPassword(), 
                $changePasswordDTO->getNewPassword()
            );
            
            return $this->json(['message' => 'Password updated successfully']);
            
        } catch (\Exception $e) {
            return $this->json(['error' => 'Unable to update password: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}