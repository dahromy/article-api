<?php

namespace App\EventListener;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Simple rate limiter for API requests
 */
#[AsEventListener(event: 'kernel.request', priority: 5)]
class ApiRateLimitListener
{
    private const ANONYMOUS_LIMIT = 50;  // 50 requests per 15 minutes for anonymous users
    private const AUTHENTICATED_LIMIT = 200;  // 200 requests per 15 minutes for authenticated users
    private const WINDOW_SECONDS = 900;  // 15 minutes

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Only limit API requests
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        // Skip rate limiting for OPTIONS requests (preflight)
        if ($request->getMethod() === Request::METHOD_OPTIONS) {
            return;
        }

        // Determine the identifier for this client
        $clientId = $this->getClientIdentifier($request);
        
        // Determine limit based on authentication status
        $isAuthenticated = $this->tokenStorage->getToken() !== null;
        $limit = $isAuthenticated ? self::AUTHENTICATED_LIMIT : self::ANONYMOUS_LIMIT;
        
        // Get current count for this client
        $cacheKey = 'rate_limit_' . md5($clientId);
        $cacheItem = $this->cache->getItem($cacheKey);
        
        if (!$cacheItem->isHit()) {
            // First request in the window
            $data = [
                'count' => 1,
                'reset' => time() + self::WINDOW_SECONDS,
            ];
            $cacheItem->set($data);
            $cacheItem->expiresAfter(self::WINDOW_SECONDS);
            $this->cache->save($cacheItem);
            
            // Set rate limit headers
            $this->setRateLimitHeaders($event->getRequest(), 1, $limit, $data['reset']);
            return;
        }
        
        $data = $cacheItem->get();
        $count = $data['count'] + 1;
        $reset = $data['reset'];
        
        // Check if limit exceeded
        if ($count > $limit) {
            $retryAfter = max(1, $reset - time());
            
            throw new TooManyRequestsHttpException(
                $retryAfter,
                'Rate limit exceeded. Try again later.',
                null,
                0,
                [
                    'X-RateLimit-Limit' => $limit,
                    'X-RateLimit-Remaining' => 0,
                    'X-RateLimit-Reset' => $reset,
                    'Retry-After' => $retryAfter,
                ]
            );
        }
        
        // Update the count
        $data['count'] = $count;
        $cacheItem->set($data);
        $this->cache->save($cacheItem);
        
        // Set rate limit headers
        $this->setRateLimitHeaders($request, $count, $limit, $reset);
    }
    
    private function getClientIdentifier(Request $request): string
    {
        // Get token if user is authenticated
        $token = $this->tokenStorage->getToken();
        if ($token !== null && $token->getUser() !== null) {
            return 'user_' . $token->getUserIdentifier();
        }
        
        // Fall back to IP address for anonymous users
        return 'ip_' . $request->getClientIp();
    }
    
    private function setRateLimitHeaders(Request $request, int $count, int $limit, int $reset): void
    {
        $request->attributes->set('rate_limit_headers', [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => max(0, $limit - $count),
            'X-RateLimit-Reset' => $reset,
        ]);
    }
}