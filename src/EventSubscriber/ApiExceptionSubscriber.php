<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Psr\Log\LoggerInterface;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    private bool $debug;
    
    public function __construct(
        private readonly LoggerInterface $logger,
        string $appEnv
    ) {
        $this->debug = $appEnv === 'dev';
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }
    
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        
        // Only handle exceptions for versioned API routes
        if (!str_starts_with($request->getPathInfo(), '/api/v')) {
            return;
        }
        
        $exception = $event->getThrowable();
        $statusCode = $this->getStatusCode($exception);
        $response = $this->createResponse($exception, $statusCode);
        
        $event->setResponse($response);
    }
    
    private function getStatusCode(\Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }
        
        if ($exception instanceof ValidationFailedException ||
            $exception instanceof NotEncodableValueException) {
            return JsonResponse::HTTP_UNPROCESSABLE_ENTITY;
        }
        
        return JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
    }
    
    private function createResponse(\Throwable $exception, int $statusCode): JsonResponse
    {
        $responseData = [
            'status' => $statusCode,
            'type' => $this->getExceptionType($exception),
        ];
        
        // Include more error details depending on exception type
        if ($exception instanceof HttpExceptionInterface) {
            $responseData['title'] = $exception->getMessage() ?: 'An error occurred';
        } elseif ($exception instanceof ValidationFailedException) {
            $violations = $exception->getViolations();
            $errors = [];
            
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            
            $responseData['title'] = 'Validation Failed';
            $responseData['errors'] = $errors;
        } elseif ($exception instanceof NotEncodableValueException) {
            $responseData['title'] = 'Invalid JSON format';
            $responseData['detail'] = $exception->getMessage();
        } else {
            $responseData['title'] = 'An unexpected error occurred';
            
            // Add exception details in debug mode only
            if ($this->debug) {
                $responseData['detail'] = $exception->getMessage();
                $responseData['trace'] = $exception->getTraceAsString();
            }
            
            // Log the error
            $this->logger->error($exception->getMessage(), [
                'exception' => $exception,
                'trace' => $exception->getTraceAsString(),
            ]);
        }
        
        return new JsonResponse($responseData, $statusCode);
    }
    
    private function getExceptionType(\Throwable $exception): string
    {
        $class = get_class($exception);
        $parts = explode('\\', $class);
        
        return end($parts);
    }
}