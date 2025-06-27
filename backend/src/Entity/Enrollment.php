<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'enrollments')]
#[ORM\UniqueConstraint(name: 'unique_student_course', columns: ['student_id', 'course_id'])]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['enrollment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Un étudiant doit être assigné à l\'inscription')]
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Un cours doit être assigné à l\'inscription')]
    private ?Course $course = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['enrollment:read'])]
    private ?\DateTimeImmutable $enrollmentDate = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(
        choices: ['Actif', 'Terminé', 'Abandonné', 'En attente'],
        message: 'Le statut doit être l\'un des suivants : {{ choices }}'
    )]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?string $status = 'Actif';

    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 20,
        minMessage: 'La note doit être au minimum {{ limit }}',
        maxMessage: 'La note doit être au maximum {{ limit }}'
    )]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?float $grade = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['enrollment:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['enrollment:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->enrollmentDate = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = 'Actif';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getEnrollmentDate(): ?\DateTimeImmutable
    {
        return $this->enrollmentDate;
    }

    public function setEnrollmentDate(\DateTimeImmutable $enrollmentDate): static
    {
        $this->enrollmentDate = $enrollmentDate;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getGrade(): ?float
    {
        return $this->grade;
    }

    public function setGrade(?float $grade): static
    {
        $this->grade = $grade;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Vérifie si l'inscription est active
     */
    public function isActive(): bool
    {
        return $this->status === 'Actif';
    }

    /**
     * Vérifie si l'inscription est terminée
     */
    public function isCompleted(): bool
    {
        return $this->status === 'Terminé';
    }

    /**
     * Vérifie si l'étudiant a abandonné le cours
     */
    public function isDropped(): bool
    {
        return $this->status === 'Abandonné';
    }

    /**
     * Vérifie si l'inscription est en attente
     */
    public function isPending(): bool
    {
        return $this->status === 'En attente';
    }

    /**
     * Retourne la mention selon la note
     */
    public function getMention(): ?string
    {
        if ($this->grade === null) {
            return null;
        }

        return match (true) {
            $this->grade >= 16 => 'Très bien',
            $this->grade >= 14 => 'Bien',
            $this->grade >= 12 => 'Assez bien',
            $this->grade >= 10 => 'Passable',
            default => 'Insuffisant'
        };
    }

    /**
     * Vérifie si l'étudiant a validé le cours
     */
    public function isPassed(): bool
    {
        return $this->grade !== null && $this->grade >= 10;
    }

    /**
     * Affichage complet de l'inscription
     */
    public function getFullDisplay(): string
    {
        return sprintf(
            '%s inscrit à %s (%s) - Statut: %s%s',
            $this->student ? $this->student->getFullName() : 'Étudiant inconnu',
            $this->course ? $this->course->getName() : 'Cours inconnu',
            $this->course ? $this->course->getCode() : '',
            $this->status,
            $this->grade ? sprintf(' - Note: %.1f/20', $this->grade) : ''
        );
    }

    /**
     * Méthode virtuelle pour obtenir les données de l'étudiant sans référence circulaire
     */
    #[Groups(['enrollment:read'])]
    public function getStudentData(): ?array
    {
        if (!$this->student) {
            return null;
        }
        
        return [
            'id' => $this->student->getId(),
            'firstName' => $this->student->getFirstName(),
            'lastName' => $this->student->getLastName(),
            'fullName' => $this->student->getFullName(),
            'email' => $this->student->getEmail(),
            'studentNumber' => $this->student->getStudentNumber()
        ];
    }

    /**
     * Méthode virtuelle pour obtenir les données du cours sans référence circulaire
     */
    #[Groups(['enrollment:read'])]
    public function getCourseData(): ?array
    {
        if (!$this->course) {
            return null;
        }
        
        return [
            'id' => $this->course->getId(),
            'name' => $this->course->getName(),
            'code' => $this->course->getCode(),
            'credits' => $this->course->getCredits(),
            'semester' => $this->course->getSemester(),
            'year' => $this->course->getYear()
        ];
    }
} 