<?php

namespace App\Controller;

use App\Entity\Teacher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/teachers', name: 'api_teachers_')]
class TeacherController extends AbstractController
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
            $teachers = $this->entityManager->getRepository(Teacher::class)->findAll();
            
            $data = $this->serializer->serialize($teachers, 'json', [
                'groups' => ['teacher:read']
            ]);
            
            return new JsonResponse(
                ['success' => true, 'data' => json_decode($data), 'count' => count($teachers)],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des enseignants',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $teacher = $this->entityManager->getRepository(Teacher::class)->find($id);
            
            if (!$teacher) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Enseignant non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $data = $this->serializer->serialize($teacher, 'json', [
                'groups' => ['teacher:read']
            ]);
            
            return new JsonResponse([
                'success' => true,
                'data' => json_decode($data)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'enseignant',
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
            
            $teacher = new Teacher();
            
            // Hydratation manuelle pour plus de contrôle
            if (isset($data['firstName'])) $teacher->setFirstName($data['firstName']);
            if (isset($data['lastName'])) $teacher->setLastName($data['lastName']);
            if (isset($data['email'])) $teacher->setEmail($data['email']);
            if (isset($data['phone'])) $teacher->setPhone($data['phone']);
            if (isset($data['speciality'])) $teacher->setSpeciality($data['speciality']);
            
            // Validation
            $errors = $this->validator->validate($teacher);
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
            
            $this->entityManager->persist($teacher);
            $this->entityManager->flush();
            
            $responseData = $this->serializer->serialize($teacher, 'json', [
                'groups' => ['teacher:read']
            ]);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Enseignant créé avec succès',
                'data' => json_decode($responseData)
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'enseignant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $teacher = $this->entityManager->getRepository(Teacher::class)->find($id);
            
            if (!$teacher) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Enseignant non trouvé'
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
            if (isset($data['firstName'])) $teacher->setFirstName($data['firstName']);
            if (isset($data['lastName'])) $teacher->setLastName($data['lastName']);
            if (isset($data['email'])) $teacher->setEmail($data['email']);
            if (isset($data['phone'])) $teacher->setPhone($data['phone']);
            if (isset($data['speciality'])) $teacher->setSpeciality($data['speciality']);
            
            // Validation
            $errors = $this->validator->validate($teacher);
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
            
            $responseData = $this->serializer->serialize($teacher, 'json', [
                'groups' => ['teacher:read']
            ]);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Enseignant mis à jour avec succès',
                'data' => json_decode($responseData)
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'enseignant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $teacher = $this->entityManager->getRepository(Teacher::class)->find($id);
            
            if (!$teacher) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Enseignant non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Vérifier s'il y a des cours assignés
            if ($teacher->getCourses()->count() > 0) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Impossible de supprimer cet enseignant car il a des cours assignés'
                ], Response::HTTP_CONFLICT);
            }
            
            $this->entityManager->remove($teacher);
            $this->entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Enseignant supprimé avec succès'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'enseignant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/courses', name: 'courses', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getCourses(int $id): JsonResponse
    {
        try {
            $teacher = $this->entityManager->getRepository(Teacher::class)->find($id);
            
            if (!$teacher) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Enseignant non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $courses = $teacher->getCourses();
            
            $data = $this->serializer->serialize($courses, 'json', [
                'groups' => ['course:read']
            ]);
            
            return new JsonResponse([
                'success' => true,
                'data' => json_decode($data),
                'count' => $courses->count()
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des cours',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 