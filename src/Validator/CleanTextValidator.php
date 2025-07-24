<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CleanTextValidator extends ConstraintValidator
{
    private const SUSPICIOUS_PATTERNS = [
        '/(<script[^>]*>|<\/script>)/i',
        '/(<iframe[^>]*>|<\/iframe>)/i',
        '/(<object[^>]*>|<\/object>)/i',
        '/(<embed[^>]*>|<\/embed>)/i',
        '/(javascript:|data:|vbscript:)/i',
        '/(onload|onerror|onclick|onmouseover)=/i',
        '/(<link[^>]*rel=["\']?stylesheet)/i',
        '/(<style[^>]*>|<\/style>)/i',
    ];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CleanText) {
            throw new UnexpectedTypeException($constraint, CleanText::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        // Check for suspicious patterns
        foreach (self::SUSPICIOUS_PATTERNS as $pattern) {
            if (preg_match($pattern, $value)) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
                return;
            }
        }

        // Check for excessive HTML tags
        $strippedValue = strip_tags($value);
        $originalLength = strlen($value);
        $strippedLength = strlen($strippedValue);
        
        // If more than 50% of content is HTML tags, it's suspicious
        if ($originalLength > 0 && ($originalLength - $strippedLength) / $originalLength > 0.5) {
            $this->context->buildViolation('Text contains excessive HTML markup.')
                ->addViolation();
        }
    }
}