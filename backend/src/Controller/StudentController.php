<?php

namespace App\Controller;

use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/students', name: 'api_students_')]
class StudentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            $students = $this->entityManager->getRepository(Student::class)->findAll();
            
            $data = $this->serializer->serialize($students, 'json', [
                'groups' => ['student:read']
            ]);
            
            return new JsonResponse([
                'success' => true,
                'data' => json_decode($data),
                'count' => count($students)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des étudiants',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $student = $this->entityManager->getRepository(Student::class)->find($id);
            
            if (!$student) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Étudiant non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $data = $this->serializer->serialize($student, 'json', [
                'groups' => ['student:read']
            ]);
            
            return new JsonResponse([
                'success' => true,
                'data' => json_decode($data)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'étudiant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Données JSON invalides'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $student = new Student();
            
            // Hydratation manuelle
            if (isset($data['firstName'])) $student->setFirstName($data['firstName']);
            if (isset($data['lastName'])) $student->setLastName($data['lastName']);
            if (isset($data['email'])) $student->setEmail($data['email']);
            if (isset($data['phone'])) $student->setPhone($data['phone']);
            if (isset($data['address'])) $student->setAddress($data['address']);
            if (isset($data['birthDate'])) {
                $student->setBirthDate(new \DateTime($data['birthDate']));
            }
            
            // Validation
            $errors = $this->validator->validate($student);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $this->entityManager->persist($student);
            $this->entityManager->flush();
            
            $responseData = $this->serializer->serialize($student, 'json', [
                'groups' => ['student:read']
            ]);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Étudiant créé avec succès',
                'data' => json_decode($responseData)
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'étudiant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $student = $this->entityManager->getRepository(Student::class)->find($id);
            
            if (!$student) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Étudiant non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Données JSON invalides'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Mise à jour des champs
            if (isset($data['firstName'])) $student->setFirstName($data['firstName']);
            if (isset($data['lastName'])) $student->setLastName($data['lastName']);
            if (isset($data['email'])) $student->setEmail($data['email']);
            if (isset($data['phone'])) $student->setPhone($data['phone']);
            if (isset($data['address'])) $student->setAddress($data['address']);
            if (isset($data['birthDate'])) {
                $student->setBirthDate(new \DateTime($data['birthDate']));
            }
            
            // Validation
            $errors = $this->validator->validate($student);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $this->entityManager->flush();
            
            $responseData = $this->serializer->serialize($student, 'json', [
                'groups' => ['student:read']
            ]);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Étudiant mis à jour avec succès',
                'data' => json_decode($responseData)
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'étudiant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $student = $this->entityManager->getRepository(Student::class)->find($id);
            
            if (!$student) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Étudiant non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Les inscriptions seront supprimées automatiquement (cascade remove)
            $this->entityManager->remove($student);
            $this->entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Étudiant supprimé avec succès'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'étudiant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/enrollments', name: 'enrollments', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getEnrollments(int $id): JsonResponse
    {
        try {
            $student = $this->entityManager->getRepository(Student::class)->find($id);
            
            if (!$student) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Étudiant non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $enrollments = $student->getEnrollments();
            
            $data = $this->serializer->serialize($enrollments, 'json', [
                'groups' => ['enrollment:read']
            ]);
            
            return new JsonResponse([
                'success' => true,
                'data' => json_decode($data),
                'count' => $enrollments->count()
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des inscriptions',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 