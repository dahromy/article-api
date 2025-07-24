<?php

namespace App\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
    }

    #[OA\Get(
        path: '/health',
        summary: 'Health check endpoint',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service is healthy',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'healthy'),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'services', type: 'object', properties: [
                            new OA\Property(property: 'database', type: 'string', example: 'connected'),
                            new OA\Property(property: 'api', type: 'string', example: 'operational')
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 503,
                description: 'Service is unhealthy'
            )
        ]
    )]
    #[Route('/health', methods: ['GET'])]
    public function check(): JsonResponse
    {
        $status = 'healthy';
        $services = [
            'api' => 'operational'
        ];

        // Check database connection
        try {
            $this->documentManager->getDocumentDatabase('App\Document\User');
            $services['database'] = 'connected';
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $services['database'] = 'disconnected';
        }

        $response = [
            'status' => $status,
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
            'services' => $services,
            'version' => '1.0.0'
        ];

        $httpStatus = $status === 'healthy' ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;
        
        return $this->json($response, $httpStatus);
    }

    #[OA\Get(
        path: '/health/ready',
        summary: 'Readiness check endpoint',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service is ready'
            ),
            new OA\Response(
                response: 503,
                description: 'Service is not ready'
            )
        ]
    )]
    #[Route('/health/ready', methods: ['GET'])]
    public function ready(): JsonResponse
    {
        // Check if the service is ready to accept requests
        try {
            // Check database connectivity
            $this->documentManager->getDocumentDatabase('App\Document\User');
            
            return $this->json([
                'status' => 'ready',
                'timestamp' => (new \DateTime())->format(\DateTime::ATOM)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'not ready',
                'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
                'error' => 'Database connection failed'
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    #[OA\Get(
        path: '/health/live',
        summary: 'Liveness check endpoint',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service is alive'
            )
        ]
    )]
    #[Route('/health/live', methods: ['GET'])]
    public function live(): JsonResponse
    {
        return $this->json([
            'status' => 'alive',
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM)
        ]);
    }
}