<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\Student;
use App\Entity\Teacher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        // Créer les enseignants
        $teachers = [];
        for ($i = 0; $i < 5; $i++) {
            $teacher = new Teacher();
            $teacher->setFirstName($faker->firstName())
                   ->setLastName($faker->lastName())
                   ->setEmail($faker->unique()->email())
                   ->setPhone($faker->phoneNumber())
                   ->setSpeciality($faker->randomElement([
                       'Mathématiques', 
                       'Informatique', 
                       'Physique', 
                       'Littérature', 
                       'Histoire',
                       'Chimie',
                       'Biologie',
                       'Économie'
                   ]));
            
            $manager->persist($teacher);
            $teachers[] = $teacher;
        }
        
        // Créer les étudiants
        $students = [];
        for ($i = 0; $i < 15; $i++) {
            $student = new Student();
            $student->setFirstName($faker->firstName())
                   ->setLastName($faker->lastName())
                   ->setEmail($faker->unique()->email())
                   ->setPhone($faker->phoneNumber())
                   ->setBirthDate($faker->dateTimeBetween('-25 years', '-18 years'))
                   ->setAddress($faker->address());
            
            $manager->persist($student);
            $students[] = $student;
        }
        
        // Créer les cours
        $courses = [];
        $courseData = [
            ['name' => 'Algorithmique et Structures de Données', 'code' => 'INFO101', 'credits' => 6, 'speciality' => 'Informatique'],
            ['name' => 'Programmation Orientée Objet', 'code' => 'INFO201', 'credits' => 5, 'speciality' => 'Informatique'],
            ['name' => 'Base de Données', 'code' => 'INFO301', 'credits' => 4, 'speciality' => 'Informatique'],
            ['name' => 'Analyse Mathématique', 'code' => 'MATH101', 'credits' => 7, 'speciality' => 'Mathématiques'],
            ['name' => 'Algèbre Linéaire', 'code' => 'MATH201', 'credits' => 6, 'speciality' => 'Mathématiques'],
            ['name' => 'Physique Générale', 'code' => 'PHYS101', 'credits' => 5, 'speciality' => 'Physique'],
            ['name' => 'Thermodynamique', 'code' => 'PHYS201', 'credits' => 4, 'speciality' => 'Physique'],
            ['name' => 'Littérature Française', 'code' => 'LITT101', 'credits' => 3, 'speciality' => 'Littérature'],
            ['name' => 'Histoire Contemporaine', 'code' => 'HIST101', 'credits' => 4, 'speciality' => 'Histoire'],
            ['name' => 'Microéconomie', 'code' => 'ECON101', 'credits' => 5, 'speciality' => 'Économie']
        ];
        
        foreach ($courseData as $data) {
            $course = new Course();
            
            // Trouver un enseignant avec la bonne spécialité ou prendre un au hasard
            $teacher = $this->findTeacherBySpeciality($teachers, $data['speciality']) 
                      ?? $faker->randomElement($teachers);
            
            $course->setName($data['name'])
                   ->setCode($data['code'])
                   ->setCredits($data['credits'])
                   ->setDescription($faker->text(200))
                   ->setMaxCapacity($faker->numberBetween(20, 50))
                   ->setSemester($faker->randomElement(['Automne', 'Hiver', 'Printemps', 'Été']))
                   ->setYear(2024)
                   ->setTeacher($teacher);
            
            $manager->persist($course);
            $courses[] = $course;
        }
        
        // Créer les inscriptions
        $enrollments = [];
        for ($i = 0; $i < 30; $i++) {
            $student = $faker->randomElement($students);
            $course = $faker->randomElement($courses);
            
            // Vérifier qu'il n'y a pas déjà d'inscription pour cette combinaison
            if (!$this->hasEnrollment($enrollments, $student, $course)) {
                $enrollment = new Enrollment();
                $enrollment->setStudent($student)
                          ->setCourse($course)
                          ->setStatus($faker->randomElement(['Actif', 'Terminé', 'Abandonné', 'En attente']))
                          ->setEnrollmentDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', 'now')));
                
                // Ajouter une note si le statut est "Completed"
                if ($enrollment->getStatus() === 'Completed') {
                    $enrollment->setGrade($faker->randomFloat(1, 8, 20));
                }
                
                // Ajouter parfois des notes
                if ($faker->boolean(30)) {
                    $enrollment->setNotes($faker->sentence());
                }
                
                $manager->persist($enrollment);
                $enrollments[] = $enrollment;
            }
        }
        
        $manager->flush();
        
        echo "Fixtures chargées avec succès !\n";
        echo "- " . count($teachers) . " enseignants créés\n";
        echo "- " . count($students) . " étudiants créés\n";
        echo "- " . count($courses) . " cours créés\n";
        echo "- " . count($enrollments) . " inscriptions créées\n";
    }
    
    /**
     * Trouve un enseignant par spécialité
     */
    private function findTeacherBySpeciality(array $teachers, string $speciality): ?Teacher
    {
        foreach ($teachers as $teacher) {
            if ($teacher->getSpeciality() === $speciality) {
                return $teacher;
            }
        }
        return null;
    }
    
    /**
     * Vérifie si une inscription existe déjà
     */
    private function hasEnrollment(array $enrollments, Student $student, Course $course): bool
    {
        foreach ($enrollments as $enrollment) {
            if ($enrollment->getStudent() === $student && $enrollment->getCourse() === $course) {
                return true;
            }
        }
        return false;
    }
} 