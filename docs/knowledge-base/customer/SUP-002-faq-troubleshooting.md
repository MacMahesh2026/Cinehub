---
doc_id: SUP-002
title: FAQ and Troubleshooting Guide
subtitle: Answers to common CineHub account, booking, cancellation, and privacy questions
version: 1.0
effective_date: 2026-11-03
last_reviewed: 2026-11-03
audience: Customer
category: Support
owner: CineHub Support
confidentiality: Public
output: public/documents/support/SUP-002-faq-troubleshooting.pdf
---

## Accounts and registration

### How do I create a CineHub account?

Open the Register page and enter your name, email address, and a password of at least 8 characters. If registration is open, CineHub creates the account and you can sign in with the same email address and password.

### Why is registration closed?

CineHub currently allows a maximum of 10 registered accounts. When 10 accounts already exist, new registration is closed and the form shows: "System allows up to 10 users only. Registration is closed."

There is no waiting list in the application. Contact support at support@cinehub.example if you need help.

### Why does CineHub say my email is already registered?

Each email address can be used for only one account. The message "Email is already registered" means an account already exists for that address. Sign in instead, or contact support if you cannot access the account.

### Why was my password rejected?

Passwords must be at least 8 characters. CineHub does not currently require a number, symbol, or capital letter, but a longer unique password is safer.

### Can I change my email address?

Changing the email address is not currently a self-service feature. Contact support and be prepared to verify your identity.

### I forgot my password. What can I do?

CineHub does not currently provide a self-service password reset. Contact support at support@cinehub.example. Support may ask you to verify your identity before helping with account access.

### Can I delete my account?

Account deletion is not currently available as a self-service feature. Email privacy@cinehub.example from the address registered on the account and follow the verification instructions. Some records may be retained for legal, fraud-prevention, or dispute purposes.

### Can I create a second account?

No. Each person should use one account, and each email address can be used only once. Creating duplicate accounts to bypass limits is prohibited and may lead to suspension.

### Does CineHub verify my email address?

Not currently. CineHub does not send a verification email during registration. Make sure the address you enter is correct because it is your login identifier and support contact address.

### Does the API have the same registration rules?

Yes. POST /api/register applies the same name, email, password, duplicate-email, and 10-account rules. If the user limit is full, it returns HTTP 403 with the registration-closed message.

## Booking and seats

### How do I book a ticket?

Sign in, choose a movie, choose a showtime, select one or more available seats, and submit the booking. A successful booking is created with the status CONFIRMED and receives a booking reference.

### Why can I not select a seat?

A seat shown as booked is already reserved for that showtime. CineHub does not place a temporary hold on seats, so another user may book a seat before you submit. Choose another available seat.

### What does "Seat A1 is already booked" mean?

It means another confirmed booking already includes that seat for the same showtime. The booking was not created. Select a different seat and try again.

### What does "Please select at least one seat" mean?

You submitted the booking form without choosing a seat. Select at least one available seat before continuing.

### How many bookings can I have?

You can have up to 3 confirmed bookings at the same time. Cancelled bookings do not count toward the limit. If you already have 3 confirmed bookings, CineHub returns: "Maximum 3 bookings are allowed per user."

### How do I book again after reaching the limit?

Cancel one of your confirmed bookings from My Bookings, then make the new booking. Cancelling frees a booking slot.

### Can I book more than 3 tickets in one booking?

CineHub does not currently set a separate maximum number of seats per booking. The seats must be available for the chosen showtime. The 3-booking limit applies to bookings, not to the number of seats in a single booking.

### Can I book seats for someone else?

You can select seats in your own account, but the booking remains associated with your account and email address. The person attending should be able to provide the booking reference at the theatre if required.

### Can I change my seats after booking?

Seat changes are not currently supported. Cancel the confirmed booking and create a new one, subject to seat availability and the 3-booking limit. Cancellation is final.

### Can I transfer my booking to another person?

Booking transfers are not currently supported. Do not share your account or booking reference as a way to transfer ownership.

### Where is my booking reference?

The reference appears on the confirmation page, on the booking details page, and in My Bookings. It starts with CH- and is followed by 10 letters or numbers, for example CH-1A2B3C4D5E.

### Will I receive a confirmation email or SMS?

No. CineHub does not currently send booking confirmation emails or SMS messages. The confirmation is shown on screen only. Save the reference and take a screenshot if needed.

### Why is my booking not showing in My Bookings?

Check that you are signed in to the same account used for the booking, confirm the booking reference, and refresh the page. If it still does not appear, contact support with your details and reference.

### Why do I see a showtime that has already passed?

CineHub displays showtimes as they are stored in the system and does not currently filter out past showtimes. Check the date and time carefully before booking.

### How is the total amount calculated?

The total is the showtime price multiplied by the number of selected seats. The amount is shown in INR. CineHub does not currently add separate taxes or convenience fees.

### Are seat types priced differently?

CineHub stores a seat type, but the current booking flow uses one price per showtime for all selected seats. There is no separate seat-type surcharge in the application.

### Can I hold a seat while I decide?

No. CineHub does not currently provide a temporary seat hold. A seat is reserved only when a booking is successfully created.

### Can I book a past showtime?

The application does not currently block bookings based on the showtime date or time. Check the date and time before confirming. Do not book a past showtime by mistake.

## Cancellation and refunds

### How do I cancel a booking?

Open My Bookings, open the booking details, and use the Cancel Booking button. You must be signed in to the account that made the booking.

### Can I cancel any booking?

Only bookings with the status CONFIRMED can be cancelled. If the booking is already cancelled, CineHub returns: "Only confirmed bookings can be cancelled."

### Is there a cancellation deadline?

CineHub does not currently apply a cancellation deadline. A confirmed booking can be cancelled even after the showtime has passed. Theatre rules may still affect entry or refunds handled directly by the theatre.

### What happens after I cancel?

The booking status changes to CANCELLED, the seats are released, and the booking no longer counts toward your 3-confirmed-booking limit. The cancelled booking remains in your history and cannot be reopened.

### Can I undo a cancellation?

No. Cancellation is final. There is no restore or reopen feature in the current application.

### Do I get a refund?

CineHub does not currently collect online payments, so it does not process online refunds. Cancellation changes the booking status only. If you paid a theatre directly, ask that theatre about its refund rules.

### Can I cancel after the showtime?

The system currently allows cancellation of any CONFIRMED booking, including after the showtime has passed. No online refund is issued. Contact the theatre for any separate arrangement.

### Why can I not cancel someone else's booking?

CineHub checks that the booking belongs to the signed-in account. You can only view and cancel your own bookings.

## Payments and pricing

### Can I pay online?

No. CineHub does not currently provide an online payment gateway and does not collect card, bank, UPI, or wallet details.

### Is my booking confirmed without payment?

Yes. The current application creates a booking with the status CONFIRMED without an online payment step. Any payment arrangement with a theatre is separate.

### Can I get an invoice?

CineHub does not currently generate invoices or tax documents. The booking confirmation shows the reference, movie, theatre, screen, showtime, seats, and total amount.

### Does CineHub store my card details?

No. The application has no payment form and does not collect card details.

## Privacy and security

### What personal data does CineHub store?

CineHub stores your name, email address, hashed password, account role, account creation date, and booking details such as movie, showtime, seats, amount, status, and reference. See the Privacy, Cookies and Data Retention Policy for details.

### Does CineHub use cookies?

CineHub uses an essential session cookie to keep you signed in and to protect forms. It does not currently use advertising or third-party analytics cookies. Blocking the session cookie prevents login and account features from working.

### Is my password stored securely?

CineHub stores a one-way password hash, not plain text. Use a strong, unique password and never share it. Support will never ask for your password or a one-time password.

### Someone accessed my account. What should I do?

Contact support immediately at support@cinehub.example. If you can still sign in, change your password through the available support process and log out of shared devices.

## Support

### How do I contact support?

Email support@cinehub.example, call +91 00000 00000 during support hours, or visit the Help Centre at /help. Include your name, registered email address, booking reference, and a clear description of the issue.

### What are support hours?

Monday to Saturday, 10:00 to 18:00 IST, excluding public holidays. Messages sent outside these hours are handled on the next business day.

### How quickly will I get a reply?

General questions have a first-response target of 2 business days. Booking and cancellation issues have a target of 1 business day for first response. Privacy requests are targeted within 30 days. These are targets, not guarantees.

### How do I escalate an issue?

Reply to the same email thread and ask for escalation. Include your original request and any reference number. Escalation moves from the support agent to the support lead, then to the product or operations owner if needed.
