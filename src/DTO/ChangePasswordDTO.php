<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordDTO
{
    #[Assert\NotBlank(message: 'Current password is required')]
    private string $currentPassword = '';

    #[Assert\NotBlank(message: 'New password is required')]
    #[Assert\Length(
        min: 8,
        minMessage: 'New password must be at least {{ limit }} characters long',
    )]
    #[Assert\Regex(
        pattern: '/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])/',
        message: 'New password must include at least one uppercase letter, one lowercase letter, and one number'
    )]
    #[Assert\NotEqualTo(
        propertyPath: 'currentPassword',
        message: 'New password must be different from current password'
    )]
    private string $newPassword = '';

    /**
     * Get the current password.
     */
    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    /**
     * Set the current password.
     * 
     * @param string $currentPassword The current password for verification
     * @return self
     */
    public function setCurrentPassword(string $currentPassword): self
    {
        $this->currentPassword = $currentPassword;
        return $this;
    }

    /**
     * Get the new password.
     */
    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    /**
     * Set the new password.
     * 
     * @param string $newPassword The new password
     * @return self
     */
    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;
        return $this;
    }
}