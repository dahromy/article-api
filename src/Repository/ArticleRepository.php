<?php

namespace App\Repository;

use App\Document\Article;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use MongoDB\BSON\Regex;
use MongoDB\DeleteResult;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;

/**
 * @template-extends ServiceDocumentRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @throws MongoDBException
     */
    public function findAllOrderedByName(): array|Iterator|int|DeleteResult|UpdateResult|InsertOneResult|null
    {
        return $this->createQueryBuilder()
            ->sort('name', 'ASC')
            ->getQuery()
            ->execute();
    }

    /**
     * @throws MongoDBException
     */
    public function findByNameLike(string $name): array|Iterator|int|DeleteResult|UpdateResult|InsertOneResult|null
    {
        return $this->createQueryBuilder()
            ->field('name')->equals(new Regex($name, 'i'))
            ->getQuery()
            ->execute();
    }
}