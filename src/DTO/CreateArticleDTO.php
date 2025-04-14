<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateArticleDTO
{
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Title must be at least {{ limit }} characters long',
        maxMessage: 'Title cannot be longer than {{ limit }} characters'
    )]
    private string $title;

    #[Assert\NotBlank(message: 'Content is required')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Content must be at least {{ limit }} characters long',
    )]
    private string $content;

    #[Assert\NotBlank(message: 'Author ID is required')]
    private string $authorId;

    #[Assert\Type('array')]
    private ?array $tags = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getAuthorId(): string
    {
        return $this->authorId;
    }

    public function setAuthorId(string $authorId): self
    {
        $this->authorId = $authorId;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }
}