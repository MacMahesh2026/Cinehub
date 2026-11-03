---
doc_id: OPS-001
title: Support Operations Playbook
subtitle: Internal triage, escalation, and response procedures for CineHub support
version: 1.0
effective_date: 2026-11-03
last_reviewed: 2026-11-03
audience: Internal support team
category: Operations
owner: CineHub Support Lead
confidentiality: Internal
output: docs/generated/internal/OPS-001-support-operations-playbook.pdf
---

## 1. Purpose and scope

This playbook is for the CineHub support team. It explains how to triage common requests, verify identity, escalate issues, and respond without promising features that the current application does not provide.

This is an internal document. Do not publish it, link it from the public website, or include it in a customer-facing retrieval index.

## 2. System summary for support

CineHub is a Symfony web application backed by a relational database. The main flow is:

1. A customer registers with a name, email address, and password.
2. The customer signs in with email and password.
3. The customer browses movies and stored showtimes.
4. The customer selects available seats for a showtime.
5. The customer submits a booking.
6. The system creates a booking with the status CONFIRMED and a reference in the format CH- plus 10 letters or numbers.
7. The customer can view the booking in My Bookings and cancel it from the booking details page.
8. Cancellation changes the status to CANCELLED and releases the seats.

The system stores users, bookings, booking seats, movies, theatres, screens, showtimes, and seats. It does not store payment data.

## 3. Current limits and rules

| Rule | Value |
| --- | --- |
| Maximum registered accounts | 10 total |
| Maximum confirmed bookings per user | 3 |
| Minimum password length | 8 characters |
| Email uniqueness | One account per email address |
| Booking statuses | CONFIRMED, CANCELLED |
| Cancellation eligibility | CONFIRMED bookings only |
| Cancellation deadline | None currently enforced |
| Online payment | Not available |
| Online refund | Not available |
| Confirmation channel | On-screen only; no email or SMS |
| Password reset | No self-service flow |
| Account deletion | No self-service flow |
| Admin interface | None exposed |

## 4. Identity verification

Before discussing account-specific data or making any manual change, verify the requester.

Minimum verification:

1. Confirm the full name on the account.
2. Confirm the registered email address.
3. For booking issues, confirm the booking reference and at least two of: movie, theatre, screen, showtime, seat numbers.
4. Check that the request is coming from the registered email address where possible.
5. If the request involves account access, use the approved identity-verification procedure before discussing or changing credentials.

Do not reveal whether an email address exists on another account, and do not share another customer's booking details.

## 5. Standard workflows

### 5.1 Registration closed because 10 accounts exist

**Symptoms:** The customer sees "System allows up to 10 users only. Registration is closed."

**Action:**

1. Confirm that the limit is reached; do not suggest repeated attempts.
2. Explain that CineHub currently allows 10 registered accounts and there is no waiting list.
3. Do not delete an existing account to make room unless the account owner has requested deletion and the deletion has been approved.
4. Do not create duplicate or shared accounts.
5. If the customer has a business need, log the request and escalate to the product owner for a capacity decision.

### 5.2 Email already registered

**Symptoms:** Web form shows "Email is already registered"; API returns HTTP 409.

**Action:**

1. Ask the customer to sign in instead of registering again.
2. If they cannot access the account, follow the account-access workflow.
3. Do not merge accounts or change an email address without the approved verification process.

### 5.3 Customer has reached 3 confirmed bookings

**Symptoms:** "Maximum 3 bookings are allowed per user."

**Action:**

1. Explain that the limit counts confirmed bookings only.
2. Ask the customer to cancel one confirmed booking from My Bookings to free a slot.
3. Confirm that cancelled bookings do not count toward the limit.
4. Do not promise an override. There is no support override in the customer interface.
5. If an exception is approved by the product owner, document the approval and follow the approved data procedure. Do not make undocumented database changes.

### 5.4 Seat already booked

**Symptoms:** "Seat A1 is already booked" or a seat appears unavailable.

**Action:**

1. Explain that CineHub does not hold seats temporarily.
2. Ask the customer to select another available seat.
3. If the customer believes the seat map is stale, confirm the movie, theatre, screen, showtime, and seat, then escalate as a content or concurrency issue.
4. Do not manually reserve a seat.

### 5.5 Customer cannot cancel

**Symptoms:** Cancel button missing, error, or "Only confirmed bookings can be cancelled."

**Action:**

1. Confirm the customer is signed in to the account that made the booking.
2. Check the booking status. Only CONFIRMED bookings can be cancelled.
3. Walk the customer through My Bookings, booking details, and Cancel Booking.
4. Explain that cancellation is final and cannot be undone.
5. If the booking is already CANCELLED, no further cancellation is needed.
6. If the cancel action fails for a confirmed booking, capture the error, booking reference, timestamp, and browser details, then escalate as a technical issue.

### 5.6 Customer asks about a refund

**Action:**

1. Explain that CineHub does not collect online payments and does not process online refunds.
2. If the customer paid a theatre directly, direct them to that theatre.
3. Do not ask for card or bank details.
4. Do not promise a refund, credit, or compensation.

### 5.7 Customer did not receive a confirmation email

**Action:**

1. Explain that CineHub does not send confirmation emails or SMS messages.
2. Help the customer find the booking in My Bookings.
3. Ask them to save the booking reference.
4. If they cannot find the booking, follow the missing-booking workflow.

### 5.8 Missing booking

**Action:**

1. Confirm the customer is signed in to the correct account.
2. Ask for the booking reference and the email address used.
3. Check the booking status and account association.
4. Do not share booking details with a different account holder.
5. If the booking does not exist, escalate with the details and timestamps.

### 5.9 Password or account access issue

**Action:**

1. Explain that there is no self-service password reset.
2. Verify identity using the approved procedure.
3. Follow the approved account-recovery process. Do not ask for the current password.
4. If no reset procedure is available, escalate to the product owner and record the request.
5. Never send a password by email or chat.

### 5.10 Account deletion or privacy request

**Action:**

1. Route the request to privacy@cinehub.example.
2. Confirm the request came from the registered email address where possible.
3. Record the request date, account identifier, and requested right.
4. Verify identity before any disclosure or deletion.
5. Retain records where required for legal, fraud-prevention, or dispute purposes.
6. Confirm completion to the requester through the approved channel.

### 5.11 Past showtime visible

**Action:**

1. Explain that the application displays stored showtimes without filtering out past dates.
2. Ask the customer to check the date and time before booking.
3. Record the movie, showtime, and theatre.
4. Escalate to content or product operations so the catalogue can be corrected.

### 5.12 Suspected security issue or account compromise

**Action:**

1. If the customer can still sign in, advise them to change their password through the supported process and log out of shared devices.
2. Collect the account email, observed activity, timestamps, and any error messages.
3. Do not ask for passwords, OTPs, card numbers, or bank details.
4. Escalate immediately to the security contact.
5. Do not discuss the issue publicly or test it against another account.

## 6. Severity and response targets

| Severity | Example | First response | Escalation |
| --- | --- | --- | --- |
| P1 - Critical | Site unavailable, widespread booking failure, suspected data breach | Within 2 hours during support hours | Immediate to product and security owners |
| P2 - High | A customer cannot complete a booking or cancellation, or account access is blocked | Within 1 business day | Support lead, then engineering/product |
| P3 - Normal | General question, registration-full question, booking-limit question | Within 2 business days | Support lead if unresolved |
| P4 - Low | Documentation request, feedback, non-urgent suggestion | Within 3 business days | Product owner if needed |

These are internal targets and may be adjusted by the support lead.

## 7. Escalation matrix

| Level | Owner | Use for |
| --- | --- | --- |
| L1 | Customer support agent | Standard questions, policy explanations, guided self-service |
| L2 | Support lead | Unresolved customer issues, exceptions, repeated failures |
| L3 | Product or engineering owner | Confirmed system defects, data issues, capacity decisions |
| Privacy | Privacy contact | Access, correction, deletion, retention, and privacy complaints |
| Security | Security contact | Vulnerabilities, account compromise, suspected data breach |
| Venue | Theatre contact | Entry rules, age restrictions, venue payments or refunds |

## 8. Response templates

### Registration full

"Thank you for your interest in CineHub. The service currently allows up to 10 registered accounts, and that limit has been reached. There is no waiting list in the application. I have recorded your request; if capacity changes, the team will review it. Please do not create duplicate accounts."

### Booking limit reached

"CineHub currently allows a maximum of 3 confirmed bookings per account. You can cancel one of your confirmed bookings from My Bookings to free a slot, then make the new booking. Cancelled bookings do not count toward the limit and cannot be restored."

### No confirmation email

"CineHub does not currently send booking confirmation emails or SMS messages. Your booking is shown on screen after confirmation and is available in My Bookings while you are signed in. Please save your booking reference."

### Refund question

"CineHub does not currently collect online payments, so it does not process online refunds. Cancelling a booking changes its status and releases the seats. If you paid a theatre directly, please contact that theatre about its refund policy."

### Seat unavailable

"CineHub does not hold seats temporarily. Another customer may have booked the seat before your submission. Please choose another available seat. If the seat map appears incorrect, send us the movie, theatre, screen, showtime, and seat number so we can investigate."

### Password reset

"CineHub does not currently provide a self-service password reset. I can help you through the account-recovery process, which requires identity verification. Please do not send your password or any one-time code."

## 9. Security and data-handling rules

- Use only approved support channels.
- Do not ask for passwords, OTPs, card numbers, bank details, or UPI PINs.
- Do not send personal data to personal email addresses or unapproved tools.
- Do not share one customer's booking details with another person.
- Avoid sharing booking confirmation URLs as proof of identity; the confirmation route is keyed by booking ID and should be treated as sensitive.
- Redact personal data from screenshots and logs before escalation.
- Record security incidents with timestamps and actions taken.
- Follow the retention rules in the Privacy, Cookies and Data Retention Policy.

## 10. Known limitations for support

- No online payment or online refund.
- No email or SMS confirmation.
- No self-service password reset.
- No self-service account deletion.
- No admin interface.
- No seat hold.
- No waitlist.
- No search or filters.
- No date filtering for showtimes.
- No per-seat pricing.
- No booking transfer or seat change.
- No restore after cancellation.
- 10-account global limit.
- 3-confirmed-booking limit per account.

When a customer asks for something outside this list, explain the limitation and escalate. Do not invent a workaround.

## 11. Document control

This playbook is reviewed by the support lead and product owner. Update it whenever limits, features, support channels, or escalation owners change. The current version and effective date are shown at the top of this document.
