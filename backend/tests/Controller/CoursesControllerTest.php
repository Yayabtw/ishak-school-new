<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CoursesControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetCoursesReturnsSuccessResponse(): void
    {
        $this->client->request('GET', '/api/courses');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetCoursesReturnsJsonArray(): void
    {
        $this->client->request('GET', '/api/courses');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('data', $responseData);
    }

    public function testCreateCourseWithValidData(): void
    {
        // D'abord créer un enseignant pour l'associer au cours
        $teacherData = [
            'firstName' => 'Prof',
            'lastName' => 'Math',
            'email' => 'prof.math@example.com',
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

        $teacherResponse = json_decode($this->client->getResponse()->getContent(), true);
        $teacherId = $teacherResponse['data']['id'];

        // Maintenant créer le cours
        $courseData = [
            'name' => 'Mathématiques Avancées',
            'description' => 'Cours de mathématiques niveau avancé',
            'code' => 'MATH301',
            'credits' => 6,
            'maxCapacity' => 30,
            'semester' => 'Automne',
            'year' => 2024,
            'teacherId' => $teacherId
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('MATH301', $responseData['data']['code']);
    }

    public function testCreateCourseWithInvalidCode(): void
    {
        $courseData = [
            'name' => 'Test Course',
            'code' => 'invalid-code', // Code invalide
            'credits' => 3,
            'semester' => 'Automne',
            'year' => 2024
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateCourseWithMissingRequiredFields(): void
    {
        $courseData = [
            'name' => 'Test Course'
            // Manque code, credits, semester, year
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateCourseWithInvalidTeacherId(): void
    {
        $courseData = [
            'name' => 'Test Course',
            'code' => 'TEST101',
            'credits' => 3,
            'semester' => 'Automne',
            'year' => 2024,
            'teacherId' => 99999 // ID d'enseignant inexistant
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetCourseById(): void
    {
        // Créer un enseignant
        $teacherData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe.courses@example.com',
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

        $teacherResponse = json_decode($this->client->getResponse()->getContent(), true);
        $teacherId = $teacherResponse['data']['id'];

        // Créer un cours
        $courseData = [
            'name' => 'Programmation Web',
            'description' => 'Cours de développement web',
            'code' => 'WEB101',
            'credits' => 4,
            'semester' => 'Printemps',
            'year' => 2024,
            'teacherId' => $teacherId
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $courseId = $responseData['data']['id'];

        // Récupérer le cours
        $this->client->request('GET', '/api/courses/' . $courseId);
        
        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Programmation Web', $responseData['data']['name']);
        $this->assertEquals('WEB101', $responseData['data']['code']);
    }

    public function testGetNonExistentCourse(): void
    {
        $this->client->request('GET', '/api/courses/99999');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateCourse(): void
    {
        // Créer un enseignant
        $teacherData = [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith.update@example.com',
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

        $teacherResponse = json_decode($this->client->getResponse()->getContent(), true);
        $teacherId = $teacherResponse['data']['id'];

        // Créer un cours
        $courseData = [
            'name' => 'Physique Générale',
            'code' => 'PHYS101',
            'credits' => 5,
            'semester' => 'Automne',
            'year' => 2024,
            'teacherId' => $teacherId
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $courseId = $responseData['data']['id'];

        // Mettre à jour le cours
        $updatedData = [
            'name' => 'Physique Générale Avancée',
            'description' => 'Cours de physique niveau avancé',
            'credits' => 6,
            'maxCapacity' => 25
        ];

        $this->client->request(
            'PUT',
            '/api/courses/' . $courseId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Physique Générale Avancée', $responseData['data']['name']);
        $this->assertEquals(6, $responseData['data']['credits']);
    }

    public function testDeleteCourse(): void
    {
        // Créer un enseignant
        $teacherData = [
            'firstName' => 'ToDelete',
            'lastName' => 'Teacher',
            'email' => 'delete.teacher@example.com',
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

        $teacherResponse = json_decode($this->client->getResponse()->getContent(), true);
        $teacherId = $teacherResponse['data']['id'];

        // Créer un cours
        $courseData = [
            'name' => 'Cours à Supprimer',
            'code' => 'DEL101',
            'credits' => 2,
            'semester' => 'Été',
            'year' => 2024,
            'teacherId' => $teacherId
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $courseId = $responseData['data']['id'];

        // Supprimer le cours
        $this->client->request('DELETE', '/api/courses/' . $courseId);
        
        $this->assertResponseIsSuccessful();

        // Vérifier qu'il n'existe plus
        $this->client->request('GET', '/api/courses/' . $courseId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateCourseWithInvalidSemester(): void
    {
        $courseData = [
            'name' => 'Test Course',
            'code' => 'TEST102',
            'credits' => 3,
            'semester' => 'InvalidSemester', // Semestre invalide
            'year' => 2024
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateCourseWithInvalidYear(): void
    {
        $courseData = [
            'name' => 'Test Course',
            'code' => 'TEST103',
            'credits' => 3,
            'semester' => 'Automne',
            'year' => 2040 // Année invalide (hors limites)
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateCourseWithInvalidCredits(): void
    {
        $courseData = [
            'name' => 'Test Course',
            'code' => 'TEST104',
            'credits' => 15, // Trop de crédits (max 10)
            'semester' => 'Automne',
            'year' => 2024
        ];

        $this->client->request(
            'POST',
            '/api/courses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($courseData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCorsHeaders(): void
    {
        $this->client->request('OPTIONS', '/api/courses');
        
        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertTrue($response->headers->has('Access-Control-Allow-Methods'));
    }
} 