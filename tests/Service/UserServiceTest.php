<?php

namespace App\Tests\Service;

use App\Document\User;
use App\Service\UserService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends KernelTestCase
{
    private UserService $userService;
    private DocumentManager|MockObject $documentManager;
    private UserPasswordHasherInterface|MockObject $passwordHasher;
    private DocumentRepository|MockObject $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userRepository = $this->createMock(DocumentRepository::class);

        // Mock the repository getter
        $this->documentManager->expects($this->any())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);

        $this->userService = new UserService(
            $this->documentManager,
            $this->passwordHasher
        );
    }

    public function testCreateUser()
    {
        $email = 'test@example.com';
        $plainPassword = 'password123';
        $roles = ['ROLE_ADMIN'];
        $hashedPassword = 'hashed_password_123';

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($hashedPassword);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), $plainPassword)
            ->willReturn($hashedPassword);

        $this->documentManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));

        $this->documentManager->expects($this->once())
            ->method('flush');

        $result = $this->userService->createUser($email, $plainPassword, $roles);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->getEmail());
        $this->assertEquals($hashedPassword, $result->getPassword());
        $this->assertContains('ROLE_ADMIN', $result->getRoles());
    }

    public function testFindUserByEmail()
    {
        $email = 'test@example.com';
        $user = new User();
        $user->setEmail($email);

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        $result = $this->userService->findUserByEmail($email);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->getEmail());
    }

    public function testFindUserByEmailNotFound()
    {
        $email = 'nonexistent@example.com';

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(null);

        $result = $this->userService->findUserByEmail($email);

        $this->assertNull($result);
    }

    public function testUpdateUserPassword()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('old_hashed_password');

        $newPlainPassword = 'new_password_123';
        $newHashedPassword = 'new_hashed_password_123';

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, $newPlainPassword)
            ->willReturn($newHashedPassword);

        $this->documentManager->expects($this->once())
            ->method('flush');

        $this->userService->updateUserPassword($user, $newPlainPassword);

        $this->assertEquals($newHashedPassword, $user->getPassword());
    }

    public function testChangePassword()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('old_hashed_password');

        $currentPassword = 'current_password';
        $newPassword = 'new_password_123';
        $newHashedPassword = 'new_hashed_password_123';

        // Mock password validation
        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $currentPassword)
            ->willReturn(true);

        // Mock password hashing
        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, $newPassword)
            ->willReturn($newHashedPassword);

        $this->documentManager->expects($this->once())
            ->method('flush');

        $this->userService->changePassword($user, $currentPassword, $newPassword);

        $this->assertEquals($newHashedPassword, $user->getPassword());
    }

    public function testChangePasswordWithInvalidCurrentPassword()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('old_hashed_password');

        $currentPassword = 'wrong_password';
        $newPassword = 'new_password_123';

        // Mock password validation to return false
        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $currentPassword)
            ->willReturn(false);

        // Expect no password hashing or flush to be called
        $this->passwordHasher->expects($this->never())
            ->method('hashPassword');
        $this->documentManager->expects($this->never())
            ->method('flush');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Current password is incorrect');

        $this->userService->changePassword($user, $currentPassword, $newPassword);
    }
}