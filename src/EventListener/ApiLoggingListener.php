<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApiLoggingListener
{
    private array $requestData = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Only log API requests
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $requestId = uniqid('req_', true);
        $request->attributes->set('request_id', $requestId);

        $this->requestData[$requestId] = [
            'timestamp' => microtime(true),
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'content_type' => $request->headers->get('Content-Type'),
        ];

        // Log request body for POST/PUT requests (excluding sensitive data)
        if (in_array($request->getMethod(), ['POST', 'PUT']) && $request->getContent()) {
            $content = $request->getContent();
            
            // Don't log passwords or sensitive data
            if (str_contains($request->getPathInfo(), '/login_check') || 
                str_contains($request->getPathInfo(), '/change-password')) {
                $this->requestData[$requestId]['body'] = '[REDACTED - Contains sensitive data]';
            } else {
                $this->requestData[$requestId]['body'] = mb_substr($content, 0, 1000); // Limit log size
            }
        }

        $this->logger->info('API Request', [
            'request_id' => $requestId,
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent')
        ]);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // Only log API responses
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $requestId = $request->attributes->get('request_id');
        if (!$requestId || !isset($this->requestData[$requestId])) {
            return;
        }

        $requestData = $this->requestData[$requestId];
        $responseTime = (microtime(true) - $requestData['timestamp']) * 1000; // Convert to milliseconds

        // Get user info if authenticated
        $userId = null;
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            $user = $token->getUser();
            $userId = method_exists($user, 'getId') ? $user->getId() : $user->getUserIdentifier();
        }

        $logData = [
            'request_id' => $requestId,
            'method' => $requestData['method'],
            'uri' => $requestData['uri'],
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => round($responseTime, 2),
            'ip' => $requestData['ip'],
            'user_id' => $userId,
            'content_length' => $response->headers->get('Content-Length'),
        ];

        // Log response body for errors (excluding sensitive data)
        if ($response->getStatusCode() >= 400) {
            $content = $response->getContent();
            if ($content && strlen($content) < 1000) {
                $logData['response_body'] = $content;
            }
        }

        $level = $response->getStatusCode() >= 500 ? 'error' : 
                ($response->getStatusCode() >= 400 ? 'warning' : 'info');

        $this->logger->log($level, 'API Response', $logData);

        // Clean up stored request data
        unset($this->requestData[$requestId]);
    }
}