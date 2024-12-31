<?php

namespace App\Tests\Service;

use App\Document\User;
use App\Service\UserService;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends KernelTestCase
{
    private UserService $userService;
    private DocumentManager|MockObject $documentManager;
    private UserPasswordHasherInterface|MockObject $passwordHasher;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->userService = new UserService(
            $this->documentManager,
            $this->passwordHasher
        );
    }

    public function testCreateUser()
    {
        $email = 'test@example.com';
        $plainPassword = 'password123';
        $hashedPassword = 'hashed_password';

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), $plainPassword)
            ->willReturn($hashedPassword);

        $this->documentManager->expects($this->once())
            ->method('persist');
        $this->documentManager->expects($this->once())
            ->method('flush');

        $user = $this->userService->createUser($email, $plainPassword);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($hashedPassword, $user->getPassword());
    }

    public function testFindUserByEmail()
    {
        $email = 'test@example.com';
        $user = new User();
        $user->setEmail($email);

        $repositoryMock = $this->createMock(DocumentManager::class);
        $repositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        $this->documentManager->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repositoryMock);

        $result = $this->userService->findUserByEmail($email);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->getEmail());
    }

    public function testUpdateUserPassword()
    {
        $user = new User();
        $newPlainPassword = 'new_password123';
        $hashedPassword = 'hashed_new_password';

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, $newPlainPassword)
            ->willReturn($hashedPassword);

        $this->documentManager->expects($this->once())
            ->method('flush');

        $this->userService->updateUserPassword($user, $newPlainPassword);

        $this->assertEquals($hashedPassword, $user->getPassword());
    }
}
