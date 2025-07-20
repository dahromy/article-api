<?php

namespace App\Controller;

use App\DTO\CreateArticleDTO;
use App\DTO\UpdateArticleDTO;
use App\OpenApi\ArticleSchema;
use App\Security\Voter\ArticleVoter;
use App\Service\ArticleServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/articles')]
class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleServiceInterface $articleService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    )
    {
    }

    #[OA\Get(
        path: '/api/v1/articles',
        summary: 'List all articles',
        parameters: [
            new OA\Parameter(name: 'page', description: 'Page number', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', description: 'Number of items per page', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'authorId', description: 'Filter by author ID', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'title', description: 'Filter by title (partial match)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'content', description: 'Filter by content (partial match)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'tags', description: 'Filter by tags (comma-separated)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'dateFrom', description: 'Filter articles created after this date (YYYY-MM-DD)', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'dateTo', description: 'Filter articles created before this date (YYYY-MM-DD)', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'sortBy', description: 'Sort field (title, createdAt, updatedAt)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sortOrder', description: 'Sort direction (asc, desc)', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: new Model(type: ArticleSchema::class))),
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
    #[Cache(public: true)]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $sortBy = $request->query->get('sortBy', 'createdAt');
        $sortOrder = $request->query->get('sortOrder', 'desc');
        
        // Build filters from query parameters
        $filters = [];
        
        if ($request->query->has('authorId')) {
            $filters['authorId'] = $request->query->get('authorId');
        }
        
        if ($request->query->has('title')) {
            $filters['title'] = $request->query->get('title');
        }
        
        if ($request->query->has('content')) {
            $filters['content'] = $request->query->get('content');
        }
        
        if ($request->query->has('tags')) {
            $tags = $request->query->get('tags');
            $filters['tags'] = explode(',', $tags);
        }
        
        if ($request->query->has('dateFrom')) {
            $filters['dateFrom'] = $request->query->get('dateFrom');
        }
        
        if ($request->query->has('dateTo')) {
            $filters['dateTo'] = $request->query->get('dateTo');
        }
        
        $result = $this->articleService->getAllArticles($page, $limit, $filters, $sortBy, $sortOrder);
        
        // Generate ETag based on result data
        $etag = hash('sha256', json_encode($result));
        
        // Check If-None-Match header
        if ($request->headers->has('If-None-Match') && $request->headers->get('If-None-Match') === $etag) {
            return new JsonResponse(null, Response::HTTP_NOT_MODIFIED);
        }
        
        $response = $this->json($result);
        $response->setEtag($etag);
        
        return $response;
    }

    #[OA\Post(
        path: '/api/v1/articles',
        summary: 'Create a new article',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateArticleDTO::class))
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
        $createArticleDTO = $this->serializer->deserialize(
            $request->getContent(),
            CreateArticleDTO::class,
            'json'
        );
        
        $violations = $this->validator->validate($createArticleDTO);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
        
        $result = $this->articleService->createArticle([
            'title' => $createArticleDTO->getTitle(),
            'content' => $createArticleDTO->getContent(),
            'authorId' => $createArticleDTO->getAuthorId(),
            'tags' => $createArticleDTO->getTags(),
        ]);

        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result['article'], Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/v1/articles/{id}',
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
        path: '/api/v1/articles/{id}',
        summary: 'Update an existing article',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: UpdateArticleDTO::class))
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
            ),
            new OA\Response(
                response: 403,
                description: 'Access denied'
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
        
        $this->denyAccessUnlessGranted(ArticleVoter::EDIT, $article, 'You can only edit your own articles.');
        
        $updateArticleDTO = $this->serializer->deserialize(
            $request->getContent(),
            UpdateArticleDTO::class,
            'json'
        );
        
        $violations = $this->validator->validate($updateArticleDTO);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $data = array_filter([
            'title' => $updateArticleDTO->getTitle(),
            'content' => $updateArticleDTO->getContent(),
            'tags' => $updateArticleDTO->getTags(),
        ], fn($value) => $value !== null);

        $result = $this->articleService->updateArticle($article, $data);

        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result['article']);
    }

    #[OA\Delete(
        path: '/api/v1/articles/{id}',
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
            ),
            new OA\Response(
                response: 403,
                description: 'Access denied'
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
        
        $this->denyAccessUnlessGranted(ArticleVoter::DELETE, $article, 'You can only delete your own articles.');

        $this->articleService->deleteArticle($article);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}