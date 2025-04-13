<?php

namespace App\Document;

use App\Repository\ArticleRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: "articles", repositoryClass: ArticleRepository::class)]
#[MongoDB\Index(keys: ['name' => 'asc'])]
#[MongoDB\Index(keys: ['price' => 1])]
#[MongoDB\Index(keys: ['author.id' => 1])]
#[MongoDB\Index(keys: ['createdAt' => -1])]
class Article implements \JsonSerializable
{
    #[MongoDB\Id]
    protected ?string $id;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    protected ?string $name;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    protected ?string $description;

    #[MongoDB\Field(type: "float")]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    protected ?float $price;

    #[MongoDB\Field(type: "int")]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    protected ?int $quantity;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
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
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'tags' => $this->tags,
            'author' => $this->author,
            'createdAt' => $this->createdAt?->format('c'),
            'updatedAt' => $this->updatedAt?->format('c'),
        ];
    }
}