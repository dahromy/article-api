<?php

namespace App\Service;

use App\Document\User;

interface UserServiceInterface
{
    /**
     * Create a new user with email, password, and optional roles.
     * 
     * @param string $email The user's email address
     * @param string $plainPassword The plain text password
     * @param array<string> $roles Optional array of user roles
     * @return User The created user
     */
    public function createUser(string $email, string $plainPassword, array $roles = []): User;
    
    /**
     * Find a user by their email address.
     * 
     * @param string $email The email address to search for
     * @return User|null The user if found
     */
    public function findUserByEmail(string $email): ?User;
    
    /**
     * Update a user's password with a new plain text password.
     * 
     * @param User $user The user whose password to update
     * @param string $newPlainPassword The new plain text password
     */
    public function updateUserPassword(User $user, string $newPlainPassword): void;
    
    /**
     * Change a user's password after verifying the current password.
     * 
     * @param User $user The user whose password to change
     * @param string $currentPassword The current password for verification
     * @param string $newPassword The new password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void;
}