<?php

namespace App\Tests\Validator;

use App\Validator\CleanText;
use App\Validator\CleanTextValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CleanTextValidatorTest extends TestCase
{
    private CleanTextValidator $validator;
    private ExecutionContextInterface $context;
    private ConstraintViolationBuilderInterface $violationBuilder;

    protected function setUp(): void
    {
        $this->validator = new CleanTextValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        
        $this->validator->initialize($this->context);
    }

    public function testValidCleanText(): void
    {
        $constraint = new CleanText();
        
        $this->context->expects($this->never())
            ->method('buildViolation');
            
        $this->validator->validate('This is clean text content', $constraint);
    }

    public function testNullValue(): void
    {
        $constraint = new CleanText();
        
        $this->context->expects($this->never())
            ->method('buildViolation');
            
        $this->validator->validate(null, $constraint);
    }

    public function testEmptyString(): void
    {
        $constraint = new CleanText();
        
        $this->context->expects($this->never())
            ->method('buildViolation');
            
        $this->validator->validate('', $constraint);
    }

    public function testScriptTag(): void
    {
        $constraint = new CleanText();
        
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($this->violationBuilder);
            
        $this->violationBuilder->expects($this->once())
            ->method('addViolation');
            
        $this->validator->validate('<script>alert("xss")</script>', $constraint);
    }

    public function testIframeTag(): void
    {
        $constraint = new CleanText();
        
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($this->violationBuilder);
            
        $this->violationBuilder->expects($this->once())
            ->method('addViolation');
            
        $this->validator->validate('<iframe src="malicious.com"></iframe>', $constraint);
    }

    public function testJavaScriptUrl(): void
    {
        $constraint = new CleanText();
        
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($this->violationBuilder);
            
        $this->violationBuilder->expects($this->once())
            ->method('addViolation');
            
        $this->validator->validate('Click here: javascript:alert("xss")', $constraint);
    }

    public function testEventHandlers(): void
    {
        $constraint = new CleanText();
        
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($this->violationBuilder);
            
        $this->violationBuilder->expects($this->once())
            ->method('addViolation');
            
        $this->validator->validate('<img src="x" onerror="alert(1)">', $constraint);
    }

    public function testExcessiveHtmlMarkup(): void
    {
        $constraint = new CleanText();
        
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('Text contains excessive HTML markup.')
            ->willReturn($this->violationBuilder);
            
        $this->violationBuilder->expects($this->once())
            ->method('addViolation');
            
        // Text with more than 30% HTML markup
        $htmlHeavyText = '<div><span><strong><em><u><i><b>text</b></i></u></em></strong></span></div>';
        $this->validator->validate($htmlHeavyText, $constraint);
    }

    public function testBasicHtmlIsAllowed(): void
    {
        $constraint = new CleanText();
        
        $this->context->expects($this->never())
            ->method('buildViolation');
            
        // Text with reasonable amount of HTML
        $normalText = 'This is <strong>normal</strong> text with some <em>formatting</em>.';
        $this->validator->validate($normalText, $constraint);
    }
}