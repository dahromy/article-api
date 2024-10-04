<?php

namespace App\Controller;

use App\Document\Article;
use App\Repository\ArticleRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/articles')]
class ArticleController extends AbstractController
{

    public function __construct(
        private readonly DocumentManager $dm,
        private readonly ArticleRepository $articleRepository,
        private readonly ValidatorInterface $validator
    )
    {
    }

    /**
     * @throws MongoDBException
     */
    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $articles = $this->articleRepository->findAllOrderedByName();
        return $this->json($articles);
    }

    /**
     * @throws \Throwable
     * @throws MongoDBException
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new Article();
        $article->setName($data['name']);
        $article->setDescription($data['description']);
        $article->setPrice($data['price']);
        $article->setQuantity($data['quantity']);

        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->dm->persist($article);
        $this->dm->flush();

        return $this->json($article, Response::HTTP_CREATED);
    }

    /**
     * @throws MappingException
     * @throws LockException
     */
    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->json($article);
    }

    /**
     * @throws MappingException
     * @throws \Throwable
     * @throws MongoDBException
     * @throws LockException
     */
    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $data = json_decode($request->getContent(), true);

        $article->setName($data['name']);
        $article->setDescription($data['description']);
        $article->setPrice($data['price']);
        $article->setQuantity($data['quantity']);

        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->dm->flush();

        return $this->json($article);
    }

    /**
     * @throws MappingException
     * @throws \Throwable
     * @throws MongoDBException
     * @throws LockException
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $this->dm->remove($article);
        $this->dm->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}