<?php

namespace App\Service;

use App\Document\User;

interface UserServiceInterface
{
    public function createUser(string $email, string $plainPassword): User;
    public function findUserByEmail(string $email): ?User;
    public function updateUserPassword(User $user, string $newPlainPassword): void;
}