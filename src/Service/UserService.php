<?php

namespace App\Service;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly DocumentManager $dm,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * @throws \Throwable
     * @throws MongoDBException
     */
    public function createUser(string $email, string $plainPassword): User
    {
        $user = new User();
        $user->setEmail($email);

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
}