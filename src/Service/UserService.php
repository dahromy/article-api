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

    public function findUserByEmail(string $email): ?User
    {
        return $this->dm->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    /**
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
     * @throws \Exception
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