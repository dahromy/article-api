<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Adds rate limit headers to API responses
 */
#[AsEventListener(event: 'kernel.response', priority: 0)]
class ApiRateLimitResponseListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        
        // Only for API routes
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }
        
        // Add rate limit headers if they were set
        if ($request->attributes->has('rate_limit_headers')) {
            $headers = $request->attributes->get('rate_limit_headers');
            foreach ($headers as $name => $value) {
                $response->headers->set($name, $value);
            }
        }
    }
}