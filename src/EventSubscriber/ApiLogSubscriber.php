<?php

namespace App\EventSubscriber;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[WithMonologChannel('api')]
readonly class ApiLogSubscriber implements EventSubscriberInterface
{


    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (str_starts_with($request->getPathInfo(), '/api')) {
            $this->logger->info('API Request', [
                'method' => $request->getMethod(),
                'path' => $request->getPathInfo(),
                'query' => $request->query->all(),
                'body' => $request->getContent(),
            ]);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if (str_starts_with($request->getPathInfo(), '/api')) {
            $this->logger->info('API Response', [
                'status_code' => $response->getStatusCode(),
                'content' => $response->getContent(),
            ]);
        }
    }
}