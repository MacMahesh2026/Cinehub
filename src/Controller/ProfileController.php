<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to view your profile.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if (\is_string($plainPassword) && '' !== $plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Your profile has been updated.');

            return $this->redirectToRoute('app_profile');
        }

        $bookings = $user->getBookings();
        $confirmed = 0;
        $cancelled = 0;

        foreach ($bookings as $booking) {
            if ('CONFIRMED' === $booking->getStatus()) {
                ++$confirmed;
            } elseif ('CANCELLED' === $booking->getStatus()) {
                ++$cancelled;
            }
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form,
            'user' => $user,
            'totalBookings' => $bookings->count(),
            'confirmedBookings' => $confirmed,
            'cancelledBookings' => $cancelled,
        ]);
    }
}
