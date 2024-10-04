<?php

namespace App\Controller;

use App\OpenApi\ArticleSchema;
use App\Service\ArticleServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
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
    )
    {
    }

    #[OA\Get(
        path: '/api/articles',
        summary: 'List all articles',
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Number of items per page',
                in: 'query',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: new Model(type: ArticleSchema::class))),
                        new OA\Property(property: 'total', type: 'integer'),
                        new OA\Property(property: 'page', type: 'integer'),
                        new OA\Property(property: 'limit', type: 'integer')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $result = $this->articleService->getAllArticles($page, $limit);

        return $this->json($result);
    }

    #[OA\Post(
        path: '/api/articles',
        summary: 'Create a new article',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: ArticleSchema::class))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Article created',
                content: new OA\JsonContent(ref: new Model(type: ArticleSchema::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input'
            )
        ]
    )]
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

    #[OA\Get(
        path: '/api/articles/{id}',
        summary: 'Get an article by ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: new Model(type: ArticleSchema::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Article not found'
            )
        ]
    )]
    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $article = $this->articleService->getArticle($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->json($article);
    }

    #[OA\Put(
        path: '/api/articles/{id}',
        summary: 'Update an existing article',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: ArticleSchema::class))
        ),
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: new Model(type: ArticleSchema::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input'
            ),
            new OA\Response(
                response: 404,
                description: 'Article not found'
            )
        ]
    )]
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

    #[OA\Delete(
        path: '/api/articles/{id}',
        summary: 'Delete an article',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Successful operation'
            ),
            new OA\Response(
                response: 404,
                description: 'Article not found'
            )
        ]
    )]
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