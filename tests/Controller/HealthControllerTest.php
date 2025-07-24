<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HealthControllerTest extends WebTestCase
{
    public function testHealthCheckEndpoint(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/health');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('timestamp', $responseData);
        $this->assertArrayHasKey('services', $responseData);
        $this->assertArrayHasKey('version', $responseData);
        
        $this->assertContains($responseData['status'], ['healthy', 'unhealthy']);
        $this->assertArrayHasKey('api', $responseData['services']);
        $this->assertEquals('operational', $responseData['services']['api']);
    }

    public function testReadinessEndpoint(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/health/ready');
        
        // Should return 200 or 503 depending on database connectivity
        $this->assertContains($client->getResponse()->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_SERVICE_UNAVAILABLE
        ]);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('timestamp', $responseData);
        $this->assertContains($responseData['status'], ['ready', 'not ready']);
    }

    public function testLivenessEndpoint(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/health/live');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('timestamp', $responseData);
        $this->assertEquals('alive', $responseData['status']);
    }

    public function testHealthCheckResponseFormat(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/health');
        
        $response = $client->getResponse();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        
        $responseData = json_decode($response->getContent(), true);
        
        // Validate timestamp format (ISO 8601)
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $responseData['timestamp']
        );
        
        // Validate version format
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $responseData['version']);
    }
}