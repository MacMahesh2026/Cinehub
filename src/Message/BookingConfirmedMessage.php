<?php

namespace App\Message;

/**
 * Dispatched after a booking is saved. Carries only the booking id so the
 * worker loads fresh data (the message may be handled in a different process,
 * possibly after the request has ended).
 */
final readonly class BookingConfirmedMessage
{
    public function __construct(
        public int $bookingId,
    ) {
    }
}
