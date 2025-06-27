<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Enrollment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/enrollments', name: 'api_enrollments_')]
class EnrollmentsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $enrollments = $this->entityManager->getRepository(Enrollment::class)->findAll();
        return $this->json([
            'success' => true,
            'data' => $enrollments
        ], Response::HTTP_OK, [], ['groups' => ['enrollment:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $enrollment = $this->entityManager->getRepository(Enrollment::class)->find($id);
        
        if (!$enrollment) {
            return $this->json([
                'success' => false,
                'message' => 'Inscription non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'success' => true,
            'data' => $enrollment
        ], Response::HTTP_OK, [], ['groups' => ['enrollment:read']]);
    }
    
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $enrollment = new Enrollment();
            
            // Récupérer l'étudiant et le cours
            if (isset($data['studentId'])) {
                $student = $this->entityManager->getRepository(\App\Entity\Student::class)->find($data['studentId']);
                if ($student) {
                    $enrollment->setStudent($student);
                }
            }
            
            if (isset($data['courseId'])) {
                $course = $this->entityManager->getRepository(\App\Entity\Course::class)->find($data['courseId']);
                if ($course) {
                    $enrollment->setCourse($course);
                }
            }
            
            // Autres champs
            if (isset($data['status'])) {
                $enrollment->setStatus($data['status']);
            }
            if (isset($data['grade'])) {
                $enrollment->setGrade((float) $data['grade']);
            }
            if (isset($data['notes'])) {
                $enrollment->setNotes($data['notes']);
            }
            if (isset($data['enrollmentDate'])) {
                $enrollment->setEnrollmentDate(new \DateTimeImmutable($data['enrollmentDate']));
            }

            $errors = $this->validator->validate($enrollment);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($enrollment);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Inscription créée avec succès',
                'data' => $enrollment
            ], Response::HTTP_CREATED, [], ['groups' => ['enrollment:read']]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $enrollment = $this->entityManager->getRepository(Enrollment::class)->find($id);
            
            if (!$enrollment) {
                return $this->json([
                    'success' => false,
                    'message' => 'Inscription non trouvée'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $data = json_decode($request->getContent(), true);
            
            // Mettre à jour l'étudiant si fourni
            if (isset($data['studentId'])) {
                $student = $this->entityManager->getRepository(\App\Entity\Student::class)->find($data['studentId']);
                if ($student) {
                    $enrollment->setStudent($student);
                }
            }
            
            // Mettre à jour le cours si fourni
            if (isset($data['courseId'])) {
                $course = $this->entityManager->getRepository(\App\Entity\Course::class)->find($data['courseId']);
                if ($course) {
                    $enrollment->setCourse($course);
                }
            }
            
            // Autres champs
            if (isset($data['status'])) {
                $enrollment->setStatus($data['status']);
            }
            if (isset($data['grade'])) {
                $enrollment->setGrade($data['grade'] ? (float) $data['grade'] : null);
            }
            if (isset($data['notes'])) {
                $enrollment->setNotes($data['notes']);
            }
            if (isset($data['enrollmentDate'])) {
                $enrollment->setEnrollmentDate(new \DateTimeImmutable($data['enrollmentDate']));
            }

            $errors = $this->validator->validate($enrollment);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Inscription modifiée avec succès',
                'data' => $enrollment
            ], Response::HTTP_OK, [], ['groups' => ['enrollment:read']]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la modification : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $enrollment = $this->entityManager->getRepository(Enrollment::class)->find($id);
            
            if (!$enrollment) {
                return $this->json([
                    'success' => false,
                    'message' => 'Inscription non trouvée'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $this->entityManager->remove($enrollment);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Inscription supprimée avec succès'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}