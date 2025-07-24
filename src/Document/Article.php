<?php

namespace App\Document;

use App\Repository\ArticleRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: "articles", repositoryClass: ArticleRepository::class)]
#[MongoDB\Index(keys: ['title' => 'asc'])]
#[MongoDB\Index(keys: ['authorId' => 1])]
#[MongoDB\Index(keys: ['author.id' => 1])]
#[MongoDB\Index(keys: ['createdAt' => -1])]
#[MongoDB\Index(keys: ['updatedAt' => -1])]
#[MongoDB\Index(keys: ['tags' => 1])]
#[MongoDB\Index(keys: ['title' => 'text', 'content' => 'text'])]
#[MongoDB\Index(keys: ['authorId' => 1, 'createdAt' => -1])]
class Article implements \JsonSerializable
{
    #[MongoDB\Id]
    protected ?string $id = null;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    protected ?string $title = null;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    protected ?string $content = null;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    protected ?string $authorId = null;

    #[MongoDB\Field(type: "collection")]
    protected array $tags = [];

    #[MongoDB\EmbedOne(targetDocument: User::class)]
    protected ?User $author = null;

    #[MongoDB\Field(type: "date")]
    protected ?\DateTime $createdAt = null;

    #[MongoDB\Field(type: "date")]
    protected ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters and setters

    /**
     * Get the unique identifier of the article.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the title of the article.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title of the article.
     * 
     * @param string $title The article title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the content of the article.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the content of the article.
     * 
     * @param string $content The article content
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get the author ID of the article.
     */
    public function getAuthorId(): ?string
    {
        return $this->authorId;
    }

    /**
     * Set the author ID of the article.
     * 
     * @param string $authorId The author's unique identifier
     * @return self
     */
    public function setAuthorId(string $authorId): self
    {
        $this->authorId = $authorId;
        return $this;
    }

    /**
     * Get the tags associated with the article.
     * 
     * @return array<string> Array of tag strings
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Set the tags for the article.
     * 
     * @param array<string> $tags Array of tag strings
     * @return self
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * Get the embedded author object.
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Set the embedded author object.
     * 
     * @param User|null $author The author object or null
     * @return self
     */
    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Get the creation date of the article.
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Get the last updated date of the article.
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Set the updated date to current time.
     * 
     * @return self
     */
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime();
        return $this;
    }

    /**
     * Doctrine lifecycle callback that sets updatedAt before update operations.
     */
    #[MongoDB\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Serialize the article object to JSON format.
     * 
     * @return array<string, mixed> Array representation for JSON serialization
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'authorId' => $this->authorId,
            'tags' => $this->tags,
            'author' => $this->author,
            'createdAt' => $this->createdAt?->format('c'),
            'updatedAt' => $this->updatedAt?->format('c'),
        ];
    }
}