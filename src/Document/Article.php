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
class Article implements \JsonSerializable
{
    #[MongoDB\Id]
    protected ?string $id;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    protected ?string $title;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    protected ?string $content;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    protected ?string $authorId;

    #[MongoDB\Field(type: "collection")]
    protected array $tags = [];

    #[MongoDB\EmbedOne(targetDocument: User::class)]
    protected ?User $author = null;

    #[MongoDB\Field(type: "date")]
    protected ?\DateTime $createdAt;

    #[MongoDB\Field(type: "date")]
    protected ?\DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters and setters

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getAuthorId(): ?string
    {
        return $this->authorId;
    }

    public function setAuthorId(string $authorId): self
    {
        $this->authorId = $authorId;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime();
        return $this;
    }

    #[MongoDB\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

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