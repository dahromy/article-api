<?php

namespace App\Service;

use App\Document\User;

interface UserServiceInterface
{
    public function createUser(string $email, string $plainPassword, array $roles = []): User;
    public function findUserByEmail(string $email): ?User;
    public function updateUserPassword(User $user, string $newPlainPassword): void;
    public function changePassword(User $user, string $currentPassword, string $newPassword): void;
}