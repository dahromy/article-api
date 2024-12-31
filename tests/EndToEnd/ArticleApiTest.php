<?php

namespace App\Tests\EndToEnd;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleApiTest extends WebTestCase
{
    public function testGetAllArticles()
    {
        $client = static::createClient();
        $client->request('GET', '/api/articles');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
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

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetArticle()
    {
        $client = static::createClient();
        $client->request('GET', '/api/articles/1');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
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

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testDeleteArticle()
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/articles/1');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
