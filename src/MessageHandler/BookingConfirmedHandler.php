<?php

namespace App\MessageHandler;

use App\Entity\Booking;
use App\Message\BookingConfirmedMessage;
use App\Repository\BookingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Sends the booking confirmation email (via the configured mailer).
 *
 * Runs on the async transport, so a slow or unavailable provider never delays
 * or breaks the customer's booking request. WhatsApp is handled by a separate
 * message so a WhatsApp failure can never cause a duplicate email.
 */
#[AsMessageHandler]
final readonly class BookingConfirmedHandler
{
    public function __construct(
        private BookingRepository $bookings,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        #[Autowire('%env(MAILER_FROM)%')]
        private string $from,
    ) {
    }

    public function __invoke(BookingConfirmedMessage $message): void
    {
        $booking = $this->bookings->find($message->bookingId);

        if (null === $booking) {
            $this->logger->warning('Booking {id} not found; skipping confirmation.', [
                'id' => $message->bookingId,
            ]);

            return;
        }

        $this->sendEmail($booking);
    }

    private function sendEmail(Booking $booking): void
    {
        $user = $booking->getUser();

        if (null === $user || null === $user->getEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from($this->from)
            ->to($user->getEmail())
            ->subject(sprintf('Your CineHub booking %s is confirmed', $booking->getBookingReference()))
            ->htmlTemplate('emails/booking_confirmed.html.twig')
            ->textTemplate('emails/booking_confirmed.txt.twig')
            ->context(['booking' => $booking]);

        $this->mailer->send($email);
    }
}
