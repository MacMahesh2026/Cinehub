---
doc_id: SUP-003
title: System Capabilities and Limitations
subtitle: What the current CineHub application does and does not do
version: 1.0
effective_date: 2026-11-03
last_reviewed: 2026-11-03
audience: Customer and support
category: Support
owner: CineHub Product
confidentiality: Public
output: public/documents/support/SUP-003-system-capabilities-limitations.pdf
---

## 1. Purpose

This document describes the current CineHub application so that customers and support teams have a single, accurate view of its capabilities and limits. It is also intended to prevent the support assistant from promising features that do not exist.

## 2. What CineHub currently does

CineHub currently provides:

- a public home page listing the movies in the catalogue;
- a public movie details page with movie information and stored showtimes;
- a public seat selection page for a chosen showtime;
- account registration through the website;
- account registration through POST /api/register;
- email and password login and logout;
- creation of a booking with the status CONFIRMED;
- a booking reference in the format CH- followed by 10 letters or numbers;
- an on-screen booking confirmation page;
- a My Bookings page for the signed-in user;
- a booking details page for the signed-in user;
- cancellation of a CONFIRMED booking by the signed-in user; and
- basic booking abuse limits: 10 registered accounts in total and 3 confirmed bookings per user.

## 3. What CineHub does not currently do

CineHub does not currently provide:

- online payment or a payment gateway;
- card, bank, UPI, or wallet collection;
- online refunds, credit notes, or refund tracking;
- invoices or tax documents;
- booking confirmation emails or SMS messages;
- email verification or OTP verification;
- self-service password reset;
- self-service account deletion;
- an admin panel, staff dashboard, or booking management console;
- seat holds or temporary reservations;
- a waiting list for full shows or full registration;
- search, genre filters, city filters, or date filters;
- filtering of past showtimes;
- seat-type pricing differences;
- booking transfers or seat changes after confirmation;
- booking restoration after cancellation;
- notifications, reminders, or calendar invites;
- live chat or WhatsApp support;
- multi-language support; or
- third-party analytics or advertising tools.

If a customer asks for one of these features, the correct response is to explain that it is not available in the current version and offer the relevant support route. Do not describe it as available.

## 4. Current limits

| Limit | Current value | Notes |
| --- | --- | --- |
| Registered accounts | 10 total | Applies to website and API registration. Registration closes when full. |
| Confirmed bookings per user | 3 | Only CONFIRMED bookings count. Cancelled bookings free a slot. |
| Password length | Minimum 8 characters | Stored as a one-way hash. |
| Email addresses per account | 1 | Email is the login identifier and must be unique. |
| Seats per booking | No separate application limit | Limited by seats available for the showtime. |
| Seat price | Per showtime | One price applies to all selected seats in the current flow. |
| Currency | INR | Indian rupees. |
| Booking reference | CH- plus 10 letters or numbers | Example: CH-1A2B3C4D5E. |
| Account roles | ROLE_USER | No admin role or admin interface is exposed. |

The 10-account limit and 3-booking limit are application rules. Support cannot override them through the customer interface.

## 5. How data is displayed

- Movies are listed from the database without search or filtering.
- Movie details and showtimes come from stored data.
- Showtimes are not filtered by the current date or time, so past showtimes may remain visible.
- Seat maps come from the seats configured for the selected screen.
- Seat availability is based on confirmed bookings for that showtime.
- The home page and movie details page are public. Booking creation, My Bookings, booking details, and cancellation require the relevant account access.
- Booking details and cancellation check that the booking belongs to the signed-in user.
- The booking confirmation page is reached by booking ID. Support should avoid sharing confirmation links between users as a security precaution.

## 6. Known behavioural edge cases

### No temporary seat hold

A seat is not locked while a customer is choosing. If two customers submit at almost the same time, availability is checked at booking time, but the application does not provide a temporary hold or a full concurrency lock.

### Past showtimes remain visible

The application does not hide showtimes whose start time has passed. A customer can select a past showtime if it is still stored in the database. Support should explain this and escalate content or catalogue issues to the product owner.

### Cancellation has no deadline

Any CONFIRMED booking can be cancelled, including after the showtime. The application does not check the showtime when cancelling.

### Cancelled bookings stay in history

Cancelled bookings remain in My Bookings with the status CANCELLED. They cannot be reopened or deleted by the customer.

### No confirmation email

The confirmation page is the only automatic confirmation. Customers who close the page can still find the booking in My Bookings while signed in.

### Registration capacity

When 10 accounts exist, registration is closed for everyone, including new customers. There is no waitlist, no automatic cleanup, and no self-service way for a customer to free a slot.

### Booking capacity

A customer with 3 confirmed bookings must cancel one before making another. Cancelled bookings do not count toward the limit, so cancellation is the supported way to free capacity.

### Account recovery

There is no self-service password reset or account recovery flow. Support must follow its identity-verification procedure before assisting.

### Account deletion

There is no self-service deletion flow. Requests go through the privacy contact and may require manual handling.

### Payment handling

No online payment is collected. The booking is confirmed without payment. Any money handled by a theatre is outside the application and must be resolved with that theatre.

## 7. Support-safe answers for common questions

| Customer says | Safe answer |
| --- | --- |
| "Can I pay online?" | "CineHub does not currently provide online payment. The booking is created without an online payment step." |
| "Where is my refund?" | "CineHub does not collect online payments, so it does not process online refunds. If you paid a theatre directly, please contact that theatre." |
| "Why did I not get an email?" | "CineHub does not currently send confirmation emails or SMS messages. Your booking is shown on screen and in My Bookings." |
| "I need a fourth booking." | "The current limit is 3 confirmed bookings per user. Cancel one confirmed booking to free a slot." |
| "Why can I not register?" | "CineHub currently allows up to 10 registered accounts. When the limit is full, registration is closed." |
| "Can you reset my password?" | "CineHub does not currently provide self-service password reset. Support can explain the available account-help process." |
| "Can I hold this seat?" | "CineHub does not currently provide temporary seat holds. A seat is reserved only when the booking is confirmed." |
| "Can I change my seats?" | "Seat changes are not currently supported. You can cancel the confirmed booking and create a new one, subject to availability and limits." |
| "I see a past showtime." | "CineHub currently displays stored showtimes without filtering out past dates. Check the date and time carefully before booking." |

## 8. What support must not promise

Do not tell a customer that CineHub can:

- take an online payment;
- issue an online refund;
- send a confirmation email or SMS;
- hold seats;
- add a fourth confirmed booking;
- create an account after the limit is full;
- reset a password instantly;
- restore a cancelled booking;
- guarantee theatre entry; or
- change a booking's seats or owner.

If a customer needs something outside these capabilities, explain the limitation and escalate to the appropriate owner rather than inventing a workaround.

## 9. Change control

This document describes the application as of the effective date. When a feature is added or a limit changes, update this document together with the relevant policy and FAQ before announcing the change to customers. The version and effective date are shown at the top of this document.

## 10. Contact

- Product owner: product@cinehub.example
- Support: support@cinehub.example
- Privacy: privacy@cinehub.example
- Help Centre: /help
