<?php

namespace App\EventListener;

use Doctrine\ODM\MongoDB\MongoDBException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $environment
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle API routes
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $response = $this->createApiErrorResponse($exception);
        $event->setResponse($response);
    }

    private function createApiErrorResponse(\Throwable $exception): JsonResponse
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $error = [
            'error' => 'Internal Server Error',
            'message' => 'An unexpected error occurred'
        ];

        // Handle specific exception types
        switch (true) {
            case $exception instanceof NotFoundHttpException:
                $statusCode = Response::HTTP_NOT_FOUND;
                $error = [
                    'error' => 'Not Found',
                    'message' => $exception->getMessage() ?: 'The requested resource was not found'
                ];
                break;

            case $exception instanceof UnauthorizedHttpException:
                $statusCode = Response::HTTP_UNAUTHORIZED;
                $error = [
                    'error' => 'Unauthorized',
                    'message' => 'Authentication is required'
                ];
                break;

            case $exception instanceof AccessDeniedHttpException:
                $statusCode = Response::HTTP_FORBIDDEN;
                $error = [
                    'error' => 'Forbidden',
                    'message' => $exception->getMessage() ?: 'Access denied'
                ];
                break;

            case $exception instanceof BadRequestHttpException:
            case $exception instanceof NotEncodableValueException:
                $statusCode = Response::HTTP_BAD_REQUEST;
                $error = [
                    'error' => 'Bad Request',
                    'message' => 'Invalid request format or data'
                ];
                break;

            case $exception instanceof ValidationFailedException:
                $statusCode = Response::HTTP_BAD_REQUEST;
                $violations = [];
                foreach ($exception->getViolations() as $violation) {
                    $violations[$violation->getPropertyPath()] = $violation->getMessage();
                }
                $error = [
                    'error' => 'Validation Failed',
                    'message' => 'The provided data is invalid',
                    'violations' => $violations
                ];
                break;

            case $exception instanceof MongoDBException:
                $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
                $error = [
                    'error' => 'Database Error',
                    'message' => 'Database service is temporarily unavailable'
                ];
                break;

            default:
                // Log unexpected exceptions
                $this->logger->error('Unexpected API exception', [
                    'exception' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ]);
                break;
        }

        // Add debug information in development environment
        if ($this->environment === 'dev') {
            $error['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        return new JsonResponse($error, $statusCode);
    }
}