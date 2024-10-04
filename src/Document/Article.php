<?php

namespace App\Document;

use App\Repository\ArticleRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: "articles", repositoryClass: ArticleRepository::class)]
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
        ];
    }
}