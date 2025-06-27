<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'courses')]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['course:read', 'enrollment:read', 'teacher:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 200)]
    #[Assert\NotBlank(message: 'Le nom du cours est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 200,
        minMessage: 'Le nom du cours doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom du cours ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Groups(['course:read', 'course:write', 'enrollment:read', 'teacher:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    #[Assert\NotBlank(message: 'Le code du cours est obligatoire')]
    #[Assert\Regex(
        pattern: '/^[A-Z]{2,4}[0-9]{3,4}$/',
        message: 'Le code doit être au format : 2-4 lettres majuscules suivies de 3-4 chiffres (ex: MATH101)'
    )]
    #[Groups(['course:read', 'course:write', 'enrollment:read'])]
    private ?string $code = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'Le nombre de crédits est obligatoire')]
    #[Assert\Positive(message: 'Le nombre de crédits doit être positif')]
    #[Assert\LessThanOrEqual(10, message: 'Le nombre de crédits ne peut pas dépasser 10')]
    #[Groups(['course:read', 'course:write', 'enrollment:read'])]
    private ?int $credits = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive(message: 'La capacité maximale doit être positive')]
    #[Groups(['course:read', 'course:write'])]
    private ?int $maxCapacity = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(message: 'Le semestre est obligatoire')]
    #[Assert\Choice(
        choices: ['Automne', 'Hiver', 'Printemps', 'Été'],
        message: 'Le semestre doit être l\'un des suivants : {{ choices }}'
    )]
    #[Groups(['course:read', 'course:write', 'enrollment:read'])]
    private ?string $semester = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'L\'année est obligatoire')]
    #[Assert\Range(
        min: 2020,
        max: 2030,
        notInRangeMessage: 'L\'année doit être entre {{ min }} et {{ max }}'
    )]
    #[Groups(['course:read', 'course:write', 'enrollment:read'])]
    private ?int $year = null;

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Un enseignant doit être assigné au cours')]
    #[Groups(['course:read', 'course:write'])]
    private ?Teacher $teacher = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['course:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['course:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Enrollment>
     */
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Enrollment::class, cascade: ['persist', 'remove'])]
    private Collection $enrollments;

    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->year = (int) date('Y'); // Année courante par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtoupper($code);
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): static
    {
        $this->credits = $credits;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getMaxCapacity(): ?int
    {
        return $this->maxCapacity;
    }

    public function setMaxCapacity(?int $maxCapacity): static
    {
        $this->maxCapacity = $maxCapacity;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getSemester(): ?string
    {
        return $this->semester;
    }

    public function setSemester(string $semester): static
    {
        $this->semester = $semester;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): static
    {
        $this->teacher = $teacher;
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
     * @return Collection<int, Enrollment>
     */
    public function getEnrollments(): Collection
    {
        return $this->enrollments;
    }

    public function addEnrollment(Enrollment $enrollment): static
    {
        if (!$this->enrollments->contains($enrollment)) {
            $this->enrollments->add($enrollment);
            $enrollment->setCourse($this);
        }

        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): static
    {
        if ($this->enrollments->removeElement($enrollment)) {
            // Set the owning side to null (unless already changed)
            if ($enrollment->getCourse() === $this) {
                $enrollment->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * Récupère tous les étudiants inscrits au cours
     */
    public function getStudents(): array
    {
        $students = [];
        foreach ($this->enrollments as $enrollment) {
            $students[] = $enrollment->getStudent();
        }
        return $students;
    }

    /**
     * Vérifie si le cours a atteint sa capacité maximale
     */
    public function isFull(): bool
    {
        if (!$this->maxCapacity) {
            return false;
        }
        
        return $this->enrollments->count() >= $this->maxCapacity;
    }

    /**
     * Récupère le nombre d'inscrits
     */
    public function getEnrollmentCount(): int
    {
        return $this->enrollments->count();
    }

    /**
     * Affichage du cours complet
     */
    public function getFullDisplay(): string
    {
        return sprintf(
            '%s - %s (%s %d) - %s',
            $this->code,
            $this->name,
            $this->semester,
            $this->year,
            $this->teacher ? $this->teacher->getFullName() : 'Aucun enseignant'
        );
    }
} 