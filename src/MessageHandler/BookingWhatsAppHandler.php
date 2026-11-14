<?php

namespace App\MessageHandler;

use App\Entity\Booking;
use App\Message\BookingWhatsAppMessage;
use App\Notifier\WhatsAppClient;
use App\Repository\BookingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Sends the booking confirmation as a WhatsApp template message.
 *
 * Kept on its own message (instead of running next to the email) so WhatsApp
 * retries and failures are isolated from email delivery.
 */
#[AsMessageHandler]
final readonly class BookingWhatsAppHandler
{
    public function __construct(
        private BookingRepository $bookings,
        private WhatsAppClient $whatsApp,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(BookingWhatsAppMessage $message): void
    {
        $booking = $this->bookings->find($message->bookingId);

        if (null === $booking) {
            $this->logger->warning('Booking {id} not found; skipping WhatsApp confirmation.', [
                'id' => $message->bookingId,
            ]);

            return;
        }

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
