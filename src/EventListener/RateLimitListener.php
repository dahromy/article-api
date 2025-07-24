<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitListener
{
    public function __construct(
        private readonly RateLimiterFactory $apiReadLimiter,
        private readonly RateLimiterFactory $apiWriteLimiter,
        private readonly RateLimiterFactory $authLimiter
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Skip rate limiting for non-API routes
        if (!str_starts_with($path, '/api/')) {
            return;
        }

        // Get client IP for rate limiting
        $clientId = $request->getClientIp();
        
        // Apply different rate limits based on endpoint
        if (str_contains($path, '/login_check')) {
            $limiter = $this->authLimiter->create($clientId);
        } elseif (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $limiter = $this->apiWriteLimiter->create($clientId);
        } else {
            $limiter = $this->apiReadLimiter->create($clientId);
        }

        $limit = $limiter->consume();
        
        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter();
            $retryAfterSeconds = $retryAfter instanceof \DateTimeInterface 
                ? max(1, $retryAfter->getTimestamp() - time())
                : ($retryAfter instanceof \DateInterval ? $retryAfter->s : 60);
                
            $response = new JsonResponse([
                'error' => 'Too many requests',
                'retry_after' => $retryAfterSeconds
            ], Response::HTTP_TOO_MANY_REQUESTS);
            
            $response->headers->set('X-RateLimit-Limit', (string) $limit->getLimit());
            $response->headers->set('X-RateLimit-Remaining', (string) $limit->getRemainingTokens());
            $response->headers->set('Retry-After', (string) $retryAfterSeconds);
            
            $event->setResponse($response);
        }
    }
}