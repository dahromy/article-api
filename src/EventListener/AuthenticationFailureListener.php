<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationFailureListener
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

    public function onJWTExpired(JWTExpiredEvent $event): void
    {
        $data = [
            'status' => Response::HTTP_UNAUTHORIZED,
            'message' => 'TOKEN_EXPIRED',
        ];

        $response = new JsonResponse($data, Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }

    public function onJWTInvalid(JWTInvalidEvent $event): void
    {
        $data = [
            'status' => Response::HTTP_UNAUTHORIZED,
            'message' => 'TOKEN_INVALID',
        ];

        $response = new JsonResponse($data, Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }
}