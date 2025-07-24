<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[MongoDB\Document(collection: "users")]
#[MongoDB\Index(keys: ['email' => 1], options: ["unique" => true])]
#[MongoDB\Index(keys: ['roles' => 1])]
#[MongoDB\Index(keys: ['createdAt' => -1])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $email = null;

    #[MongoDB\Field(type: 'string')]
    private string $password = '';

    #[MongoDB\Field(type: 'collection')]
    private array $roles = [];

    #[MongoDB\Field(type: "date")]
    private ?\DateTime $createdAt = null;

    #[MongoDB\Field(type: "date")]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Get the unique identifier of the user.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the email address of the user.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the email address of the user.
     * 
     * @param string $email The user's email address
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the username (alias for email).
     * 
     * @deprecated Use getUserIdentifier() instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * Get the user's roles.
     * 
     * @return array<string> Array of role strings
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * Set the user's roles.
     * 
     * @param array<string> $roles Array of role strings
     * @return self
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Get the hashed password.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the hashed password.
     * 
     * @param string $password The hashed password
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get the salt used to hash the password.
     * 
     * @return null Always returns null as modern password hashers don't use separate salt
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Erase credentials (clear sensitive data).
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * Get the user identifier (email).
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
}