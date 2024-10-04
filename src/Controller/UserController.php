<?php

namespace App\Controller;

use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {
    }

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

    #[Route('/{id}/change-password', methods: ['POST'])]
    public function changePassword(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['newPassword'])) {
            return $this->json(['error' => 'New password is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userService->findUserByEmail($id);

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