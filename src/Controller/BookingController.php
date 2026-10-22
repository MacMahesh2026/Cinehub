<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\BookingSeat;
use App\Entity\Showtime;
use App\Entity\Seat;
use App\Entity\User;
use App\Repository\BookingSeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class BookingController extends AbstractController
{
    #[Route('/showtime/{id}/seats', name: 'showtime_seats', methods: ['GET'])]
    public function seats(
        Showtime $showtime,
        BookingSeatRepository $bookingSeatRepository
    ): Response {
        $screen = $showtime->getScreen();

        $bookedSeatIds = $bookingSeatRepository
            ->findBookedSeatIdsForShowtime($showtime->getId());

        return $this->render('booking/seats.html.twig', [
            'showtime' => $showtime,
            'screen' => $screen,
            'seats' => $screen->getSeats(),
            'bookedSeatIds' => $bookedSeatIds,
        ]);
    }

    #[Route('/showtime/{id}/book', name: 'showtime_book', methods: ['POST'])]
    public function book(
        Showtime $showtime,
        Request $request,
        EntityManagerInterface $entityManager,
        BookingSeatRepository $bookingSeatRepository
    ): Response {
        $seatIds = $request->request->all('seat_ids');

        if (empty($seatIds)) {
            $this->addFlash('error', 'Please select at least one seat.');

            return $this->redirectToRoute('showtime_seats', [
                'id' => $showtime->getId(),
            ]);
        }

        $user = $this->getUser();

        // Find the test user for now.
        // $user = $entityManager
        //     ->getRepository(User::class)
        //     ->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            throw $this->createNotFoundException('Test user not found.');
        }

        $screen = $showtime->getScreen();

        $seats = [];

        foreach ($seatIds as $seatId) {
            $seat = $entityManager
                ->getRepository(Seat::class)
                ->find((int) $seatId);

            if ($seat === null || $seat->getScreen()->getId() !== $screen->getId()) {
                throw $this->createAccessDeniedException(
                    'Invalid seat selected.'
                );
            }

            $seats[] = $seat;
        }

        // Check whether any selected seat is already booked
        foreach ($seats as $seat) {
            $alreadyBooked = $bookingSeatRepository->createQueryBuilder('bs')
                ->join('bs.booking', 'b')
                ->andWhere('bs.seat = :seat')
                ->andWhere('b.showtime = :showtime')
                ->andWhere('b.status = :status')
                ->setParameter('seat', $seat)
                ->setParameter('showtime', $showtime)
                ->setParameter('status', 'CONFIRMED')
                ->getQuery()
                ->getOneOrNullResult();

            if ($alreadyBooked !== null) {
                $this->addFlash(
                    'error',
                    sprintf('Seat %s is already booked.', $seat->getSeatNumber())
                );

                return $this->redirectToRoute('showtime_seats', [
                    'id' => $showtime->getId(),
                ]);
            }
        }

        $price = (float) $showtime->getPrice();
        $totalAmount = $price * count($seats);

        $booking = new Booking();

        $booking->setUser($user);
        $booking->setShowtime($showtime);
        $booking->setBookingReference(
            'CH-' . strtoupper(bin2hex(random_bytes(5)))
        );
        $booking->setBookingDate(new \DateTime());
        $booking->setTotalAmount(number_format($totalAmount, 2, '.', ''));
        $booking->setStatus('CONFIRMED');

        $entityManager->persist($booking);

        foreach ($seats as $seat) {
            $bookingSeat = new BookingSeat();

            $bookingSeat->setBooking($booking);
            $bookingSeat->setSeat($seat);

            $entityManager->persist($bookingSeat);
        }

        $entityManager->flush();

        return $this->redirectToRoute('booking_confirmation', [
            'id' => $booking->getId(),
        ]);
    }

    #[Route('/booking/{id}/confirmation', name: 'booking_confirmation', methods: ['GET'])]
    public function confirmation(
        Booking $booking
    ): Response {
        return $this->render('booking/confirmation.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/my-bookings', name: 'my_bookings', methods: ['GET'])]
    public function myBookings(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException(
                'You must be logged in to view your bookings.'
            );
        }

        $bookings = $user->getBookings();

        return $this->render('booking/my_bookings.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('/booking/{id}', name: 'booking_details', methods: ['GET'])]
    public function details(
        Booking $booking,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException(
                'You must be logged in to view this booking.'
            );
        }

        if ($booking->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException(
                'You are not allowed to view this booking.'
            );
        }

        return $this->render('booking/details.html.twig', [
            'booking' => $booking,
            'cancelToken' => $csrfTokenManager->getToken(
                'cancel_booking_' . $booking->getId()
            )->getValue(),
        ]);
    }

    #[Route('/booking/{id}/cancel', name: 'booking_cancel', methods: ['POST'])]
    public function cancel(
        Booking $booking,
        Request $request,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException(
                'You must be logged in to cancel a booking.'
            );
        }

        if ($booking->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException(
                'You are not allowed to cancel this booking.'
            );
        }

        $token = new CsrfToken(
            'cancel_booking_' . $booking->getId(),
            $request->request->get('_token')
        );

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException(
                'Invalid CSRF token.'
            );
        }

        if ($booking->getStatus() !== 'CONFIRMED') {
            $this->addFlash(
                'error',
                'Only confirmed bookings can be cancelled.'
            );

            return $this->redirectToRoute('booking_details', [
                'id' => $booking->getId(),
            ]);
        }

        $booking->setStatus('CANCELLED');

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Booking cancelled successfully.'
        );

        return $this->redirectToRoute('booking_details', [
            'id' => $booking->getId(),
        ]);
    }
}