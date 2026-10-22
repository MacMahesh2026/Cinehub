<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // Get JSON request body
        $data = json_decode($request->getContent(), true);

        // Check if JSON is valid
        if (!is_array($data)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid JSON request.'
            ], 400);
        }

        // Get request values
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        // Basic validation
        if ($name === '') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Name is required.'
            ], 400);
        }

        if ($email === '') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Email is required.'
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Please provide a valid email address.'
            ], 400);
        }

        if ($password === '') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Password is required.'
            ], 400);
        }

        if (strlen($password) < 8) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Password must be at least 8 characters.'
            ], 400);
        }

        // Check if email already exists
        $existingUser = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser !== null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Email is already registered.'
            ], 409);
        }

        // Create user
        $user = new User();

        $user->setName($name);
        $user->setEmail($email);

        // Hash password
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setPassword($hashedPassword);

        // ROLE_USER is automatically added by getRoles(),
        // so we don't need to explicitly set it here.

        // Save user
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }
}