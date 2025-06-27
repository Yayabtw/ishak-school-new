<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/courses', name: 'api_courses_')]
class CoursesController extends AbstractController
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
            $courses = $this->entityManager->getRepository(Course::class)->findAll();
            
            $data = $this->serializer->serialize($courses, 'json', [
                'groups' => ['course:read']
            ]);
            
            return new JsonResponse(
                ['success' => true, 'data' => json_decode($data), 'count' => count($courses)],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des cours',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $course = $this->entityManager->getRepository(Course::class)->find($id);
            
            if (!$course) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Cours non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $this->serializer->serialize($course, 'json', [
                'groups' => ['course:read']
            ]);

            return new JsonResponse([
                'success' => true,
                'data' => json_decode($data)
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération du cours',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            // Récupération des données JSON
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Données JSON invalides'
                ], Response::HTTP_BAD_REQUEST);
            }

            $course = new Course();
            $course->setName($data['name'] ?? null);
            $course->setDescription($data['description'] ?? null);
            $course->setCode($data['code'] ?? null);
            $course->setCredits($data['credits'] ?? null);
            $course->setMaxCapacity($data['maxCapacity'] ?? null);
            $course->setSemester($data['semester'] ?? null);
            $course->setYear($data['year'] ?? null);

            // Gestion de l'enseignant
            if (isset($data['teacherId'])) {
                $teacher = $this->entityManager->getRepository(\App\Entity\Teacher::class)->find($data['teacherId']);
                if (!$teacher) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Enseignant non trouvé'
                    ], Response::HTTP_BAD_REQUEST);
                }
                $course->setTeacher($teacher);
            }

            // Validation
            $errors = $this->validator->validate($course);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($course);
            $this->entityManager->flush();

            $data = $this->serializer->serialize($course, 'json', [
                'groups' => ['course:read']
            ]);

            return new JsonResponse([
                'success' => true,
                'message' => 'Cours créé avec succès',
                'data' => json_decode($data)
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la création du cours',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $course = $this->entityManager->getRepository(Course::class)->find($id);
            
            if (!$course) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Cours non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            // Récupération des données JSON
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Données JSON invalides'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Mise à jour des champs si fournis
            if (isset($data['name'])) {
                $course->setName($data['name']);
            }
            if (isset($data['description'])) {
                $course->setDescription($data['description']);
            }
            if (isset($data['code'])) {
                $course->setCode($data['code']);
            }
            if (isset($data['credits'])) {
                $course->setCredits($data['credits']);
            }
            if (isset($data['maxCapacity'])) {
                $course->setMaxCapacity($data['maxCapacity']);
            }
            if (isset($data['semester'])) {
                $course->setSemester($data['semester']);
            }
            if (isset($data['year'])) {
                $course->setYear($data['year']);
            }

            // Gestion de l'enseignant
            if (isset($data['teacherId'])) {
                $teacher = $this->entityManager->getRepository(\App\Entity\Teacher::class)->find($data['teacherId']);
                if (!$teacher) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Enseignant non trouvé'
                    ], Response::HTTP_BAD_REQUEST);
                }
                $course->setTeacher($teacher);
            }

            // Validation
            $errors = $this->validator->validate($course);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            $data = $this->serializer->serialize($course, 'json', [
                'groups' => ['course:read']
            ]);

            return new JsonResponse([
                'success' => true,
                'message' => 'Cours modifié avec succès',
                'data' => json_decode($data)
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la modification du cours',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $course = $this->entityManager->getRepository(Course::class)->find($id);
            
            if (!$course) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Cours non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($course);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Cours supprimé avec succès'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la suppression du cours',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

