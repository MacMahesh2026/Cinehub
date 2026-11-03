---
doc_id: POL-003
title: Account and Registration Policy
subtitle: Rules for creating and using a CineHub account
version: 1.0
effective_date: 2026-11-03
last_reviewed: 2026-11-03
audience: Customer
category: Policy
owner: CineHub Support
confidentiality: Public
output: public/documents/policies/POL-003-account-registration-policy.pdf
---

## 1. Scope

This policy explains how CineHub accounts work, what information is required to register, the current account limit, and what to do if you cannot access your account.

It should be read together with our Terms of Service, Privacy, Cookies and Data Retention Policy, and Booking, Seat and Cancellation Policy.

## 2. Who can register

You may register for a CineHub account if:

- you provide your own name and a valid email address;
- you keep your login details secure;
- you use the account for personal, non-commercial purposes; and
- you agree to the Terms of Service and this policy.

If you are under 18, you may use CineHub only with the involvement of a parent or legal guardian and subject to the age rules of the relevant theatre.

## 3. Information required to register

CineHub asks for:

| Field | Requirement |
| --- | --- |
| Name | Required. Your display name. |
| Email address | Required. Must be valid and unique. CineHub converts the address to lowercase for comparison. |
| Password | Required. Minimum 8 characters. Stored as a one-way hash, not plain text. |

CineHub does not currently require email verification, a phone number, an OTP, or a date of birth during registration.

## 4. Account limit: 10 registered users

CineHub currently allows a maximum of 10 registered accounts in total.

- The limit applies across the whole service, not per person or per email domain.
- When 10 accounts already exist, new registration is closed.
- The web registration form returns the message: "System allows up to 10 users only. Registration is closed."
- The API registration endpoint returns HTTP 403 with the same message.
- CineHub does not currently operate a waiting list or automatic account removal process.

Do not create duplicate accounts to bypass the limit. Duplicate accounts may be suspended.

If you need access and registration is closed, contact support at support@cinehub.example and explain your situation. Support may not be able to create an account if the limit is full; capacity decisions are handled by the CineHub operator.

## 5. Email address rules

- Each account must use a different email address.
- The email address is the login identifier.
- If the address is already registered, the web form returns: "Email is already registered."
- The API returns HTTP 409 with the same message.
- Changing the email address on an existing account is not currently supported as a self-service feature. Contact support if the address on your account is wrong.

## 6. Password rules and account security

- Passwords must be at least 8 characters.
- CineHub stores a password hash and never displays or emails your password.
- You should use a unique password that you do not use on other websites.
- You are responsible for activity performed through your account.
- Log out when using a shared or public device.

CineHub does not currently provide a self-service "forgot password" or password-reset flow. If you cannot sign in, contact support for assistance. Support may require identity verification before helping with account access.

## 7. Roles and permissions

- Every registered user receives the ROLE_USER role.
- A user can view their own bookings and cancel their own confirmed bookings.
- CineHub does not currently provide an administrator interface, staff dashboard, or public user directory.
- Access to another user's bookings is not permitted.

## 8. Login and logout

- Sign in with the email address and password used at registration.
- A successful login creates an authenticated session.
- Logging out ends the session.
- If login fails, check the email address and password first, then contact support. CineHub does not reveal whether a particular email exists as part of a failed login message.

## 9. Account closure and deletion

CineHub does not currently provide self-service account deletion.

To request closure or deletion:

1. Email privacy@cinehub.example from the address registered on the account.
2. Include your name, email address, and a clear description of the request.
3. Complete any identity verification requested by support.

Some booking or security records may be retained where required for legal, fraud-prevention, or dispute purposes. See the Privacy, Cookies and Data Retention Policy for retention details.

## 10. Suspension and duplicate accounts

CineHub may suspend or close an account if it is used to:

- bypass the 10-account limit or the 3-confirmed-booking limit;
- make false or speculative bookings;
- access another user's data;
- interfere with the service; or
- breach the Terms of Service.

Where appropriate, we may contact you before taking action so that you can explain the activity.

## 11. Registration through the API

The API registration endpoint is:

    POST /api/register

It accepts a JSON body containing name, email, and password. It applies the same validation and limits as website registration:

| Condition | API response |
| --- | --- |
| Missing or invalid name, email, or password | HTTP 400 with a validation message |
| Password shorter than 8 characters | HTTP 400 with a validation message |
| Email already registered | HTTP 409 with "Email is already registered." |
| 10 users already registered | HTTP 403 with "System allows up to 10 users only. Registration is closed." |
| Successful registration | HTTP 201 with the new user's id, name, email, roles, and creation date |

The API currently exposes registration only. It does not expose a public login, token-issuing, booking, or account-management endpoint in this version.

## 12. Changes to this policy

We may update this policy as the account system changes. The current version and effective date are shown at the top of this document.

## 13. Contact

For account help:

- Email: support@cinehub.example
- Phone: +91 00000 00000
- Hours: Monday to Saturday, 10:00 to 18:00 IST
- Privacy requests: privacy@cinehub.example
- Help Centre: /help
