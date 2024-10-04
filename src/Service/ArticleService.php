<?php

namespace App\Service;

use App\Document\Article;
use App\Repository\ArticleRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleService implements ArticleServiceInterface
{
    public function __construct(
        private readonly DocumentManager $dm,
        private readonly ArticleRepository $articleRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function getAllArticles(int $page, int $limit): array
    {
        $articles = $this->articleRepository->findAllOrderedByName($page, $limit);
        $total = $this->articleRepository->countAll();

        return [
            'data' => $articles,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    public function createArticle(array $data): array
    {
        $article = new Article();
        $article->setName($data['name']);
        $article->setDescription($data['description']);
        $article->setPrice($data['price']);
        $article->setQuantity($data['quantity']);

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
        $article->setName($data['name']);
        $article->setDescription($data['description']);
        $article->setPrice($data['price']);
        $article->setQuantity($data['quantity']);

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