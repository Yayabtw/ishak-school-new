<?php

namespace App\Tests\Entity;

use App\Entity\Teacher;
use PHPUnit\Framework\TestCase;

class TeacherTest extends TestCase
{
    public function testTeacherCreation(): void
    {
        $teacher = new Teacher();
        $teacher->setFirstName('Jean');
        $teacher->setLastName('Dupont');
        $teacher->setEmail('jean.dupont@example.com');
        $teacher->setPhone('+33123456789');
        $teacher->setSpeciality('Informatique');

        $this->assertEquals('Jean', $teacher->getFirstName());
        $this->assertEquals('Dupont', $teacher->getLastName());
        $this->assertEquals('jean.dupont@example.com', $teacher->getEmail());
        $this->assertEquals('+33123456789', $teacher->getPhone());
        $this->assertEquals('Informatique', $teacher->getSpeciality());
    }

    public function testTeacherFullName(): void
    {
        $teacher = new Teacher();
        $teacher->setFirstName('Marie');
        $teacher->setLastName('Martin');

        $this->assertEquals('Marie Martin', $teacher->getFullName());
    }

    public function testTeacherTimestamps(): void
    {
        $teacher = new Teacher();
        $teacher->setFirstName('Test');
        $teacher->setLastName('User');
        $teacher->setEmail('test@example.com');
        $teacher->setSpeciality('Test');

        // Test que les timestamps sont définis automatiquement
        $teacher->setCreatedAt(new \DateTimeImmutable());
        $teacher->setUpdatedAt(new \DateTimeImmutable());

        $this->assertInstanceOf(\DateTimeImmutable::class, $teacher->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $teacher->getUpdatedAt());
    }

    public function testTeacherEmailValidation(): void
    {
        $teacher = new Teacher();
        $teacher->setEmail('invalid-email');

        // Note: En conditions réelles, la validation se fait via les contraintes Symfony
        // Ici on teste juste que la propriété est correctement définie
        $this->assertEquals('invalid-email', $teacher->getEmail());
    }

    public function testTeacherSpecialityOptions(): void
    {
        $validSpecialities = [
            'Informatique',
            'Mathématiques', 
            'Physique',
            'Chimie',
            'Biologie',
            'Histoire',
            'Géographie',
            'Français',
            'Anglais'
        ];

        $teacher = new Teacher();
        
        foreach ($validSpecialities as $speciality) {
            $teacher->setSpeciality($speciality);
            $this->assertEquals($speciality, $teacher->getSpeciality());
        }
    }

    public function testTeacherCourses(): void
    {
        $teacher = new Teacher();
        
        // Test que la collection de cours est initialisée
        $this->assertCount(0, $teacher->getCourses());
        $this->assertTrue($teacher->getCourses()->isEmpty());
    }

    public function testTeacherRequiredFields(): void
    {
        $teacher = new Teacher();
        
        // Test des champs obligatoires
        $teacher->setFirstName('John');
        $teacher->setLastName('Doe');
        $teacher->setEmail('john.doe@example.com');
        $teacher->setSpeciality('Informatique');

        $this->assertNotEmpty($teacher->getFirstName());
        $this->assertNotEmpty($teacher->getLastName());
        $this->assertNotEmpty($teacher->getEmail());
        $this->assertNotEmpty($teacher->getSpeciality());
    }
} 