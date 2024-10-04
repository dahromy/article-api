<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JWTNotFoundListener
{
    public function onJWTNotFound(JWTNotFoundEvent $event): void
    {
        $data = [
            'status' => Response::HTTP_FORBIDDEN,
            'message' => 'AUTHENTICATION_FAILED',
        ];

        $response = new JsonResponse($data, Response::HTTP_FORBIDDEN);

        $event->setResponse($response);
    }
}