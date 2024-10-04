<?php

namespace App\Service;

use App\Document\Article;

interface ArticleServiceInterface
{
    public function getAllArticles(int $page, int $limit): array;
    public function createArticle(array $data): array;
    public function getArticle(string $id): ?Article;
    public function updateArticle(Article $article, array $data): array;
    public function deleteArticle(Article $article): void;
}