<?php

namespace App\Service;

use App\Document\Article;

interface ArticleServiceInterface
{
    /**
     * Get all articles with pagination, filtering and sorting.
     * 
     * @param int $page Page number (1-based)
     * @param int $limit Items per page
     * @param array<string, mixed> $filters Optional filters
     * @param string $sortBy Field to sort by
     * @param string $sortOrder Sort direction ('asc' or 'desc')
     * @return array<string, mixed> Paginated articles data
     */
    public function getAllArticles(
        int $page = 1, 
        int $limit = 10, 
        array $filters = [],
        string $sortBy = 'createdAt', 
        string $sortOrder = 'desc'
    ): array;
    
    /**
     * Create a new article from provided data.
     * 
     * @param array<string, mixed> $data Article data
     * @return array<string, mixed> Response with article or errors
     */
    public function createArticle(array $data): array;
    
    /**
     * Get a single article by ID.
     * 
     * @param string $id The article ID
     * @return Article|null The article if found
     */
    public function getArticle(string $id): ?Article;
    
    /**
     * Update an existing article.
     * 
     * @param Article $article The article to update
     * @param array<string, mixed> $data New article data
     * @return array<string, mixed> Response with updated article or errors
     */
    public function updateArticle(Article $article, array $data): array;
    
    /**
     * Delete an article.
     * 
     * @param Article $article The article to delete
     */
    public function deleteArticle(Article $article): void;
}