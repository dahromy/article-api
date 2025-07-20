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
    private string $title = '';

    #[Assert\NotBlank(message: 'Content is required')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Content must be at least {{ limit }} characters long',
    )]
    private string $content = '';

    #[Assert\NotBlank(message: 'Author ID is required')]
    private string $authorId = '';

    #[Assert\Type('array')]
    private ?array $tags = null;

    /**
     * Get the article title.
     */
    public function getTitle(): string
    {
        return $this->title;
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
        return $this->content;
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
        return $this->tags;
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