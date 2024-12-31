<?php

namespace App\Tests\Controller;

use App\Document\Article;
use App\Service\ArticleServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    private $client;
    private $articleService;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->articleService = $this->createMock(ArticleServiceInterface::class);
        static::getContainer()->set(ArticleServiceInterface::class, $this->articleService);
    }

    public function testIndex()
    {
        $this->articleService->method('getAllArticles')->willReturn([
            'data' => [],
            'total' => 0,
            'page' => 1,
            'limit' => 10
        ]);

        $this->client->request('GET', '/api/articles');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testCreate()
    {
        $data = [
            'name' => 'Test Article',
            'description' => 'Test Description',
            'price' => 10.99,
            'quantity' => 5
        ];

        $this->articleService->method('createArticle')->willReturn(['article' => new Article()]);

        $this->client->request('POST', '/api/articles', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testShow()
    {
        $article = new Article();
        $article->setName('Test Article');

        $this->articleService->method('getArticle')->willReturn($article);

        $this->client->request('GET', '/api/articles/123');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testUpdate()
    {
        $data = [
            'name' => 'Updated Article',
            'description' => 'Updated Description',
            'price' => 15.99,
            'quantity' => 10
        ];

        $article = new Article();
        $article->setName('Test Article');

        $this->articleService->method('getArticle')->willReturn($article);
        $this->articleService->method('updateArticle')->willReturn(['article' => $article]);

        $this->client->request('PUT', '/api/articles/123', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testDelete()
    {
        $article = new Article();
        $article->setName('Test Article');

        $this->articleService->method('getArticle')->willReturn($article);

        $this->client->request('DELETE', '/api/articles/123');

        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }
}
