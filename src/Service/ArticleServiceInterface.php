<?php

namespace App\Service;

use App\Document\Article;

interface ArticleServiceInterface
{
    /**
     * Get all articles with pagination, filtering and sorting
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @param array $filters Optional filters
     * @param string $sortBy Field to sort by
     * @param string $sortOrder Sort direction ('asc' or 'desc')
     * @return array
     */
    public function getAllArticles(
        int $page = 1, 
        int $limit = 10, 
        array $filters = [],
        string $sortBy = 'createdAt', 
        string $sortOrder = 'desc'
    ): array;
    
    public function createArticle(array $data): array;
    public function getArticle(string $id): ?Article;
    public function updateArticle(Article $article, array $data): array;
    public function deleteArticle(Article $article): void;
}