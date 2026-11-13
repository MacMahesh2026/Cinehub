<?php

namespace App\MessageHandler;

use App\Entity\Booking;
use App\Message\BookingConfirmedMessage;
use App\Notifier\WhatsAppClient;
use App\Repository\BookingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Sends the booking confirmation email (via the configured mailer) and, when
 * the customer has a phone number on file, a WhatsApp template message.
 *
 * Runs on the async transport, so a slow or unavailable provider never delays
 * or breaks the customer's booking request.
 */
#[AsMessageHandler]
final readonly class BookingConfirmedHandler
{
    public function __construct(
        private BookingRepository $bookings,
        private MailerInterface $mailer,
        private WhatsAppClient $whatsApp,
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
        $this->sendWhatsApp($booking);
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
            ->context(['booking' => $booking]);

        $this->mailer->send($email);
    }

    private function sendWhatsApp(Booking $booking): void
    {
        $user = $booking->getUser();

        if (null === $user || null === $user->getPhone()) {
            return;
        }

        // Order must match the {{1}}, {{2}}, ... placeholders of the approved
        // "booking_confirmation" utility template in Meta's WhatsApp Manager.
        $this->whatsApp->sendBookingConfirmation($user->getPhone(), [
            $user->getName() ?? 'there',
            $booking->getBookingReference() ?? '',
            $booking->getShowtime()?->getMovie()?->getTitle() ?? '',
            $booking->getShowtime()?->getStartTime()?->format('d M Y, h:i A') ?? '',
        ]);
    }
}
