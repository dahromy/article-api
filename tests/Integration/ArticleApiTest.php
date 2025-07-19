<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleApiTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetAllArticlesEndpoint()
    {
        $this->client->request('GET', '/api/v1/articles');
        
        // API requires authentication
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('AUTHENTICATION_FAILED', $responseData['message']);
    }

    public function testGetAllArticlesWithFilters()
    {
        $this->client->request('GET', '/api/v1/articles', [
            'page' => 1,
            'limit' => 5,
            'authorId' => '507f1f77bcf86cd799439011',
            'title' => 'test',
            'tags' => 'php,symfony',
            'sortBy' => 'createdAt',
            'sortOrder' => 'desc'
        ]);
        
        // API requires authentication
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testCreateArticleEndpoint()
    {
        $articleData = [
            'title' => 'Test Article for API',
            'content' => 'This is test content for the article API test',
            'authorId' => '507f1f77bcf86cd799439011',
            'tags' => ['test', 'api', 'php']
        ];

        $this->client->request('POST', '/api/v1/articles', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($articleData));
        
        // Should require authentication
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testCreateArticleWithInvalidData()
    {
        $invalidData = [
            'title' => '', // Invalid: empty title
            'content' => 'short', // Invalid: too short
            'authorId' => '', // Invalid: empty author ID
            'tags' => 'not-an-array' // Invalid: should be array
        ];

        $this->client->request('POST', '/api/v1/articles', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($invalidData));
        
        // Authentication error comes first
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetArticleEndpoint()
    {
        $articleId = '507f1f77bcf86cd799439011';
        
        $this->client->request('GET', "/api/v1/articles/{$articleId}");
        
        // API requires authentication
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testGetNonexistentArticle()
    {
        $nonexistentId = '507f1f77bcf86cd799439999';
        
        $this->client->request('GET', "/api/v1/articles/{$nonexistentId}");
        
        // API requires authentication first
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetArticleWithInvalidId()
    {
        $invalidId = 'invalid-object-id';
        
        $this->client->request('GET', "/api/v1/articles/{$invalidId}");
        
        // API requires authentication first
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateArticleEndpoint()
    {
        $articleId = '507f1f77bcf86cd799439011';
        $updateData = [
            'title' => 'Updated Article Title',
            'content' => 'This is updated content for the article',
            'tags' => ['updated', 'test', 'api']
        ];

        $this->client->request('PUT', "/api/v1/articles/{$articleId}", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($updateData));
        
        // Should require authentication
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testUpdateNonexistentArticle()
    {
        $nonexistentId = '507f1f77bcf86cd799439999';
        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'tags' => ['updated']
        ];

        $this->client->request('PUT', "/api/v1/articles/{$nonexistentId}", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($updateData));
        
        // Authentication error comes first
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateArticleWithInvalidData()
    {
        $articleId = '507f1f77bcf86cd799439011';
        $invalidData = [
            'title' => '', // Invalid: empty title
            'content' => 'short', // Invalid: too short
            'tags' => 'not-an-array' // Invalid: should be array
        ];

        $this->client->request('PUT', "/api/v1/articles/{$articleId}", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($invalidData));
        
        // Authentication error comes first
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteArticleEndpoint()
    {
        $articleId = '507f1f77bcf86cd799439011';
        
        $this->client->request('DELETE', "/api/v1/articles/{$articleId}");
        
        // Should require authentication
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testDeleteNonexistentArticle()
    {
        $nonexistentId = '507f1f77bcf86cd799439999';
        
        $this->client->request('DELETE', "/api/v1/articles/{$nonexistentId}");
        
        // Authentication error comes first
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteArticleWithInvalidId()
    {
        $invalidId = 'invalid-object-id';
        
        $this->client->request('DELETE', "/api/v1/articles/{$invalidId}");
        
        // Authentication error comes first  
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testApiContentTypeHeaders()
    {
        $this->client->request('GET', '/api/v1/articles');
        
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testApiErrorResponseStructure()
    {
        $this->client->request('GET', '/api/v1/articles');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        // Verify error response structure
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertIsInt($responseData['status']);
        $this->assertIsString($responseData['message']);
    }

    public function testApiErrorResponseStatusConsistency()
    {
        $this->client->request('GET', '/api/v1/articles');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        // Status in response body should match HTTP status code
        $this->assertEquals(
            $this->client->getResponse()->getStatusCode(),
            $responseData['status']
        );
    }
}