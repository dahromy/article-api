<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserDTO
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Email cannot be longer than {{ limit }} characters'
    )]
    private string $email = '';

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(
        min: 8,
        max: 128,
        minMessage: 'Password must be at least {{ limit }} characters long',
        maxMessage: 'Password cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        message: 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)'
    )]
    #[Assert\NotCompromisedPassword(message: 'This password is too common and has been compromised')]
    private string $password = '';

    #[Assert\Type('array')]
    #[Assert\Count(
        max: 5,
        maxMessage: 'Cannot assign more than {{ limit }} roles'
    )]
    #[Assert\All([
        new Assert\Choice(
            choices: ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_EDITOR', 'ROLE_MODERATOR'],
            message: 'Invalid role "{{ value }}". Allowed roles are: ROLE_USER, ROLE_ADMIN, ROLE_EDITOR, ROLE_MODERATOR'
        )
    ])]
    private array $roles = ['ROLE_USER'];

    /**
     * Get the user's email address.
     */
    public function getEmail(): string
    {
        return strtolower(trim($this->email));
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
        // Ensure ROLE_USER is always present
        $roles = array_unique($this->roles);
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        return $roles;
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