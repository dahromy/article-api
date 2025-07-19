<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    public function testIndexRoute()
    {
        $client = static::createClient();
        
        // Test the articles index endpoint
        $client->request('GET', '/api/v1/articles');
        
        // API requires authentication, expect 403
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('AUTHENTICATION_FAILED', $responseData['message']);
    }

    public function testIndexWithPaginationParameters()
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/v1/articles', [
            'page' => 1,
            'limit' => 5,
            'sortBy' => 'createdAt',
            'sortOrder' => 'desc'
        ]);
        
        // API requires authentication, expect 403
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testShowRouteWithValidId()
    {
        $client = static::createClient();
        
        // Use a valid ObjectId format
        $validId = '507f1f77bcf86cd799439011';
        $client->request('GET', "/api/v1/articles/{$validId}");
        
        // API requires authentication, expect 403
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testShowRouteWithInvalidId()
    {
        $client = static::createClient();
        
        $invalidId = 'invalid-id';
        $client->request('GET', "/api/v1/articles/{$invalidId}");
        
        // API requires authentication, expect 403
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateWithoutAuthentication()
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/v1/articles', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Test Article',
            'content' => 'This is test content for the article',
            'authorId' => '507f1f77bcf86cd799439011',
            'tags' => ['test', 'php']
        ]));
        
        // Should require authentication - 403 Forbidden
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateWithInvalidData()
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/v1/articles', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => '', // Invalid: empty title
            'content' => 'short', // Invalid: too short
            'authorId' => '', // Invalid: empty author ID
        ]));
        
        // Should return authentication error first
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateWithoutAuthentication()
    {
        $client = static::createClient();
        
        $validId = '507f1f77bcf86cd799439011';
        $client->request('PUT', "/api/v1/articles/{$validId}", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Updated Article',
            'content' => 'Updated content for the article',
            'tags' => ['updated', 'test']
        ]));
        
        // Should require authentication
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteWithoutAuthentication()
    {
        $client = static::createClient();
        
        $validId = '507f1f77bcf86cd799439011';
        $client->request('DELETE', "/api/v1/articles/{$validId}");
        
        // Should require authentication
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteNonexistentArticle()
    {
        $client = static::createClient();
        
        $nonexistentId = '507f1f77bcf86cd799439999';
        $client->request('DELETE', "/api/v1/articles/{$nonexistentId}");
        
        // Should return 403 (authentication required)
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}