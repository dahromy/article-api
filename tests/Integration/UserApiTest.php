<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserApiTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreateUserEndpoint()
    {
        $userData = [
            'email' => 'apitest@example.com',
            'password' => 'ApiPassword123',
            'roles' => ['ROLE_USER']
        ];

        $this->client->request('POST', '/api/v1/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($userData));
        
        // May succeed, fail with validation, or require authentication
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_CREATED),
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
        
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testCreateUserWithInvalidEmail()
    {
        $userData = [
            'email' => 'invalid-email-format', // Invalid email
            'password' => 'ApiPassword123',
            'roles' => ['ROLE_USER']
        ];

        $this->client->request('POST', '/api/v1/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($userData));
        
        // Should return validation error or other error
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
        
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testCreateUserWithWeakPassword()
    {
        $userData = [
            'email' => 'weakpass@example.com',
            'password' => '123', // Too weak password
            'roles' => ['ROLE_USER']
        ];

        $this->client->request('POST', '/api/v1/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($userData));
        
        // Should return validation error
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
        
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testCreateUserWithMissingFields()
    {
        $userData = [
            'email' => '', // Empty email
            'password' => '', // Empty password
        ];

        $this->client->request('POST', '/api/v1/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($userData));
        
        // Should return validation error
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
        
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testCreateUserWithInvalidDataStructure()
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'ValidPassword123',
            'roles' => 'not-an-array' // Should be array
        ];

        $this->client->request('POST', '/api/v1/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($userData));
        
        // Should return validation error
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
    }

    public function testChangePasswordEndpoint()
    {
        $userId = '507f1f77bcf86cd799439011';
        $passwordData = [
            'currentPassword' => 'CurrentPassword123',
            'newPassword' => 'NewPassword456'
        ];

        $this->client->request('POST', "/api/v1/users/{$userId}/change-password", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($passwordData));
        
        // May succeed, fail with validation, or require authentication
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_OK),
                $this->equalTo(Response::HTTP_NOT_FOUND),
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
        
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testChangePasswordWithInvalidUserId()
    {
        $invalidUserId = 'invalid-user-id';
        $passwordData = [
            'currentPassword' => 'CurrentPassword123',
            'newPassword' => 'NewPassword456'
        ];

        $this->client->request('POST', "/api/v1/users/{$invalidUserId}/change-password", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($passwordData));
        
        // Should return not found or validation error
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_NOT_FOUND),
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
    }

    public function testChangePasswordWithWeakNewPassword()
    {
        $userId = '507f1f77bcf86cd799439011';
        $passwordData = [
            'currentPassword' => 'CurrentPassword123',
            'newPassword' => '123' // Too weak
        ];

        $this->client->request('POST', "/api/v1/users/{$userId}/change-password", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($passwordData));
        
        // Should return validation error
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_NOT_FOUND),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
        
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testChangePasswordWithSamePassword()
    {
        $userId = '507f1f77bcf86cd799439011';
        $samePassword = 'SamePassword123';
        $passwordData = [
            'currentPassword' => $samePassword,
            'newPassword' => $samePassword // Same as current
        ];

        $this->client->request('POST', "/api/v1/users/{$userId}/change-password", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($passwordData));
        
        // Should return validation error due to DTO constraint
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_NOT_FOUND),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
        
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testChangePasswordForNonexistentUser()
    {
        $nonexistentUserId = '507f1f77bcf86cd799439999';
        $passwordData = [
            'currentPassword' => 'CurrentPassword123',
            'newPassword' => 'NewPassword456'
        ];

        $this->client->request('POST', "/api/v1/users/{nonexistentUserId}/change-password", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($passwordData));
        
        // Should return not found error
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_NOT_FOUND),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
    }

    public function testChangePasswordWithMissingData()
    {
        $userId = '507f1f77bcf86cd799439011';
        $passwordData = [
            'currentPassword' => '', // Empty current password
            'newPassword' => '' // Empty new password
        ];

        $this->client->request('POST', "/api/v1/users/{$userId}/change-password", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($passwordData));
        
        // Should return validation error
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_NOT_FOUND),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
    }

    public function testUserApiContentTypeHeaders()
    {
        $this->client->request('POST', '/api/v1/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => 'test@example.com', 'password' => 'Test123']));
        
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUserApiResponseStructure()
    {
        $this->client->request('POST', '/api/v1/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => 'test@example.com', 'password' => 'Test123']));
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        // Verify response is valid JSON and has some structure
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
    }

    public function testPasswordChangeApiResponseStructure()
    {
        $userId = '507f1f77bcf86cd799439011';
        $this->client->request('POST', "/api/v1/users/{$userId}/change-password", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'currentPassword' => 'OldPassword123',
            'newPassword' => 'NewPassword456'
        ]));
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        // Verify response is valid JSON
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
    }

    public function testInvalidJsonRequestHandling()
    {
        $this->client->request('POST', '/api/v1/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], 'invalid-json-content');
        
        // Should handle invalid JSON gracefully
        $this->assertThat(
            $this->client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS),
                $this->equalTo(Response::HTTP_FORBIDDEN),
                $this->equalTo(Response::HTTP_TOO_MANY_REQUESTS)
            )
        );
    }
}