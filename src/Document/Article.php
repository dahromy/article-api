<?php

namespace App\Document;

use App\Repository\ArticleRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: "articles", repositoryClass: ArticleRepository::class)]
class Article
{
    #[MongoDB\Id]
    protected ?string $id;

    #[MongoDB\Field(type: "string")]
    protected ?string $name;

    #[MongoDB\Field(type: "string")]
    protected ?string $description;

    #[MongoDB\Field(type: "float")]
    protected ?float $price;

    #[MongoDB\Field(type: "int")]
    protected ?int $quantity;

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
}