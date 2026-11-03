---
doc_id: POL-002
title: Privacy, Cookies and Data Retention Policy
subtitle: How CineHub collects, uses, stores, and protects customer data
version: 1.0
effective_date: 2026-11-03
last_reviewed: 2026-11-03
audience: Customer
category: Policy
owner: CineHub Support
confidentiality: Public
output: public/documents/policies/POL-002-privacy-cookies-data-retention-policy.pdf
---

## 1. Scope and controller

This policy explains how CineHub handles personal data when you use the website, create an account, view showtimes, make a booking, cancel a booking, or contact support.

CineHub is operated by the CineHub team. The operator of CineHub is the data controller for the personal data described in this policy. For privacy questions or requests, contact privacy@cinehub.example.

This policy should be read together with our Terms of Service, Account and Registration Policy, and Booking, Seat and Cancellation Policy.

## 2. Data we collect

### Account data

When you register, CineHub collects and stores:

- your name;
- your email address;
- your password, stored only as a one-way password hash;
- your account role (currently ROLE_USER);
- the date and time your account was created; and
- technical session data needed to keep you logged in.

CineHub does not currently send a verification email and does not collect a phone number, postal address, date of birth, government identifier, or payment card during registration.

### Booking data

When you make or cancel a booking, CineHub stores:

- the booking reference;
- the movie, theatre, screen, and showtime;
- the seats selected;
- the booking date and time;
- the total amount shown for the booking;
- the booking status (CONFIRMED or CANCELLED); and
- the account that created the booking.

### Support and privacy communications

If you contact support or make a privacy request, we receive the information you choose to provide, such as your name, email address, booking reference, and a description of the issue. We use it to investigate and respond.

### Technical data and logs

CineHub uses a session cookie to keep you logged in and to protect forms against cross-site request forgery. Depending on how the service is deployed, standard server and application logs may record technical request information such as timestamps, request paths, error details, IP address, and browser user-agent. Logs are used for security, troubleshooting, and service reliability.

CineHub does not currently run advertising, behavioural profiling, or third-party analytics tools.

### API data

The CineHub API currently exposes a registration endpoint at POST /api/register. API registration collects the same account data as website registration. Where API authentication tokens are used, the token is sent by the client in the HTTP Authorization header; CineHub does not store API tokens in a browser cookie for this application.

## 3. Data we do not collect

The current CineHub application does not collect or store:

- payment card numbers, bank details, UPI identifiers, or wallet credentials;
- government-issued identity documents;
- precise location data;
- biometric data;
- marketing preferences or advertising profiles; or
- email or SMS notification preferences, because the service does not currently send booking notifications.

## 4. Why we use personal data

CineHub uses personal data to:

- create and secure your account;
- authenticate you when you log in;
- show your bookings and booking details;
- create, confirm, and cancel bookings;
- prevent duplicate accounts, booking-limit abuse, and fraudulent activity;
- provide customer support and respond to privacy requests;
- maintain the security and reliability of the service; and
- comply with applicable legal obligations.

We rely on the performance of our agreement with you, your consent where required, our legitimate interests in operating and securing the service, and legal obligations, as applicable in the relevant jurisdiction.

## 5. Cookies and sessions

CineHub uses cookies and similar browser storage only as needed to operate the service.

| Cookie or mechanism | Purpose | Essential? |
| --- | --- | --- |
| Session cookie | Keeps you logged in and stores short-lived form protection data between requests | Yes |
| CSRF protection | Helps prevent unauthorised form submissions, including booking cancellation | Yes |
| API bearer token | Used by API clients in the Authorization header, not stored as a browser cookie by CineHub | Only for API use |

CineHub does not currently use advertising cookies, social media tracking pixels, or third-party analytics cookies. If these are added in future, this policy and the cookie information will be updated before use.

You can block or delete cookies in your browser, but blocking the session cookie will prevent you from logging in and using account features.

## 6. Sharing personal data

CineHub does not sell personal data. We may share limited data with:

- hosting, database, email, and infrastructure providers that process data on our instructions;
- theatres or ticketing partners where necessary to support a booking or resolve a dispute;
- professional advisers, auditors, or insurers where necessary and subject to confidentiality;
- law enforcement, regulators, or courts where required by law; and
- a successor organisation in connection with a merger, acquisition, or transfer of the service, subject to appropriate safeguards.

Where a service provider processes personal data on our behalf, we require it to protect the data and use it only for the agreed purpose.

## 7. Security

CineHub takes reasonable technical and organisational measures to protect personal data, including:

- storing passwords as one-way hashes rather than plain text;
- using authenticated sessions for account features;
- checking that a booking belongs to the logged-in user before allowing cancellation;
- using CSRF protection for cancel actions;
- limiting account creation and booking abuse through system limits; and
- restricting access to production systems and secrets.

No system is completely secure. You should use a strong, unique password and contact support immediately if you believe your account has been compromised. CineHub cannot guarantee absolute security.

## 8. Data retention

CineHub keeps personal data only for as long as needed for the purposes described in this policy.

| Data type | Default retention approach |
| --- | --- |
| Account data | While your account remains active, and for up to 24 months after your last booking or account activity |
| Booking records | Up to 24 months after the booking date, unless needed for a dispute, fraud investigation, or legal obligation |
| Support and privacy requests | Up to 24 months after the request is closed |
| Security and server logs | Up to 90 days, unless a longer period is needed for an investigation |
| Data required by law | For the period required by applicable law |

When data is no longer needed, it is deleted or anonymised. Some records may be retained longer where necessary to establish, exercise, or defend legal claims, or to comply with a legal obligation.

## 9. Your privacy rights

Subject to applicable law, you may request to:

- access the personal data CineHub holds about you;
- correct inaccurate account information;
- delete your account or personal data;
- restrict or object to certain processing;
- withdraw consent where processing is based on consent;
- receive information about how your data is used; and
- lodge a complaint with a competent data protection authority.

To make a request, email privacy@cinehub.example with enough information for us to identify your account. We may need to verify your identity before acting on the request. We aim to respond within 30 days, or within the period required by applicable law.

CineHub does not currently provide self-service account deletion. A deletion request is handled manually and may require identity verification. Some booking records may be retained where required for legal, accounting, fraud-prevention, or dispute purposes.

## 10. Children's privacy

CineHub is not intended for children under 13. If you are between 13 and 18, you should use the service only with the involvement of a parent or legal guardian and subject to the age rules of the relevant theatre.

If you believe a child has provided personal data without appropriate consent, contact privacy@cinehub.example so that we can review and, where appropriate, delete the data.

## 11. International transfers

CineHub may use hosting or service providers located outside your country. Where personal data is transferred internationally, we take reasonable steps to ensure it receives an appropriate level of protection consistent with applicable law.

## 12. Changes to this policy

We may update this policy to reflect changes to the service, legal requirements, or our practices. The current version and effective date are shown at the top of this document. Material changes will be communicated through reasonable means.

## 13. Contact

For privacy questions, data requests, or complaints:

- Privacy email: privacy@cinehub.example
- General support: support@cinehub.example
- Phone: +91 00000 00000
- Hours: Monday to Saturday, 10:00 to 18:00 IST
- Help Centre: /help
