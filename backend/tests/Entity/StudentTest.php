<?php

namespace App\Tests\Entity;

use App\Entity\Student;
use App\Entity\Enrollment;
use PHPUnit\Framework\TestCase;

class StudentTest extends TestCase
{
    public function testStudentCreation(): void
    {
        $student = new Student();
        $student->setFirstName('Marie');
        $student->setLastName('Dupont');
        $student->setEmail('marie.dupont@example.com');
        $student->setPhone('+33123456789');
        $student->setAddress('123 Rue de la Paix, Paris');
        $student->setBirthDate(new \DateTime('2000-05-15'));

        $this->assertEquals('Marie', $student->getFirstName());
        $this->assertEquals('Dupont', $student->getLastName());
        $this->assertEquals('marie.dupont@example.com', $student->getEmail());
        $this->assertEquals('+33123456789', $student->getPhone());
        $this->assertEquals('123 Rue de la Paix, Paris', $student->getAddress());
        $this->assertEquals('2000-05-15', $student->getBirthDate()->format('Y-m-d'));
    }

    public function testStudentFullName(): void
    {
        $student = new Student();
        $student->setFirstName('Jean');
        $student->setLastName('Martin');

        $this->assertEquals('Jean Martin', $student->getFullName());
    }

    public function testStudentTimestamps(): void
    {
        $student = new Student();
        $student->setFirstName('Test');
        $student->setLastName('User');
        $student->setEmail('test@example.com');

        $this->assertInstanceOf(\DateTimeImmutable::class, $student->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $student->getUpdatedAt());
    }

    public function testStudentNumberGeneration(): void
    {
        $student = new Student();
        $studentNumber = $student->getStudentNumber();

        $this->assertNotNull($studentNumber);
        $this->assertStringStartsWith('STU', $studentNumber);
        $this->assertEquals(11, strlen($studentNumber)); // STU + 4 digits year + 4 digits random
        
        // Vérifier le format avec regex
        $this->assertMatchesRegularExpression('/^STU\d{8}$/', $studentNumber);
    }

    public function testStudentNumberUniqueness(): void
    {
        $student1 = new Student();
        $student2 = new Student();

        $number1 = $student1->getStudentNumber();
        $number2 = $student2->getStudentNumber();

        // Les numéros devraient être différents (très probable avec random_int)
        $this->assertNotEquals($number1, $number2);
    }

    public function testStudentAgeCalculation(): void
    {
        $student = new Student();

        // Sans date de naissance
        $this->assertNull($student->getAge());

        // Avec date de naissance (25 ans)
        $birthDate = new \DateTime('1999-01-01');
        $student->setBirthDate($birthDate);
        
        $expectedAge = $birthDate->diff(new \DateTime())->y;
        $this->assertEquals($expectedAge, $student->getAge());
    }

    public function testStudentAgeWithFutureBirthDate(): void
    {
        $student = new Student();
        
        // Date future (ne devrait pas arriver en réalité avec validation)
        $futureBirthDate = new \DateTime('+1 year');
        $student->setBirthDate($futureBirthDate);
        
        // L'âge sera négatif ou 0
        $age = $student->getAge();
        $this->assertLessThanOrEqual(0, $age);
    }

    public function testStudentEnrollmentsCollection(): void
    {
        $student = new Student();

        $this->assertCount(0, $student->getEnrollments());
        $this->assertTrue($student->getEnrollments()->isEmpty());
    }

    public function testStudentEnrollmentManagement(): void
    {
        $student = new Student();
        $enrollment = new Enrollment();

        $student->addEnrollment($enrollment);

        $this->assertCount(1, $student->getEnrollments());
        $this->assertTrue($student->getEnrollments()->contains($enrollment));
        $this->assertEquals($student, $enrollment->getStudent());

        $student->removeEnrollment($enrollment);

        $this->assertCount(0, $student->getEnrollments());
        $this->assertFalse($student->getEnrollments()->contains($enrollment));
    }

    public function testStudentGetCourses(): void
    {
        $student = new Student();
        
        // L'étudiant devrait retourner un tableau vide au début
        $courses = $student->getCourses();
        $this->assertIsArray($courses);
        $this->assertEmpty($courses);
    }

    public function testStudentUpdatedAtOnModification(): void
    {
        $student = new Student();
        $originalUpdatedAt = $student->getUpdatedAt();

        // Petite pause pour s'assurer que le timestamp change
        usleep(1000);

        $student->setFirstName('Nouveau Prénom');
        $newUpdatedAt = $student->getUpdatedAt();

        $this->assertGreaterThan($originalUpdatedAt, $newUpdatedAt);
    }

    public function testStudentEmailValidation(): void
    {
        $student = new Student();
        
        // Email valide
        $validEmail = 'test@example.com';
        $student->setEmail($validEmail);
        $this->assertEquals($validEmail, $student->getEmail());
    }

    public function testStudentPhoneValidation(): void
    {
        $student = new Student();

        // Téléphones valides
        $validPhones = [
            '+33123456789',
            '01 23 45 67 89',
            '01-23-45-67-89',
            '(01) 23 45 67 89',
            null // Optionnel
        ];

        foreach ($validPhones as $phone) {
            $student->setPhone($phone);
            $this->assertEquals($phone, $student->getPhone());
        }
    }

    public function testStudentRequiredFields(): void
    {
        $student = new Student();

        // Définir les champs obligatoires
        $student->setFirstName('John');
        $student->setLastName('Doe');
        $student->setEmail('john.doe@example.com');

        $this->assertNotEmpty($student->getFirstName());
        $this->assertNotEmpty($student->getLastName());
        $this->assertNotEmpty($student->getEmail());
    }

    public function testStudentOptionalFields(): void
    {
        $student = new Student();

        // Champs optionnels par défaut
        $this->assertNull($student->getPhone());
        $this->assertNull($student->getBirthDate());
        $this->assertNull($student->getAddress());

        // Définir les champs optionnels
        $student->setPhone('+33987654321');
        $student->setBirthDate(new \DateTime('1995-12-25'));
        $student->setAddress('456 Avenue des Champs');

        $this->assertEquals('+33987654321', $student->getPhone());
        $this->assertEquals('1995-12-25', $student->getBirthDate()->format('Y-m-d'));
        $this->assertEquals('456 Avenue des Champs', $student->getAddress());
    }

    public function testStudentNameLength(): void
    {
        $student = new Student();

        // Noms valides
        $validFirstName = 'Jean-Pierre';
        $validLastName = 'Martin-Dubois';

        $student->setFirstName($validFirstName);
        $student->setLastName($validLastName);

        $this->assertEquals($validFirstName, $student->getFirstName());
        $this->assertEquals($validLastName, $student->getLastName());
    }

    public function testStudentBirthDateTypes(): void
    {
        $student = new Student();

        // Test avec DateTime
        $dateTime = new \DateTime('2001-03-15');
        $student->setBirthDate($dateTime);
        $this->assertInstanceOf(\DateTimeInterface::class, $student->getBirthDate());
        $this->assertEquals('2001-03-15', $student->getBirthDate()->format('Y-m-d'));

        // Test avec DateTimeImmutable
        $dateTimeImmutable = new \DateTimeImmutable('2002-06-20');
        $student->setBirthDate($dateTimeImmutable);
        $this->assertInstanceOf(\DateTimeInterface::class, $student->getBirthDate());
        $this->assertEquals('2002-06-20', $student->getBirthDate()->format('Y-m-d'));
    }

    public function testStudentAddressLength(): void
    {
        $student = new Student();

        // Adresse normale
        $normalAddress = '123 Rue de la République, 75001 Paris';
        $student->setAddress($normalAddress);
        $this->assertEquals($normalAddress, $student->getAddress());

        // Adresse longue
        $longAddress = '123 Rue de la République avec un nom très long qui pourrait poser problème, Appartement 456, Bâtiment C, 75001 Paris, France';
        $student->setAddress($longAddress);
        $this->assertEquals($longAddress, $student->getAddress());
    }

    public function testStudentCanBeCreatedWithMinimalData(): void
    {
        $student = new Student();
        $student->setFirstName('Minimal');
        $student->setLastName('Student');
        $student->setEmail('minimal@example.com');

        // Vérifier que l'étudiant est correctement créé avec le minimum requis
        $this->assertEquals('Minimal', $student->getFirstName());
        $this->assertEquals('Student', $student->getLastName());
        $this->assertEquals('minimal@example.com', $student->getEmail());
        $this->assertNotNull($student->getStudentNumber());
        $this->assertInstanceOf(\DateTimeImmutable::class, $student->getCreatedAt());
    }

    public function testStudentSetStudentNumber(): void
    {
        $student = new Student();
        $customNumber = 'STU20241234';
        
        $student->setStudentNumber($customNumber);
        $this->assertEquals($customNumber, $student->getStudentNumber());
    }
} 