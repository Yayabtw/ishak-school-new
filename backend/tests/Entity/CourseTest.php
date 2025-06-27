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

        $this->assertFalse($course->isFull());

        $course->setMaxCapacity(2);
        $this->assertFalse($course->isFull());

        $enrollment1 = new Enrollment();
        $enrollment2 = new Enrollment();

        $course->addEnrollment($enrollment1);
        $this->assertFalse($course->isFull()); 

        $course->addEnrollment($enrollment2);
        $this->assertTrue($course->isFull()); 
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

    public function testCourseCodeFormat(): void
    {
        $course = new Course();

        $validCodes = ['MATH101', 'INFO2023', 'PHYS1001', 'BIO201'];

        foreach ($validCodes as $code) {
            $course->setCode($code);
            $this->assertEquals(strtoupper($code), $course->getCode());
        }
    }
}