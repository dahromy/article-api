<?php

namespace App\Tests\EndToEnd;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserApiTest extends WebTestCase
{
    public function testCreateUser()
    {
        $client = static::createClient();
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'testuser@example.com',
            'password' => 'password123'
        ]));

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertEquals('testuser@example.com', $responseData['email']);
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
        $client = static::createClient();
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'testuser@example.com',
            'password' => 'password123'
        ]));

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $userId = $responseData['id'];

        $client->request('POST', '/api/users/' . $userId . '/change-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'newPassword' => 'newpassword123'
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testChangePasswordWithInvalidData()
    {
        $client = static::createClient();
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'testuser@example.com',
            'password' => 'password123'
        ]));

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $userId = $responseData['id'];

        $client->request('POST', '/api/users/' . $userId . '/change-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
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
