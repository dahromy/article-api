<?php

namespace App\Repository;

use App\Document\Article;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Doctrine\ODM\MongoDB\MongoDBException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;

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
    // Define allowed sort fields to prevent injection attacks
    private const ALLOWED_SORT_FIELDS = ['title', 'createdAt', 'updatedAt'];
    private const DEFAULT_SORT_FIELD = 'createdAt';
    private const DEFAULT_SORT_ORDER = 'desc';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Find paginated, filtered, and sorted articles
     *
     * @param int $page Page number (1-based)
     * @param int $limit Items per page
     * @param array $filters Optional filters (authorId, title, tags)
     * @param string $sortBy Field to sort by
     * @param string $sortOrder Sort direction ('asc' or 'desc')
     * @return array{items: Article[], total: int, page: int, limit: int}
     * @throws MongoDBException
     */
    public function findPaginated(
        int $page = 1,
        int $limit = 10,
        array $filters = [],
        string $sortBy = self::DEFAULT_SORT_FIELD,
        string $sortOrder = self::DEFAULT_SORT_ORDER
    ): array {
        // Ensure page is at least 1
        $page = max(1, $page);
        
        // Validate and sanitize sort parameters
        if (!in_array($sortBy, self::ALLOWED_SORT_FIELDS)) {
            $sortBy = self::DEFAULT_SORT_FIELD;
        }
        
        $sortDirection = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
        
        $qb = $this->createQueryBuilder();
        
        // Apply filters
        $this->applyFilters($qb, $filters);
        
        // Get total count before pagination
        $totalQb = clone $qb;
        $total = $totalQb->count();
        
        // Apply sorting and pagination
        $items = $qb
            ->sort($sortBy, $sortDirection)
            ->skip(($page - 1) * $limit)
            ->limit($limit)
            ->getQuery()
            ->execute();
            
        // Convert cursor to array
        $itemsArray = [];
        foreach ($items as $item) {
            $itemsArray[] = $item;
        }
        
        return [
            'items' => $itemsArray,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Apply filters to the query builder
     */
    private function applyFilters($qb, array $filters): void
    {
        // Filter by author ID
        if (!empty($filters['authorId'])) {
            try {
                $authorId = new ObjectId($filters['authorId']);
                $qb->field('author.id')->equals($authorId);
            } catch (\Exception $e) {
                // Invalid ObjectId, will return no results
                $qb->field('author.id')->equals(new ObjectId('000000000000000000000000'));
            }
        }
        
        // Filter by title (partial match)
        if (!empty($filters['title'])) {
            $qb->field('title')->equals(new Regex($filters['title'], 'i'));
        }
        
        // Filter by tags (exact match of any tag in the array)
        if (!empty($filters['tags'])) {
            $tags = is_array($filters['tags']) ? $filters['tags'] : [$filters['tags']];
            $qb->field('tags')->in($tags);
        }
        
        // Filter by content (partial match)
        if (!empty($filters['content'])) {
            $qb->field('content')->equals(new Regex($filters['content'], 'i'));
        }
        
        // Filter by date range
        if (!empty($filters['dateFrom'])) {
            try {
                $dateFrom = new \DateTime($filters['dateFrom']);
                $qb->field('createdAt')->gte($dateFrom);
            } catch (\Exception $e) {
                // Invalid date format, ignore this filter
            }
        }
        
        if (!empty($filters['dateTo'])) {
            try {
                $dateTo = new \DateTime($filters['dateTo']);
                $qb->field('createdAt')->lte($dateTo);
            } catch (\Exception $e) {
                // Invalid date format, ignore this filter
            }
        }
    }

    /**
     * @throws MongoDBException
     */
    public function findAllOrderedByTitle(int $page = 1, int $limit = 10): array
    {
        $result = $this->createQueryBuilder()
            ->sort('title', 'ASC')
            ->skip(($page - 1) * $limit)
            ->limit($limit)
            ->getQuery()
            ->execute();
            
        $items = [];
        foreach ($result as $item) {
            $items[] = $item;
        }
        
        return $items;
    }

    /**
     * @throws MongoDBException
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder()
            ->count()
            ->getQuery()
            ->execute();
    }

    /**
     * @throws MongoDBException
     */
    public function findByTitleLike(string $title): array
    {
        $result = $this->createQueryBuilder()
            ->field('title')->equals(new Regex($title, 'i'))
            ->getQuery()
            ->execute();
            
        $items = [];
        foreach ($result as $item) {
            $items[] = $item;
        }
        
        return $items;
    }
}