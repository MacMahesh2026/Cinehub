<?php

namespace App\Message;

/**
 * Dispatched after a booking is saved, separately from the confirmation email
 * so a WhatsApp failure never retries (and therefore never duplicates) the
 * email.
 */
final readonly class BookingWhatsAppMessage
{
    public function __construct(
        public int $bookingId,
    ) {
    }
}
