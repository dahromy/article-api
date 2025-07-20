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
     * Get all articles with pagination, filtering, and sorting.
     * 
     * @param int $page The page number (1-based)
     * @param int $limit The number of articles per page
     * @param array<string, mixed> $filters Filter criteria
     * @param string $sortBy The field to sort by
     * @param string $sortOrder The sort order ('asc' or 'desc')
     * @return array<string, mixed> Paginated articles data
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

    /**
     * Create a new article from provided data.
     * 
     * @param array<string, mixed> $data Article data containing title, content, authorId, and optionally tags
     * @return array<string, mixed> Response with either article data or validation errors
     */
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

    /**
     * Get a single article by ID.
     * 
     * @param string $id The article ID
     * @return Article|null The article if found, null otherwise
     */
    public function getArticle(string $id): ?Article
    {
        return $this->articleRepository->find($id);
    }

    /**
     * Update an existing article with new data.
     * 
     * @param Article $article The article to update
     * @param array<string, mixed> $data New data for the article
     * @return array<string, mixed> Response with either updated article data or validation errors
     */
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

    /**
     * Delete an article.
     * 
     * @param Article $article The article to delete
     */
    public function deleteArticle(Article $article): void
    {
        $this->dm->remove($article);
        $this->dm->flush();
    }
}