<?php

namespace App\Service;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private DocumentManager             $dm,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Create a new user with email, password, and optional roles.
     * 
     * @param string $email The user's email address
     * @param string $plainPassword The plain text password
     * @param array<string> $roles Optional array of user roles
     * @return User The created user
     * @throws \Throwable
     * @throws MongoDBException
     */
    public function createUser(string $email, string $plainPassword, array $roles = []): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->dm->persist($user);
        $this->dm->flush();

        return $user;
    }

    /**
     * Find a user by their email address.
     * 
     * @param string $email The email address to search for
     * @return User|null The user if found, null otherwise
     */
    public function findUserByEmail(string $email): ?User
    {
        return $this->dm->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    /**
     * Update a user's password with a new plain text password.
     * 
     * @param User $user The user whose password to update
     * @param string $newPlainPassword The new plain text password
     * @throws \Throwable
     * @throws MongoDBException
     */
    public function updateUserPassword(User $user, string $newPlainPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPlainPassword);
        $user->setPassword($hashedPassword);

        $this->dm->flush();
    }

    /**
     * Change a user's password after verifying the current password.
     * 
     * @param User $user The user whose password to change
     * @param string $currentPassword The current password for verification
     * @param string $newPassword The new password
     * @throws \Exception If the current password is incorrect
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        // Verify current password
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            throw new \Exception('Current password is incorrect');
        }

        // Update to new password
        $this->updateUserPassword($user, $newPassword);
    }
}