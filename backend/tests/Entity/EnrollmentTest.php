<?php

namespace App\Tests\Entity;

use App\Entity\Enrollment;
use App\Entity\Student;
use App\Entity\Course;
use App\Entity\Teacher;
use PHPUnit\Framework\TestCase;

class EnrollmentTest extends TestCase
{
    private function createStudent(): Student
    {
        $student = new Student();
        $student->setFirstName('John');
        $student->setLastName('Doe');
        $student->setEmail('john.doe@example.com');
        return $student;
    }

    private function createCourse(): Course
    {
        $teacher = new Teacher();
        $teacher->setFirstName('Prof');
        $teacher->setLastName('Teacher');
        $teacher->setEmail('prof@example.com');
        $teacher->setSpeciality('Informatique');

        $course = new Course();
        $course->setName('Test Course');
        $course->setCode('TEST101');
        $course->setCredits(3);
        $course->setSemester('Automne');
        $course->setYear(2024);
        $course->setTeacher($teacher);
        
        return $course;
    }

    public function testEnrollmentCreation(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();

        $enrollment = new Enrollment();
        $enrollment->setStudent($student);
        $enrollment->setCourse($course);
        $enrollment->setStatus('Actif');
        $enrollment->setGrade(15.5);
        $enrollment->setNotes('Bon travail');

        $this->assertEquals($student, $enrollment->getStudent());
        $this->assertEquals($course, $enrollment->getCourse());
        $this->assertEquals('Actif', $enrollment->getStatus());
        $this->assertEquals(15.5, $enrollment->getGrade());
        $this->assertEquals('Bon travail', $enrollment->getNotes());
    }

    public function testEnrollmentDefaultValues(): void
    {
        $enrollment = new Enrollment();

        $this->assertEquals('Actif', $enrollment->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $enrollment->getEnrollmentDate());
        $this->assertInstanceOf(\DateTimeImmutable::class, $enrollment->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $enrollment->getUpdatedAt());
        $this->assertNull($enrollment->getGrade());
        $this->assertNull($enrollment->getNotes());
    }

    public function testEnrollmentStatusMethods(): void
    {
        $enrollment = new Enrollment();

        // Test status Actif (par défaut)
        $this->assertTrue($enrollment->isActive());
        $this->assertFalse($enrollment->isCompleted());
        $this->assertFalse($enrollment->isDropped());
        $this->assertFalse($enrollment->isPending());

        // Test status Terminé
        $enrollment->setStatus('Terminé');
        $this->assertFalse($enrollment->isActive());
        $this->assertTrue($enrollment->isCompleted());
        $this->assertFalse($enrollment->isDropped());
        $this->assertFalse($enrollment->isPending());

        // Test status Abandonné
        $enrollment->setStatus('Abandonné');
        $this->assertFalse($enrollment->isActive());
        $this->assertFalse($enrollment->isCompleted());
        $this->assertTrue($enrollment->isDropped());
        $this->assertFalse($enrollment->isPending());

        // Test status En attente
        $enrollment->setStatus('En attente');
        $this->assertFalse($enrollment->isActive());
        $this->assertFalse($enrollment->isCompleted());
        $this->assertFalse($enrollment->isDropped());
        $this->assertTrue($enrollment->isPending());
    }

    public function testEnrollmentMentionCalculation(): void
    {
        $enrollment = new Enrollment();

        // Sans note
        $this->assertNull($enrollment->getMention());

        // Note insuffisante
        $enrollment->setGrade(8);
        $this->assertEquals('Insuffisant', $enrollment->getMention());

        // Passable
        $enrollment->setGrade(10);
        $this->assertEquals('Passable', $enrollment->getMention());

        $enrollment->setGrade(11.5);
        $this->assertEquals('Passable', $enrollment->getMention());

        // Assez Bien
        $enrollment->setGrade(12);
        $this->assertEquals('Assez Bien', $enrollment->getMention());

        $enrollment->setGrade(13.5);
        $this->assertEquals('Assez Bien', $enrollment->getMention());

        // Bien
        $enrollment->setGrade(14);
        $this->assertEquals('Bien', $enrollment->getMention());

        $enrollment->setGrade(15.5);
        $this->assertEquals('Bien', $enrollment->getMention());

        // Très Bien
        $enrollment->setGrade(16);
        $this->assertEquals('Très Bien', $enrollment->getMention());

        $enrollment->setGrade(18.5);
        $this->assertEquals('Très Bien', $enrollment->getMention());

        $enrollment->setGrade(20);
        $this->assertEquals('Très Bien', $enrollment->getMention());
    }

    public function testEnrollmentIsPassedMethod(): void
    {
        $enrollment = new Enrollment();

        // Sans note
        $this->assertFalse($enrollment->isPassed());

        // Note insuffisante
        $enrollment->setGrade(9.5);
        $this->assertFalse($enrollment->isPassed());

        // Note juste passable
        $enrollment->setGrade(10);
        $this->assertTrue($enrollment->isPassed());

        // Note largement suffisante
        $enrollment->setGrade(15);
        $this->assertTrue($enrollment->isPassed());
    }

    public function testEnrollmentFullDisplay(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();

        $enrollment = new Enrollment();
        $enrollment->setStudent($student);
        $enrollment->setCourse($course);
        $enrollment->setStatus('Actif');

        $expected = 'John Doe - TEST101 (Actif)';
        $this->assertEquals($expected, $enrollment->getFullDisplay());
    }

    public function testEnrollmentStudentData(): void
    {
        $student = $this->createStudent();
        $enrollment = new Enrollment();
        $enrollment->setStudent($student);

        $studentData = $enrollment->getStudentData();

        $this->assertIsArray($studentData);
        $this->assertEquals('John', $studentData['firstName']);
        $this->assertEquals('Doe', $studentData['lastName']);
        $this->assertEquals('john.doe@example.com', $studentData['email']);
    }

    public function testEnrollmentStudentDataWithoutStudent(): void
    {
        $enrollment = new Enrollment();

        $this->assertNull($enrollment->getStudentData());
    }

    public function testEnrollmentCourseData(): void
    {
        $course = $this->createCourse();
        $enrollment = new Enrollment();
        $enrollment->setCourse($course);

        $courseData = $enrollment->getCourseData();

        $this->assertIsArray($courseData);
        $this->assertEquals('Test Course', $courseData['name']);
        $this->assertEquals('TEST101', $courseData['code']);
        $this->assertEquals(3, $courseData['credits']);
    }

    public function testEnrollmentCourseDataWithoutCourse(): void
    {
        $enrollment = new Enrollment();

        $this->assertNull($enrollment->getCourseData());
    }

    public function testEnrollmentUpdatedAtOnModification(): void
    {
        $enrollment = new Enrollment();
        $originalUpdatedAt = $enrollment->getUpdatedAt();

        // Petite pause pour s'assurer que le timestamp change
        usleep(1000);

        $enrollment->setStatus('Terminé');
        $newUpdatedAt = $enrollment->getUpdatedAt();

        $this->assertGreaterThan($originalUpdatedAt, $newUpdatedAt);
    }

    public function testEnrollmentGradeRange(): void
    {
        $enrollment = new Enrollment();

        // Notes valides (0-20)
        $validGrades = [0, 5.5, 10, 15.75, 20];

        foreach ($validGrades as $grade) {
            $enrollment->setGrade($grade);
            $this->assertEquals($grade, $enrollment->getGrade());
        }

        // Note null (optionnelle)
        $enrollment->setGrade(null);
        $this->assertNull($enrollment->getGrade());
    }

    public function testEnrollmentValidStatuses(): void
    {
        $enrollment = new Enrollment();
        $validStatuses = ['Actif', 'Terminé', 'Abandonné', 'En attente'];

        foreach ($validStatuses as $status) {
            $enrollment->setStatus($status);
            $this->assertEquals($status, $enrollment->getStatus());
        }
    }

    public function testEnrollmentDateManagement(): void
    {
        $enrollment = new Enrollment();
        $customDate = new \DateTimeImmutable('2024-01-15 10:30:00');

        $enrollment->setEnrollmentDate($customDate);

        $this->assertEquals($customDate, $enrollment->getEnrollmentDate());
        $this->assertEquals('2024-01-15', $enrollment->getEnrollmentDate()->format('Y-m-d'));
    }

    public function testEnrollmentNotesManagement(): void
    {
        $enrollment = new Enrollment();

        // Notes vides par défaut
        $this->assertNull($enrollment->getNotes());

        // Définir des notes
        $notes = 'Excellent travail en classe. Participation active aux discussions.';
        $enrollment->setNotes($notes);
        $this->assertEquals($notes, $enrollment->getNotes());

        // Notes longues
        $longNotes = str_repeat('Lorem ipsum dolor sit amet. ', 100);
        $enrollment->setNotes($longNotes);
        $this->assertEquals($longNotes, $enrollment->getNotes());
    }

    public function testEnrollmentGradeEdgeCases(): void
    {
        $enrollment = new Enrollment();

        // Note exactement 0
        $enrollment->setGrade(0.0);
        $this->assertEquals(0.0, $enrollment->getGrade());
        $this->assertEquals('Insuffisant', $enrollment->getMention());
        $this->assertFalse($enrollment->isPassed());

        // Note exactement 10
        $enrollment->setGrade(10.0);
        $this->assertEquals(10.0, $enrollment->getGrade());
        $this->assertEquals('Passable', $enrollment->getMention());
        $this->assertTrue($enrollment->isPassed());

        // Note exactement 20
        $enrollment->setGrade(20.0);
        $this->assertEquals(20.0, $enrollment->getGrade());
        $this->assertEquals('Très Bien', $enrollment->getMention());
        $this->assertTrue($enrollment->isPassed());
    }

    public function testEnrollmentRelationships(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $enrollment = new Enrollment();

        // Test assignation des relations
        $enrollment->setStudent($student);
        $enrollment->setCourse($course);

        $this->assertEquals($student, $enrollment->getStudent());
        $this->assertEquals($course, $enrollment->getCourse());

        // Test modification de relation
        $newStudent = new Student();
        $newStudent->setFirstName('Jane');
        $newStudent->setLastName('Smith');
        $newStudent->setEmail('jane.smith@example.com');

        $enrollment->setStudent($newStudent);
        $this->assertEquals($newStudent, $enrollment->getStudent());
        $this->assertNotEquals($student, $enrollment->getStudent());
    }
} 