<?php

namespace App\Tests\EndToEnd;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleApiTest extends WebTestCase
{
    public function testGetAllArticles()
    {
        $client = static::createClient();
        $client->request('GET', '/api/articles');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testCreateArticle()
    {
        $client = static::createClient();
        $client->request('POST', '/api/articles', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Test Article',
            'description' => 'Test Description',
            'price' => 10.99,
            'quantity' => 5
        ]));

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetArticle()
    {
        $client = static::createClient();
        $client->request('GET', '/api/articles/1');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testUpdateArticle()
    {
        $client = static::createClient();
        $client->request('PUT', '/api/articles/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Updated Article',
            'description' => 'Updated Description',
            'price' => 15.99,
            'quantity' => 10
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testDeleteArticle()
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/articles/1');

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }
}
