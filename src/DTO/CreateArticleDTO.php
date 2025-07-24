<?php

namespace App\DTO;

use App\Validator\CleanText;
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
    #[CleanText]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s\-_.,!?()]+$/u',
        message: 'Title contains invalid characters'
    )]
    private string $title = '';

    #[Assert\NotBlank(message: 'Content is required')]
    #[Assert\Length(
        min: 10,
        max: 50000,
        minMessage: 'Content must be at least {{ limit }} characters long',
        maxMessage: 'Content cannot be longer than {{ limit }} characters'
    )]
    #[CleanText]
    private string $content = '';

    #[Assert\NotBlank(message: 'Author ID is required')]
    #[Assert\Regex(
        pattern: '/^[a-f0-9]{24}$/',
        message: 'Author ID must be a valid MongoDB ObjectId'
    )]
    private string $authorId = '';

    #[Assert\Type('array')]
    #[Assert\Count(
        max: 10,
        maxMessage: 'Cannot have more than {{ limit }} tags'
    )]
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Length(
            min: 2,
            max: 50,
            minMessage: 'Tag must be at least {{ limit }} characters long',
            maxMessage: 'Tag cannot be longer than {{ limit }} characters'
        ),
        new Assert\Regex(
            pattern: '/^[a-zA-Z0-9\-_]+$/',
            message: 'Tags can only contain letters, numbers, hyphens and underscores'
        )
    ])]
    private ?array $tags = null;

    /**
     * Get the article title.
     */
    public function getTitle(): string
    {
        return trim($this->title);
    }

    /**
     * Set the article title.
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
     * Get the article content.
     */
    public function getContent(): string
    {
        return trim($this->content);
    }

    /**
     * Set the article content.
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
     * Get the author ID.
     */
    public function getAuthorId(): string
    {
        return $this->authorId;
    }

    /**
     * Set the author ID.
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
     * Get the article tags.
     * 
     * @return array<string>|null Array of tag strings or null
     */
    public function getTags(): ?array
    {
        return $this->tags ? array_map('trim', $this->tags) : null;
    }

    /**
     * Set the article tags.
     * 
     * @param array<string>|null $tags Array of tag strings or null
     * @return self
     */
    public function setTags(?array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }
}