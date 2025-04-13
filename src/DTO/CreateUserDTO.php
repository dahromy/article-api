<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserDTO
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email')]
    private string $email;

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Password must be at least {{ limit }} characters long',
    )]
    #[Assert\Regex(
        pattern: '/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])/',
        message: 'Password must include at least one uppercase letter, one lowercase letter, and one number'
    )]
    private string $password;

    #[Assert\Optional]
    #[Assert\Type('array')]
    private array $roles = [];

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }
}