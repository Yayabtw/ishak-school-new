<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class StudentControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetStudentsReturnsSuccessResponse(): void
    {
        $this->client->request('GET', '/api/students');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetStudentsReturnsJsonArray(): void
    {
        $this->client->request('GET', '/api/students');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('data', $responseData);
    }

    public function testCreateStudentWithValidData(): void
    {
        $studentData = [
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'email' => 'jean.dupont@student.example.com',
            'phone' => '+33123456789',
            'address' => '123 Rue de la Paix, Paris',
            'birthDate' => '2000-05-15'
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Jean', $responseData['data']['firstName']);
        $this->assertEquals('Dupont', $responseData['data']['lastName']);
        $this->assertNotNull($responseData['data']['studentNumber']); // Vérifie que le numéro étudiant est généré
    }

    public function testCreateStudentWithInvalidEmail(): void
    {
        $studentData = [
            'firstName' => 'Test',
            'lastName' => 'Student',
            'email' => 'invalid-email',
            'birthDate' => '2000-01-01'
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateStudentWithMissingRequiredFields(): void
    {
        $studentData = [
            'firstName' => 'Test'
            // Manque lastName, email
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateStudentWithInvalidBirthDate(): void
    {
        $studentData = [
            'firstName' => 'Test',
            'lastName' => 'Student',
            'email' => 'test.student@example.com',
            'birthDate' => '2030-01-01' // Date future
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateStudentWithInvalidPhone(): void
    {
        $studentData = [
            'firstName' => 'Test',
            'lastName' => 'Student',
            'email' => 'test.phone@example.com',
            'phone' => 'invalid-phone-format',
            'birthDate' => '2000-01-01'
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetStudentById(): void
    {
        // Créer un étudiant
        $studentData = [
            'firstName' => 'Marie',
            'lastName' => 'Martin',
            'email' => 'marie.martin@example.com',
            'phone' => '+33987654321',
            'birthDate' => '1999-03-20'
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $studentId = $responseData['data']['id'];

        // Récupérer l'étudiant
        $this->client->request('GET', '/api/students/' . $studentId);
        
        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Marie', $responseData['data']['firstName']);
        $this->assertEquals('Martin', $responseData['data']['lastName']);
    }

    public function testGetNonExistentStudent(): void
    {
        $this->client->request('GET', '/api/students/99999');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateStudent(): void
    {
        // Créer un étudiant
        $studentData = [
            'firstName' => 'Pierre',
            'lastName' => 'Dubois',
            'email' => 'pierre.dubois@example.com',
            'birthDate' => '1998-12-10'
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $studentId = $responseData['data']['id'];

        // Mettre à jour l'étudiant
        $updatedData = [
            'firstName' => 'Pierre Updated',
            'lastName' => 'Dubois Updated',
            'email' => 'pierre.updated@example.com',
            'phone' => '+33111222333',
            'address' => '456 Avenue des Champs, Lyon'
        ];

        $this->client->request(
            'PUT',
            '/api/students/' . $studentId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Pierre Updated', $responseData['data']['firstName']);
        $this->assertEquals('+33111222333', $responseData['data']['phone']);
    }

    public function testDeleteStudent(): void
    {
        // Créer un étudiant
        $studentData = [
            'firstName' => 'ToDelete',
            'lastName' => 'Student',
            'email' => 'delete.student@example.com',
            'birthDate' => '2001-06-15'
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $studentId = $responseData['data']['id'];

        // Supprimer l'étudiant
        $this->client->request('DELETE', '/api/students/' . $studentId);
        
        $this->assertResponseIsSuccessful();

        // Vérifier qu'il n'existe plus
        $this->client->request('GET', '/api/students/' . $studentId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetStudentEnrollments(): void
    {
        // Créer un étudiant
        $studentData = [
            'firstName' => 'TestEnrollments',
            'lastName' => 'Student',
            'email' => 'test.enrollments@example.com',
            'birthDate' => '2000-08-25'
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $studentId = $responseData['data']['id'];

        // Tester l'endpoint des inscriptions de l'étudiant
        $this->client->request('GET', '/api/students/' . $studentId . '/enrollments');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }

    public function testCreateStudentWithMinimalData(): void
    {
        $studentData = [
            'firstName' => 'Minimal',
            'lastName' => 'Student',
            'email' => 'minimal.student@example.com'
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Minimal', $responseData['data']['firstName']);
    }

    public function testUpdateStudentWithPartialData(): void
    {
        // Créer un étudiant
        $studentData = [
            'firstName' => 'Partial',
            'lastName' => 'Update',
            'email' => 'partial.update@example.com',
            'birthDate' => '1999-11-11'
        ];

        $this->client->request(
            'POST',
            '/api/students',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($studentData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $studentId = $responseData['data']['id'];

        // Mettre à jour seulement le téléphone
        $updatedData = [
            'phone' => '+33777888999'
        ];

        $this->client->request(
            'PUT',
            '/api/students/' . $studentId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('+33777888999', $responseData['data']['phone']);
        $this->assertEquals('Partial', $responseData['data']['firstName']); // Autres champs inchangés
    }

    public function testCreateStudentWithValidPhoneFormats(): void
    {
        $validPhones = [
            '+33123456789',
            '01 23 45 67 89',
            '01-23-45-67-89',
            '(01) 23 45 67 89'
        ];

        foreach ($validPhones as $index => $phone) {
            $studentData = [
                'firstName' => 'PhoneTest' . $index,
                'lastName' => 'Student',
                'email' => 'phone.test' . $index . '@example.com',
                'phone' => $phone,
                'birthDate' => '2000-01-01'
            ];

            $this->client->request(
                'POST',
                '/api/students',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($studentData)
            );

            $this->assertResponseStatusCodeSame(Response::HTTP_CREATED, 
                "Failed for phone format: $phone");
        }
    }

    public function testCorsHeaders(): void
    {
        $this->client->request('OPTIONS', '/api/students');
        
        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertTrue($response->headers->has('Access-Control-Allow-Methods'));
    }
} 