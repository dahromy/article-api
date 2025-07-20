<?php

namespace App\Tests\Service;

use App\Document\Article;
use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleServiceTest extends KernelTestCase
{
    private ArticleService $articleService;
    private DocumentManager|MockObject $documentManager;
    private ArticleRepository|MockObject $articleRepository;
    private ValidatorInterface|MockObject $validator;

    /**
     * @param array $result
     * @param array $data
     * @return void
     */
    public function assertArticleData(array $result, array $data): void
    {
        $this->assertArrayHasKey('article', $result);
        $this->assertInstanceOf(Article::class, $result['article']);
        $this->assertEquals($data['name'], $result['article']->getName());
        $this->assertEquals($data['description'], $result['article']->getDescription());
        $this->assertEquals($data['price'], $result['article']->getPrice());
        $this->assertEquals($data['quantity'], $result['article']->getQuantity());
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->articleRepository = $this->createMock(ArticleRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->articleService = new ArticleService(
            $this->documentManager,
            $this->articleRepository,
            $this->validator
        );
    }

    public function testGetAllArticles()
    {
        $page = 1;
        $limit = 10;
        $filters = [];
        $sortBy = 'createdAt';
        $sortOrder = 'desc';
        $articles = [new Article(), new Article()];
        $total = 2;

        $expectedResult = [
            'items' => $articles,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];

        $this->articleRepository->expects($this->once())
            ->method('findPaginated')
            ->with($page, $limit, $filters, $sortBy, $sortOrder)
            ->willReturn($expectedResult);

        $result = $this->articleService->getAllArticles($page, $limit, $filters, $sortBy, $sortOrder);

        $this->assertEquals($expectedResult, $result);
    }

    public function testCreateArticle()
    {
        $data = [
            'name' => 'Test Article',
            'description' => 'Test Description',
            'price' => 10.99,
            'quantity' => 5
        ];

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->documentManager->expects($this->once())
            ->method('persist');
        $this->documentManager->expects($this->once())
            ->method('flush');

        $result = $this->articleService->createArticle($data);

        $this->assertArticleData($result, $data);
    }

    public function testGetArticle()
    {
        $articleId = '123';
        $article = new Article();
        $article->setName('Test Article');

        $this->articleRepository->expects($this->once())
            ->method('find')
            ->with($articleId)
            ->willReturn($article);

        $result = $this->articleService->getArticle($articleId);

        $this->assertInstanceOf(Article::class, $result);
        $this->assertEquals('Test Article', $result->getName());
    }

    public function testUpdateArticle()
    {
        $article = new Article();
        $data = [
            'name' => 'Updated Article',
            'description' => 'Updated Description',
            'price' => 15.99,
            'quantity' => 10
        ];

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->documentManager->expects($this->once())
            ->method('flush');

        $result = $this->articleService->updateArticle($article, $data);

        $this->assertArticleData($result, $data);
    }

    public function testDeleteArticle()
    {
        $article = new Article();

        $this->documentManager->expects($this->once())
            ->method('remove')
            ->with($article);
        $this->documentManager->expects($this->once())
            ->method('flush');

        $this->articleService->deleteArticle($article);

        // If no exception is thrown, the test passes
        $this->assertTrue(true);
    }

    public function testCreateArticleWithInvalidData()
    {
        $data = [
            'name' => '', // Invalid: empty name
            'description' => 'Test Description',
            'price' => -10.99, // Invalid: negative price
            'quantity' => -5 // Invalid: negative quantity
        ];

        $violations = $this->createMock(ConstraintViolationList::class);
        $violations->method('count')->willReturn(2);
        $violations->method('__toString')->willReturn('Validation errors occurred');

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->documentManager->expects($this->never())
            ->method('persist');
        $this->documentManager->expects($this->never())
            ->method('flush');

        $result = $this->articleService->createArticle($data);

        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Validation errors occurred', $result['errors']);
    }

    public function testUpdateArticleWithInvalidData()
    {
        $article = new Article();
        $data = [
            'name' => '', // Invalid: empty name
            'description' => 'Updated Description',
            'price' => -15.99, // Invalid: negative price
            'quantity' => -10 // Invalid: negative quantity
        ];

        $violations = $this->createMock(ConstraintViolationList::class);
        $violations->method('count')->willReturn(2);
        $violations->method('__toString')->willReturn('Validation errors occurred');

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->documentManager->expects($this->never())
            ->method('flush');

        $result = $this->articleService->updateArticle($article, $data);

        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Validation errors occurred', $result['errors']);
    }

    public function testGetArticleNotFound()
    {
        $articleId = 'nonexistent-id';

        $this->articleRepository->expects($this->once())
            ->method('find')
            ->with($articleId)
            ->willReturn(null);

        $result = $this->articleService->getArticle($articleId);

        $this->assertNull($result);
    }
}