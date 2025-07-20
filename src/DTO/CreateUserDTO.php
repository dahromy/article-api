<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserDTO
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email')]
    private string $email = '';

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Password must be at least {{ limit }} characters long',
    )]
    #[Assert\Regex(
        pattern: '/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])/',
        message: 'Password must include at least one uppercase letter, one lowercase letter, and one number'
    )]
    private string $password = '';

    #[Assert\Type('array')]
    private array $roles = [];

    /**
     * Get the user's email address.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the user's email address.
     * 
     * @param string $email The email address
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the user's password.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the user's password.
     * 
     * @param string $password The plain text password
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get the user's roles.
     * 
     * @return array<string> Array of role strings
     */
    public function getRoles(): array
    {
        return $this->roles;
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
}