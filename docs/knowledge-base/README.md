# CineHub Knowledge Base

This folder is the **source of truth** for CineHub's customer policies, support guides, and internal support playbook. The PDFs in `public/documents/` are generated from these Markdown files, so edit the Markdown, never the PDF.

## Structure

```
docs/knowledge-base/
├── customer/
│   ├── POL-001-terms-of-service.md
│   ├── POL-002-privacy-cookies-data-retention-policy.md
│   ├── POL-003-account-registration-policy.md
│   ├── POL-004-booking-seat-cancellation-policy.md
│   ├── SUP-001-customer-support-contact-guide.md
│   ├── SUP-002-faq-troubleshooting.md
│   └── SUP-003-system-capabilities-limitations.md
├── internal/
│   └── OPS-001-support-operations-playbook.md
└── manifest.json
```

Generated outputs:

- Customer PDFs: `public/documents/policies/` and `public/documents/support/`
- Internal PDF: `docs/generated/internal/` (never placed under `public/`)
- RAG manifest: `docs/knowledge-base/manifest.json`

## Regenerate the PDFs

```bash
python3 -m pip install --user 'reportlab<5'
python3 tools/generate_knowledge_pdfs.py
```

The script:

- reads the YAML-style front matter from each Markdown file;
- writes the PDF to the path in the `output` field;
- records the document ID, version, effective date, source path, PDF path, public URL, SHA-256 checksum, and file size in `manifest.json`.

## Public URLs

| Document | URL |
| --- | --- |
| POL-001 Terms of Service | `/documents/policies/POL-001-terms-of-service.pdf` |
| POL-002 Privacy, Cookies and Data Retention | `/documents/policies/POL-002-privacy-cookies-data-retention-policy.pdf` |
| POL-003 Account and Registration | `/documents/policies/POL-003-account-registration-policy.pdf` |
| POL-004 Booking, Seat and Cancellation | `/documents/policies/POL-004-booking-seat-cancellation-policy.pdf` |
| SUP-001 Customer Support and Contact | `/documents/support/SUP-001-customer-support-contact-guide.pdf` |
| SUP-002 FAQ and Troubleshooting | `/documents/support/SUP-002-faq-troubleshooting.pdf` |
| SUP-003 System Capabilities and Limitations | `/documents/support/SUP-003-system-capabilities-limitations.pdf` |

The internal playbook is **not** linked from the website and has no public URL.

## Before publishing

Replace these placeholders consistently across all documents:

- `support@cinehub.example`
- `privacy@cinehub.example`
- `security@cinehub.example`
- `product@cinehub.example`
- `+91 00000 00000`
- support hours, response targets, and escalation owners
- the legal entity name, registered address, and governing jurisdiction details
- the data-retention periods in POL-002

This is version 1.0 operational documentation, not a substitute for review by a qualified lawyer or privacy professional. Review the legal documents against the law that applies to the business before publishing.

## RAG notes

- Ingest the Markdown or the extracted PDF text, not the rendered HTML page.
- Keep the front matter as metadata (`doc_id`, `version`, `effective_date`, `audience`, `category`, `confidentiality`).
- Keep the internal document in a separate index or apply a `confidentiality=internal` filter so it is never returned to customers.
- Chunk by section heading (roughly 400-800 tokens), with 10-15% overlap.
- Do not split FAQ question/answer pairs, tables, or lists of limits.
- Use `manifest.json` to map a retrieved chunk back to the correct PDF page and public URL for citations.
- The key canonical facts are: 10 registered accounts total, 3 confirmed bookings per user, no online payment, no online refund, no email/SMS confirmation, no self-service password reset, and no seat hold.
