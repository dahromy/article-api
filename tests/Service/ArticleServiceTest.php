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
        $this->assertEquals($data['title'], $result['article']->getTitle());
        $this->assertEquals($data['content'], $result['article']->getContent());
        $this->assertEquals($data['authorId'], $result['article']->getAuthorId());
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
            'title' => 'Test Article',
            'content' => 'Test content for the article',
            'authorId' => '507f1f77bcf86cd799439011'
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
        $article->setTitle('Test Article');

        $this->articleRepository->expects($this->once())
            ->method('find')
            ->with($articleId)
            ->willReturn($article);

        $result = $this->articleService->getArticle($articleId);

        $this->assertInstanceOf(Article::class, $result);
        $this->assertEquals('Test Article', $result->getTitle());
    }

    public function testUpdateArticle()
    {
        $article = new Article();
        $data = [
            'title' => 'Updated Article',
            'content' => 'Updated content for the article'
        ];

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->documentManager->expects($this->once())
            ->method('flush');

        $result = $this->articleService->updateArticle($article, $data);

        $this->assertArrayHasKey('article', $result);
        $this->assertInstanceOf(Article::class, $result['article']);
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
            'title' => '', // Invalid: empty title
            'content' => 'Short', // Invalid: too short content
            'authorId' => '' // Invalid: empty author ID
        ];

        $violations = $this->createMock(ConstraintViolationList::class);
        $violations->method('count')->willReturn(3);
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
            'title' => '', // Invalid: empty title
            'content' => 'Short' // Invalid: too short content
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