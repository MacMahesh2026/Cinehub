<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $error = null;
        $success = null;

        if ($request->isMethod('POST')) {
            $name = trim($request->request->get('name', ''));
            $email = strtolower(trim($request->request->get('email', '')));
            $password = $request->request->get('password', '');

            if ($name === '') {
                $error = 'Name is required.';
            } elseif ($email === '') {
                $error = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please provide a valid email address.';
            } elseif ($password === '') {
                $error = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } else {
                $existingUser = $entityManager
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if ($existingUser !== null) {
                    $error = 'Email is already registered.';
                } else {
                    $user = new User();
                    $user->setName($name);
                    $user->setEmail($email);
                    $user->setPassword($passwordHasher->hashPassword($user, $password));
                    $user->setRoles(['ROLE_USER']);

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Registration successful! Please login.');
                    return $this->redirectToRoute('app_login');
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'error' => $error,
            'success' => $success,
        ]);
    }
}
