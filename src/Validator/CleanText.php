<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CleanText extends Constraint
{
    public string $message = 'Text contains potentially harmful content.';
    
    public function __construct(
        array $groups = null,
        mixed $payload = null,
        string $message = null
    ) {
        parent::__construct([], $groups, $payload);
        
        $this->message = $message ?? $this->message;
    }
}