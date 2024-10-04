<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JWTExpiredListener
{
    public function onJWTExpired(JWTExpiredEvent $event): void
    {
        $data = [
            'status' => Response::HTTP_UNAUTHORIZED,
            'message' => 'TOKEN_EXPIRED',
        ];

        $response = new JsonResponse($data, Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }
}