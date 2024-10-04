<?php

namespace App\Controller;

use App\Service\ArticleServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/articles')]
class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleServiceInterface $articleService
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $result = $this->articleService->getAllArticles($page, $limit);

        return $this->json($result);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $result = $this->articleService->createArticle($data);

        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result['article'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $article = $this->articleService->getArticle($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->json($article);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $article = $this->articleService->getArticle($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $data = json_decode($request->getContent(), true);

        $result = $this->articleService->updateArticle($article, $data);

        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result['article']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $article = $this->articleService->getArticle($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $this->articleService->deleteArticle($article);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}