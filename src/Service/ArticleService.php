<?php

namespace App\Service;

use App\Document\Article;
use App\Repository\ArticleRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ArticleService implements ArticleServiceInterface
{
    public function __construct(
        private DocumentManager    $dm,
        private ArticleRepository  $articleRepository,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * @throws MongoDBException
     */
    public function getAllArticles(
        int $page = 1, 
        int $limit = 10, 
        array $filters = [],
        string $sortBy = 'createdAt', 
        string $sortOrder = 'desc'
    ): array {
        return $this->articleRepository->findPaginated(
            $page, 
            $limit, 
            $filters, 
            $sortBy, 
            $sortOrder
        );
    }

    public function createArticle(array $data): array
    {
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setAuthorId($data['authorId']);
        
        if (isset($data['tags'])) {
            $article->setTags($data['tags']);
        }

        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            return ['errors' => (string) $errors];
        }

        $this->dm->persist($article);
        $this->dm->flush();

        return ['article' => $article];
    }

    public function getArticle(string $id): ?Article
    {
        return $this->articleRepository->find($id);
    }

    public function updateArticle(Article $article, array $data): array
    {
        if (isset($data['title'])) {
            $article->setTitle($data['title']);
        }
        
        if (isset($data['content'])) {
            $article->setContent($data['content']);
        }
        
        if (isset($data['tags'])) {
            $article->setTags($data['tags']);
        }

        $article->setUpdatedAt();

        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            return ['errors' => (string) $errors];
        }

        $this->dm->flush();

        return ['article' => $article];
    }

    public function deleteArticle(Article $article): void
    {
        $this->dm->remove($article);
        $this->dm->flush();
    }
}