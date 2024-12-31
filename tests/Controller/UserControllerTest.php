<?php

namespace App\Tests\Controller;

use App\Document\User;
use App\Service\UserServiceInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private DocumentManager $documentManager;
    private UserServiceInterface $userService;
    protected static $container;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->documentManager = self::$container->get(DocumentManager::class);
        $this->userService = self::$container->get(UserServiceInterface::class);
    }

    public function testCreateUser()
    {
        $client = static::createClient();
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $user = $this->documentManager->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testCreateUserWithInvalidData()
    {
        $client = static::createClient();
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => '',
            'password' => 'password123'
        ]));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testChangePassword()
    {
        $user = $this->userService->createUser('test@example.com', 'password123');

        $client = static::createClient();
        $client->request('POST', '/api/users/' . $user->getId() . '/change-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'newPassword' => 'newpassword123'
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $updatedUser = $this->documentManager->getRepository(User::class)->find($user->getId());
        $this->assertNotNull($updatedUser);
        $this->assertTrue($this->userService->isPasswordValid($updatedUser, 'newpassword123'));
    }

    public function testChangePasswordWithInvalidData()
    {
        $user = $this->userService->createUser('test@example.com', 'password123');

        $client = static::createClient();
        $client->request('POST', '/api/users/' . $user->getId() . '/change-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'newPassword' => ''
        ]));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testChangePasswordForNonExistentUser()
    {
        $client = static::createClient();
        $client->request('POST', '/api/users/nonexistent/change-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'newPassword' => 'newpassword123'
        ]));

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }
}
