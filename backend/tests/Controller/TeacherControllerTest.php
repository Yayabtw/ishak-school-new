<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TeacherControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetTeachersReturnsSuccessResponse(): void
    {
        $this->client->request('GET', '/api/teachers');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTeachersReturnsJsonArray(): void
    {
        $this->client->request('GET', '/api/teachers');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertIsArray($responseData);
    }

    public function testCreateTeacherWithValidData(): void
    {
        $teacherData = [
            'firstName' => 'Test',
            'lastName' => 'Teacher',
            'email' => 'test.teacher@example.com',
            'phone' => '+33123456789',
            'speciality' => 'Informatique'
        ];

        $this->client->request(
            'POST',
            '/api/teachers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teacherData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testCreateTeacherWithInvalidEmail(): void
    {
        $teacherData = [
            'firstName' => 'Test',
            'lastName' => 'Teacher',
            'email' => 'invalid-email',
            'speciality' => 'Informatique'
        ];

        $this->client->request(
            'POST',
            '/api/teachers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teacherData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateTeacherWithMissingRequiredFields(): void
    {
        $teacherData = [
            'firstName' => 'Test'
            // Manque lastName, email, speciality
        ];

        $this->client->request(
            'POST',
            '/api/teachers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teacherData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetTeacherById(): void
    {
        // D'abord créer un enseignant
        $teacherData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'speciality' => 'Mathématiques'
        ];

        $this->client->request(
            'POST',
            '/api/teachers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teacherData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $teacherId = $responseData['data']['id'] ?? 1;

        // Ensuite le récupérer
        $this->client->request('GET', '/api/teachers/' . $teacherId);
        
        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('John', $responseData['data']['firstName']);
        $this->assertEquals('Doe', $responseData['data']['lastName']);
    }

    public function testGetNonExistentTeacher(): void
    {
        $this->client->request('GET', '/api/teachers/99999');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateTeacher(): void
    {
        // Créer un enseignant d'abord
        $teacherData = [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'speciality' => 'Physique'
        ];

        $this->client->request(
            'POST',
            '/api/teachers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teacherData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $teacherId = $responseData['data']['id'] ?? 1;

        // Mettre à jour
        $updatedData = [
            'firstName' => 'Jane Updated',
            'lastName' => 'Smith Updated',
            'email' => 'jane.updated@example.com',
            'speciality' => 'Chimie'
        ];

        $this->client->request(
            'PUT',
            '/api/teachers/' . $teacherId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteTeacher(): void
    {
        // Créer un enseignant d'abord
        $teacherData = [
            'firstName' => 'ToDelete',
            'lastName' => 'Teacher',
            'email' => 'delete@example.com',
            'speciality' => 'Histoire'
        ];

        $this->client->request(
            'POST',
            '/api/teachers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teacherData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $teacherId = $responseData['data']['id'] ?? 1;

        // Supprimer
        $this->client->request('DELETE', '/api/teachers/' . $teacherId);
        
        $this->assertResponseIsSuccessful();

        // Vérifier qu'il n'existe plus
        $this->client->request('GET', '/api/teachers/' . $teacherId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetTeacherCourses(): void
    {
        $this->client->request('GET', '/api/teachers/1/courses');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }

    public function testCorsHeaders(): void
    {
        $this->client->request('OPTIONS', '/api/teachers');
        
        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertTrue($response->headers->has('Access-Control-Allow-Methods'));
    }
} 