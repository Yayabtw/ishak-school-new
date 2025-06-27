<?php

namespace App\Tests\Entity;

use App\Entity\Course;
use App\Entity\Teacher;
use App\Entity\Enrollment;
use PHPUnit\Framework\TestCase;

class CourseTest extends TestCase
{
    public function testCourseCreation(): void
    {
        $course = new Course();
        $course->setName('Mathématiques Avancées');
        $course->setDescription('Cours de mathématiques niveau universitaire');
        $course->setCode('MATH301');
        $course->setCredits(6);
        $course->setMaxCapacity(30);
        $course->setSemester('Automne');
        $course->setYear(2024);

        $this->assertEquals('Mathématiques Avancées', $course->getName());
        $this->assertEquals('Cours de mathématiques niveau universitaire', $course->getDescription());
        $this->assertEquals('MATH301', $course->getCode());
        $this->assertEquals(6, $course->getCredits());
        $this->assertEquals(30, $course->getMaxCapacity());
        $this->assertEquals('Automne', $course->getSemester());
        $this->assertEquals(2024, $course->getYear());
    }

    public function testCourseCodeAutoUppercase(): void
    {
        $course = new Course();
        $course->setCode('math101');

        $this->assertEquals('MATH101', $course->getCode());
    }

    public function testCourseTimestamps(): void
    {
        $course = new Course();
        $course->setName('Test Course');
        $course->setCode('TEST101');
        $course->setCredits(3);
        $course->setSemester('Printemps');

        $this->assertInstanceOf(\DateTimeImmutable::class, $course->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $course->getUpdatedAt());
    }

    public function testCourseDefaultYear(): void
    {
        $course = new Course();
        $currentYear = (int) date('Y');

        $this->assertEquals($currentYear, $course->getYear());
    }

    public function testCourseTeacherRelation(): void
    {
        $course = new Course();
        $teacher = new Teacher();
        $teacher->setFirstName('John');
        $teacher->setLastName('Doe');
        $teacher->setEmail('john.doe@example.com');
        $teacher->setSpeciality('Informatique');

        $course->setTeacher($teacher);

        $this->assertEquals($teacher, $course->getTeacher());
    }

    public function testCourseEnrollmentsCollection(): void
    {
        $course = new Course();

        $this->assertCount(0, $course->getEnrollments());
        $this->assertTrue($course->getEnrollments()->isEmpty());
    }

    public function testCourseEnrollmentManagement(): void
    {
        $course = new Course();
        $enrollment = new Enrollment();

        $course->addEnrollment($enrollment);

        $this->assertCount(1, $course->getEnrollments());
        $this->assertTrue($course->getEnrollments()->contains($enrollment));
        $this->assertEquals($course, $enrollment->getCourse());

        $course->removeEnrollment($enrollment);

        $this->assertCount(0, $course->getEnrollments());
        $this->assertFalse($course->getEnrollments()->contains($enrollment));
    }

    public function testCourseIsFullMethod(): void
    {
        $course = new Course();

        // Sans capacité maximale, le cours n'est jamais plein
        $this->assertFalse($course->isFull());

        // Avec capacité maximale mais sans inscriptions
        $course->setMaxCapacity(2);
        $this->assertFalse($course->isFull());

        // Ajouter des inscriptions
        $enrollment1 = new Enrollment();
        $enrollment2 = new Enrollment();

        $course->addEnrollment($enrollment1);
        $this->assertFalse($course->isFull()); // 1/2

        $course->addEnrollment($enrollment2);
        $this->assertTrue($course->isFull()); // 2/2
    }

    public function testCourseEnrollmentCount(): void
    {
        $course = new Course();

        $this->assertEquals(0, $course->getEnrollmentCount());

        $enrollment1 = new Enrollment();
        $enrollment2 = new Enrollment();

        $course->addEnrollment($enrollment1);
        $this->assertEquals(1, $course->getEnrollmentCount());

        $course->addEnrollment($enrollment2);
        $this->assertEquals(2, $course->getEnrollmentCount());

        $course->removeEnrollment($enrollment1);
        $this->assertEquals(1, $course->getEnrollmentCount());
    }

    public function testCourseFullDisplay(): void
    {
        $course = new Course();
        $course->setCode('MATH101');
        $course->setName('Mathématiques de Base');

        $expected = 'MATH101 - Mathématiques de Base';
        $this->assertEquals($expected, $course->getFullDisplay());
    }

    public function testCourseGetStudents(): void
    {
        $course = new Course();
        
        // Le cours devrait retourner un tableau vide au début
        $students = $course->getStudents();
        $this->assertIsArray($students);
        $this->assertEmpty($students);
    }

    public function testCourseUpdatedAtOnModification(): void
    {
        $course = new Course();
        $originalUpdatedAt = $course->getUpdatedAt();

        // Petite pause pour s'assurer que le timestamp change
        usleep(1000);

        $course->setName('Nouveau Nom');
        $newUpdatedAt = $course->getUpdatedAt();

        $this->assertGreaterThan($originalUpdatedAt, $newUpdatedAt);
    }

    public function testCourseSemesterValidValues(): void
    {
        $course = new Course();
        $validSemesters = ['Automne', 'Hiver', 'Printemps', 'Été'];

        foreach ($validSemesters as $semester) {
            $course->setSemester($semester);
            $this->assertEquals($semester, $course->getSemester());
        }
    }

    public function testCourseCreditsRange(): void
    {
        $course = new Course();

        // Test avec valeurs valides (1-10)
        for ($credits = 1; $credits <= 10; $credits++) {
            $course->setCredits($credits);
            $this->assertEquals($credits, $course->getCredits());
        }
    }

    public function testCourseYearRange(): void
    {
        $course = new Course();
        $validYears = [2020, 2024, 2030];

        foreach ($validYears as $year) {
            $course->setYear($year);
            $this->assertEquals($year, $course->getYear());
        }
    }

    public function testCourseMaxCapacityPositive(): void
    {
        $course = new Course();

        $course->setMaxCapacity(25);
        $this->assertEquals(25, $course->getMaxCapacity());

        $course->setMaxCapacity(null);
        $this->assertNull($course->getMaxCapacity());
    }

    public function testCourseNameLength(): void
    {
        $course = new Course();

        // Nom valide
        $validName = 'Cours de Test';
        $course->setName($validName);
        $this->assertEquals($validName, $course->getName());

        // Nom long (mais dans les limites)
        $longName = str_repeat('A', 200);
        $course->setName($longName);
        $this->assertEquals($longName, $course->getName());
    }

    public function testCourseCodeFormat(): void
    {
        $course = new Course();

        // Codes valides
        $validCodes = ['MATH101', 'INFO2023', 'PHYS1001', 'BIO201'];

        foreach ($validCodes as $code) {
            $course->setCode($code);
            // Le code est automatiquement mis en majuscules
            $this->assertEquals(strtoupper($code), $course->getCode());
        }
    }

    public function testCourseOptionalFields(): void
    {
        $course = new Course();

        // Description optionnelle
        $this->assertNull($course->getDescription());
        $course->setDescription('Description du cours');
        $this->assertEquals('Description du cours', $course->getDescription());

        // Capacité maximale optionnelle
        $this->assertNull($course->getMaxCapacity());
        $course->setMaxCapacity(50);
        $this->assertEquals(50, $course->getMaxCapacity());
    }

    public function testCourseRequiredFields(): void
    {
        $course = new Course();

        // Définir tous les champs obligatoires
        $course->setName('Cours Obligatoire');
        $course->setCode('REQ101');
        $course->setCredits(3);
        $course->setSemester('Automne');
        $course->setYear(2024);

        $this->assertNotEmpty($course->getName());
        $this->assertNotEmpty($course->getCode());
        $this->assertNotNull($course->getCredits());
        $this->assertNotEmpty($course->getSemester());
        $this->assertNotNull($course->getYear());
    }
} 