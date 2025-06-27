<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentsControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetEnrollmentsReturnsSuccessResponse(): void
    {
        $this->client->request('GET', '/api/enrollments');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetEnrollmentsReturnsJsonArray(): void
    {
        $this->client->request('GET', '/api/enrollments');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('data', $responseData);
    }

    private function createTeacher(): int
    {
        $teacherData = [
            'firstName' => 'Prof',
            'lastName' => 'Enrollment',
            'email' => 'prof.enrollment@example.com',
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

        $response = json_decode($this->client->getResponse()->getContent(), true);
        return $response['data']['id'];
    }

    private function createStudent(string $uniqueId = ''): int
    {
        $studentData = [
            'firstName' => 'Student' . $uniqueId,
            'lastName' => 'Test',
            'email' => 'student.test' . $uniqueId . '@example.com',
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

        $response = json_decode($this->client->getResponse()->getContent(), true);
        return $response['data']['id'];
    }

    private function createCourse(int $teacherId, string $uniqueCode = ''): int
    {
        $courseData = [
            'name' => 'Course Test ' . $uniqueCode,
            'code' => 'TEST' . ($uniqueCode ?: '101'),
            'credits' => 3,
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

        $response = json_decode($this->client->getResponse()->getContent(), true);
        return $response['data']['id'];
    }

    public function testCreateEnrollmentWithValidData(): void
    {
        $teacherId = $this->createTeacher();
        $studentId = $this->createStudent('enrollment1');
        $courseId = $this->createCourse($teacherId, '201');

        $enrollmentData = [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'status' => 'Actif',
            'enrollmentDate' => '2024-01-15T10:00:00+00:00'
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Actif', $responseData['data']['status']);
    }

    public function testCreateEnrollmentWithMissingStudent(): void
    {
        $teacherId = $this->createTeacher();
        $courseId = $this->createCourse($teacherId, '202');

        $enrollmentData = [
            'courseId' => $courseId,
            'status' => 'Actif'
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateEnrollmentWithMissingCourse(): void
    {
        $studentId = $this->createStudent('enrollment2');

        $enrollmentData = [
            'studentId' => $studentId,
            'status' => 'Actif'
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateEnrollmentWithInvalidStatus(): void
    {
        $teacherId = $this->createTeacher();
        $studentId = $this->createStudent('enrollment3');
        $courseId = $this->createCourse($teacherId, '203');

        $enrollmentData = [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'status' => 'StatusInvalide'
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateEnrollmentWithInvalidGrade(): void
    {
        $teacherId = $this->createTeacher();
        $studentId = $this->createStudent('enrollment4');
        $courseId = $this->createCourse($teacherId, '204');

        $enrollmentData = [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'status' => 'Actif',
            'grade' => 25 // Note invalide (max 20)
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateEnrollmentWithValidGrade(): void
    {
        $teacherId = $this->createTeacher();
        $studentId = $this->createStudent('enrollment5');
        $courseId = $this->createCourse($teacherId, '205');

        $enrollmentData = [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'status' => 'Terminé',
            'grade' => 15.5,
            'notes' => 'Très bon travail'
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(15.5, $responseData['data']['grade']);
        $this->assertEquals('Très bon travail', $responseData['data']['notes']);
    }

    public function testGetEnrollmentById(): void
    {
        $teacherId = $this->createTeacher();
        $studentId = $this->createStudent('getbyid');
        $courseId = $this->createCourse($teacherId, '206');

        // Créer une inscription
        $enrollmentData = [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'status' => 'Actif'
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $enrollmentId = $responseData['data']['id'];

        // Récupérer l'inscription
        $this->client->request('GET', '/api/enrollments/' . $enrollmentId);
        
        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Actif', $responseData['data']['status']);
    }

    public function testGetNonExistentEnrollment(): void
    {
        $this->client->request('GET', '/api/enrollments/99999');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateEnrollment(): void
    {
        $teacherId = $this->createTeacher();
        $studentId = $this->createStudent('update');
        $courseId = $this->createCourse($teacherId, '207');

        // Créer une inscription
        $enrollmentData = [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'status' => 'Actif'
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $enrollmentId = $responseData['data']['id'];

        // Mettre à jour l'inscription
        $updatedData = [
            'status' => 'Terminé',
            'grade' => 18,
            'notes' => 'Excellent travail!'
        ];

        $this->client->request(
            'PUT',
            '/api/enrollments/' . $enrollmentId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Terminé', $responseData['data']['status']);
        $this->assertEquals(18, $responseData['data']['grade']);
        $this->assertEquals('Excellent travail!', $responseData['data']['notes']);
    }

    public function testDeleteEnrollment(): void
    {
        $teacherId = $this->createTeacher();
        $studentId = $this->createStudent('delete');
        $courseId = $this->createCourse($teacherId, '208');

        // Créer une inscription
        $enrollmentData = [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'status' => 'Actif'
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $enrollmentId = $responseData['data']['id'];

        // Supprimer l'inscription
        $this->client->request('DELETE', '/api/enrollments/' . $enrollmentId);
        
        $this->assertResponseIsSuccessful();

        // Vérifier qu'elle n'existe plus
        $this->client->request('GET', '/api/enrollments/' . $enrollmentId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateEnrollmentWithAllValidStatuses(): void
    {
        $validStatuses = ['Actif', 'Terminé', 'Abandonné', 'En attente'];
        
        foreach ($validStatuses as $index => $status) {
            $teacherId = $this->createTeacher();
            $studentId = $this->createStudent('status' . $index);
            $courseId = $this->createCourse($teacherId, '30' . $index);

            $enrollmentData = [
                'studentId' => $studentId,
                'courseId' => $courseId,
                'status' => $status
            ];

            $this->client->request(
                'POST',
                '/api/enrollments',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($enrollmentData)
            );

            $this->assertResponseStatusCodeSame(Response::HTTP_CREATED, 
                "Failed for status: $status");
            
            $responseData = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals($status, $responseData['data']['status']);
        }
    }

    public function testUpdateEnrollmentWithNewStudentAndCourse(): void
    {
        $teacherId = $this->createTeacher();
        $studentId1 = $this->createStudent('original');
        $studentId2 = $this->createStudent('new');
        $courseId1 = $this->createCourse($teacherId, '401');
        $courseId2 = $this->createCourse($teacherId, '402');

        // Créer une inscription
        $enrollmentData = [
            'studentId' => $studentId1,
            'courseId' => $courseId1,
            'status' => 'Actif'
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $enrollmentId = $responseData['data']['id'];

        // Mettre à jour avec un nouvel étudiant et cours
        $updatedData = [
            'studentId' => $studentId2,
            'courseId' => $courseId2,
            'status' => 'En attente'
        ];

        $this->client->request(
            'PUT',
            '/api/enrollments/' . $enrollmentId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('En attente', $responseData['data']['status']);
    }

    public function testCreateEnrollmentWithGradeZero(): void
    {
        $teacherId = $this->createTeacher();
        $studentId = $this->createStudent('grade0');
        $courseId = $this->createCourse($teacherId, '501');

        $enrollmentData = [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'status' => 'Terminé',
            'grade' => 0
        ];

        $this->client->request(
            'POST',
            '/api/enrollments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($enrollmentData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(0, $responseData['data']['grade']);
    }

    public function testCorsHeaders(): void
    {
        $this->client->request('OPTIONS', '/api/enrollments');
        
        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertTrue($response->headers->has('Access-Control-Allow-Methods'));
    }
} 