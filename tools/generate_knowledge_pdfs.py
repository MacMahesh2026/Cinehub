#!/usr/bin/env python3
"""Generate CineHub policy/support PDFs from Markdown sources.

The Markdown files in docs/knowledge-base are the source of truth for the
website PDFs and for RAG ingestion. Run this script after editing them:

    python3 tools/generate_knowledge_pdfs.py

Requirements:
    python3 -m pip install --user reportlab

The script also writes docs/knowledge-base/manifest.json, which lists every
document's public URL (if any) and checksum. Internal documents are written
outside public/ so they cannot be downloaded from the website.
"""

from __future__ import annotations

import hashlib
import json
import re
from datetime import date
from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.platypus import (
    HRFlowable,
    LongTable,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)

ROOT = Path(__file__).resolve().parent.parent
SOURCE_DIR = ROOT / "docs" / "knowledge-base"
MANIFEST_PATH = SOURCE_DIR / "manifest.json"

PAGE_WIDTH, PAGE_HEIGHT = A4
LEFT_MARGIN = RIGHT_MARGIN = 20 * mm
TOP_MARGIN = 24 * mm
BOTTOM_MARGIN = 20 * mm

BRAND = colors.HexColor("#111827")
ACCENT = colors.HexColor("#2563eb")
MUTED = colors.HexColor("#6b7280")
BORDER = colors.HexColor("#e5e7eb")
SOFT_BG = colors.HexColor("#f9fafb")
DANGER_BG = colors.HexColor("#fef2f2")


def parse_front_matter(text: str):
    if not text.startswith("---"):
        raise ValueError("Markdown file must start with YAML-style front matter")

    parts = text.split("---", 2)
    if len(parts) != 3:
        raise ValueError("Invalid front matter block")

    metadata = {}
    for line in parts[1].strip().splitlines():
        line = line.strip()
        if not line or line.startswith("#") or ":" not in line:
            continue
        key, value = line.split(":", 1)
        metadata[key.strip()] = value.strip().strip('"').strip("'")

    return metadata, parts[2].lstrip("\n")


def escape_inline(text: str) -> str:
    text = text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")

    # Links: [label](url)
    text = re.sub(
        r"\[([^\]]+)\]\(([^)]+)\)",
        r'<link href="\2" color="#2563eb">\1</link>',
        text,
    )
    text = re.sub(r"\*\*(.+?)\*\*", r"<b>\1</b>", text)
    text = re.sub(r"`([^`]+)`", r'<font name="Courier">\1</font>', text)
    return text


def build_styles():
    base = getSampleStyleSheet()

    styles = {
        "title": ParagraphStyle(
            "Title",
            parent=base["Title"],
            fontName="Helvetica-Bold",
            fontSize=24,
            leading=29,
            textColor=BRAND,
            spaceAfter=2,
            alignment=TA_LEFT,
        ),
        "subtitle": ParagraphStyle(
            "Subtitle",
            parent=base["Normal"],
            fontName="Helvetica",
            fontSize=10.5,
            leading=15,
            textColor=MUTED,
            spaceAfter=14,
        ),
        "h2": ParagraphStyle(
            "H2",
            parent=base["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=14,
            leading=18,
            textColor=BRAND,
            spaceBefore=16,
            spaceAfter=6,
            keepWithNext=1,
        ),
        "h3": ParagraphStyle(
            "H3",
            parent=base["Heading3"],
            fontName="Helvetica-Bold",
            fontSize=11.5,
            leading=15,
            textColor=colors.HexColor("#374151"),
            spaceBefore=11,
            spaceAfter=4,
            keepWithNext=1,
        ),
        "body": ParagraphStyle(
            "Body",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=9.8,
            leading=14.5,
            textColor=colors.HexColor("#222222"),
            spaceAfter=6,
            splitLongWords=1,
        ),
        "bullet": ParagraphStyle(
            "Bullet",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=9.8,
            leading=14.5,
            textColor=colors.HexColor("#222222"),
            leftIndent=16,
            bulletIndent=4,
            spaceAfter=3,
            splitLongWords=1,
        ),
        "quote": ParagraphStyle(
            "Quote",
            parent=base["BodyText"],
            fontName="Helvetica-Oblique",
            fontSize=9.5,
            leading=14,
            textColor=MUTED,
            leftIndent=12,
            borderPadding=6,
            backColor=SOFT_BG,
            spaceBefore=4,
            spaceAfter=8,
        ),
        "cell": ParagraphStyle(
            "Cell",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=8.3,
            leading=11,
            textColor=colors.HexColor("#222222"),
            splitLongWords=1,
        ),
        "cell_header": ParagraphStyle(
            "CellHeader",
            parent=base["BodyText"],
            fontName="Helvetica-Bold",
            fontSize=8.3,
            leading=11,
            textColor=BRAND,
            splitLongWords=1,
        ),
    }

    return styles


def metadata_table(metadata, styles):
    rows = [
        [
            Paragraph("<b>Document ID</b>", styles["cell"]),
            Paragraph(metadata.get("doc_id", ""), styles["cell"]),
            Paragraph("<b>Version</b>", styles["cell"]),
            Paragraph(metadata.get("version", ""), styles["cell"]),
        ],
        [
            Paragraph("<b>Effective date</b>", styles["cell"]),
            Paragraph(metadata.get("effective_date", ""), styles["cell"]),
            Paragraph("<b>Last reviewed</b>", styles["cell"]),
            Paragraph(metadata.get("last_reviewed", ""), styles["cell"]),
        ],
        [
            Paragraph("<b>Audience</b>", styles["cell"]),
            Paragraph(metadata.get("audience", ""), styles["cell"]),
            Paragraph("<b>Classification</b>", styles["cell"]),
            Paragraph(metadata.get("confidentiality", ""), styles["cell"]),
        ],
        [
            Paragraph("<b>Category</b>", styles["cell"]),
            Paragraph(metadata.get("category", ""), styles["cell"]),
            Paragraph("<b>Owner</b>", styles["cell"]),
            Paragraph(metadata.get("owner", ""), styles["cell"]),
        ],
    ]

    table = Table(rows, colWidths=[30 * mm, 55 * mm, 28 * mm, 47 * mm])
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, -1), SOFT_BG),
                ("BOX", (0, 0), (-1, -1), 0.5, BORDER),
                ("INNERGRID", (0, 0), (-1, -1), 0.35, BORDER),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 7),
                ("RIGHTPADDING", (0, 0), (-1, -1), 7),
                ("TOPPADDING", (0, 0), (-1, -1), 5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
            ]
        )
    )
    return table


def parse_blocks(body: str):
    """Yield simple block tuples from the Markdown subset used by the docs."""
    lines = body.splitlines()
    i = 0

    while i < len(lines):
        line = lines[i].rstrip()

        if not line.strip():
            i += 1
            continue

        if line.startswith("```"):
            language = line[3:].strip()
            code_lines = []
            i += 1
            while i < len(lines) and not lines[i].startswith("```"):
                code_lines.append(lines[i])
                i += 1
            i += 1
            yield ("code", language, "\n".join(code_lines))
            continue

        if line.startswith("#### "):
            yield ("h4", line[5:].strip())
            i += 1
            continue

        if line.startswith("### "):
            yield ("h3", line[4:].strip())
            i += 1
            continue

        if line.startswith("## "):
            yield ("h2", line[3:].strip())
            i += 1
            continue

        if line.startswith("# "):
            # The document title already comes from front matter.
            yield ("h2", line[2:].strip())
            i += 1
            continue

        if line.strip() == "---":
            yield ("hr",)
            i += 1
            continue

        if line.startswith("> "):
            quote_lines = []
            while i < len(lines) and lines[i].startswith("> "):
                quote_lines.append(lines[i][2:].strip())
                i += 1
            yield ("quote", " ".join(quote_lines))
            continue

        if line.lstrip().startswith("- ") or line.lstrip().startswith("* "):
            items = []
            while i < len(lines):
                current = lines[i].lstrip()
                if not (current.startswith("- ") or current.startswith("* ")):
                    break
                items.append(current[2:].strip())
                i += 1
            yield ("ul", items)
            continue

        if re.match(r"^\d+\.\s+", line.strip()):
            items = []
            while i < len(lines) and re.match(r"^\d+\.\s+", lines[i].strip()):
                items.append(re.sub(r"^\d+\.\s+", "", lines[i].strip()))
                i += 1
            yield ("ol", items)
            continue

        if line.lstrip().startswith("|"):
            table_lines = []
            while i < len(lines) and lines[i].lstrip().startswith("|"):
                table_lines.append(lines[i].strip())
                i += 1
            yield ("table", table_lines)
            continue

        paragraph_lines = [line.strip()]
        i += 1
        while i < len(lines):
            nxt = lines[i].strip()
            if (
                not nxt
                or nxt.startswith("#")
                or nxt.startswith("> ")
                or nxt.startswith("|")
                or nxt.startswith("- ")
                or nxt.startswith("* ")
                or re.match(r"^\d+\.\s+", nxt)
                or nxt == "---"
                or nxt.startswith("```")
            ):
                break
            paragraph_lines.append(nxt)
            i += 1

        yield ("p", " ".join(paragraph_lines))


def table_flowable(table_lines, styles):
    rows = []
    for index, line in enumerate(table_lines):
        cells = [cell.strip() for cell in line.strip("|").split("|")]
        if index == 1 and all(re.fullmatch(r":?-{2,}:?", cell or "") for cell in cells):
            continue
        rows.append(cells)

    if not rows:
        return Spacer(1, 1)

    column_count = max(len(row) for row in rows)
    normalized = [row + [""] * (column_count - len(row)) for row in rows]

    weights = []
    for column in range(column_count):
        longest = max(len(row[column]) for row in normalized)
        weights.append(max(longest, 10))

    total_weight = sum(weights)
    available = PAGE_WIDTH - LEFT_MARGIN - RIGHT_MARGIN
    widths = [available * weight / total_weight for weight in weights]

    body = []
    for row_index, row in enumerate(normalized):
        style = styles["cell_header"] if row_index == 0 else styles["cell"]
        body.append([Paragraph(escape_inline(cell), style) for cell in row])

    table = LongTable(body, colWidths=widths, repeatRows=1)
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), SOFT_BG),
                ("BOX", (0, 0), (-1, -1), 0.5, BORDER),
                ("INNERGRID", (0, 0), (-1, -1), 0.35, BORDER),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 6),
                ("RIGHTPADDING", (0, 0), (-1, -1), 6),
                ("TOPPADDING", (0, 0), (-1, -1), 5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
            ]
        )
    )
    return table


def build_story(metadata, body, styles):
    story = [
        Paragraph(escape_inline(metadata.get("title", "Untitled")), styles["title"]),
        Paragraph(escape_inline(metadata.get("subtitle", "")), styles["subtitle"]),
        metadata_table(metadata, styles),
        Spacer(1, 8),
        HRFlowable(width="100%", thickness=2, color=ACCENT, spaceBefore=2, spaceAfter=14),
    ]

    for block in parse_blocks(body):
        kind = block[0]

        if kind == "h2":
            story.append(Paragraph(escape_inline(block[1]), styles["h2"]))
        elif kind == "h3":
            story.append(Paragraph(escape_inline(block[1]), styles["h3"]))
        elif kind == "h4":
            story.append(Paragraph(escape_inline(block[1]), styles["h3"]))
        elif kind == "p":
            story.append(Paragraph(escape_inline(block[1]), styles["body"]))
        elif kind == "quote":
            story.append(Paragraph(escape_inline(block[1]), styles["quote"]))
        elif kind == "ul":
            for item in block[1]:
                story.append(
                    Paragraph(escape_inline(item), styles["bullet"], bulletText="-")
                )
            story.append(Spacer(1, 3))
        elif kind == "ol":
            for index, item in enumerate(block[1], start=1):
                story.append(
                    Paragraph(
                        escape_inline(item),
                        styles["bullet"],
                        bulletText=f"{index}.",
                    )
                )
            story.append(Spacer(1, 3))
        elif kind == "table":
            story.append(table_flowable(block[1], styles))
            story.append(Spacer(1, 10))
        elif kind == "hr":
            story.append(HRFlowable(width="100%", thickness=0.5, color=BORDER, spaceBefore=4, spaceAfter=8))
        elif kind == "code":
            code_style = ParagraphStyle(
                "Code",
                parent=styles["body"],
                fontName="Courier",
                fontSize=8,
                leading=11,
                backColor=SOFT_BG,
                borderPadding=6,
                spaceBefore=4,
                spaceAfter=8,
            )
            story.append(Paragraph(escape_inline(block[2]).replace("\n", "<br/>"), code_style))

    return story


def page_decorator(metadata):
    def decorate(canvas, doc):
        canvas.saveState()

        canvas.setFont("Helvetica-Bold", 8)
        canvas.setFillColor(BRAND)
        canvas.drawString(LEFT_MARGIN, PAGE_HEIGHT - 14 * mm, "CineHub")

        canvas.setFont("Helvetica", 8)
        canvas.setFillColor(MUTED)
        canvas.drawRightString(
            PAGE_WIDTH - RIGHT_MARGIN,
            PAGE_HEIGHT - 14 * mm,
            metadata.get("doc_id", ""),
        )

        canvas.setStrokeColor(BORDER)
        canvas.setLineWidth(0.5)
        canvas.line(
            LEFT_MARGIN,
            PAGE_HEIGHT - 16 * mm,
            PAGE_WIDTH - RIGHT_MARGIN,
            PAGE_HEIGHT - 16 * mm,
        )

        canvas.setFont("Helvetica", 7.5)
        canvas.setFillColor(MUTED)
        canvas.drawString(
            LEFT_MARGIN,
            12 * mm,
            "Version {}  |  Effective {}".format(
                metadata.get("version", ""),
                metadata.get("effective_date", ""),
            ),
        )
        canvas.drawRightString(
            PAGE_WIDTH - RIGHT_MARGIN,
            12 * mm,
            "Page {}".format(canvas.getPageNumber()),
        )

        canvas.restoreState()

    return decorate


def build_pdf(metadata, body, output_path: Path):
    styles = build_styles()
    output_path.parent.mkdir(parents=True, exist_ok=True)

    document = SimpleDocTemplate(
        str(output_path),
        pagesize=A4,
        leftMargin=LEFT_MARGIN,
        rightMargin=RIGHT_MARGIN,
        topMargin=TOP_MARGIN,
        bottomMargin=BOTTOM_MARGIN,
        title=metadata.get("title", ""),
        author="CineHub",
        subject=metadata.get("category", ""),
        creator="CineHub knowledge-base generator",
    )

    document.build(
        build_story(metadata, body, styles),
        onFirstPage=page_decorator(metadata),
        onLaterPages=page_decorator(metadata),
    )


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(65536), b""):
            digest.update(chunk)
    return digest.hexdigest()


def main():
    sources = sorted(SOURCE_DIR.glob("customer/*.md")) + sorted(
        SOURCE_DIR.glob("internal/*.md")
    )

    if not sources:
        raise SystemExit("No Markdown sources found under docs/knowledge-base")

    manifest = {
        "generated_at": date.today().isoformat(),
        "generator": "tools/generate_knowledge_pdfs.py",
        "documents": [],
    }

    for source in sources:
        metadata, body = parse_front_matter(source.read_text(encoding="utf-8"))
        output_value = metadata.get("output")
        if not output_value:
            raise SystemExit(f"{source}: missing 'output' metadata")

        output_path = ROOT / output_value
        build_pdf(metadata, body, output_path)

        public_url = None
        try:
            relative_output = output_path.resolve().relative_to((ROOT / "public").resolve())
            public_url = "/" + relative_output.as_posix()
        except ValueError:
            public_url = None

        entry = {
            "doc_id": metadata.get("doc_id", ""),
            "title": metadata.get("title", ""),
            "subtitle": metadata.get("subtitle", ""),
            "version": metadata.get("version", ""),
            "effective_date": metadata.get("effective_date", ""),
            "last_reviewed": metadata.get("last_reviewed", ""),
            "audience": metadata.get("audience", ""),
            "category": metadata.get("category", ""),
            "owner": metadata.get("owner", ""),
            "confidentiality": metadata.get("confidentiality", ""),
            "source": str(source.relative_to(ROOT)),
            "pdf": str(output_path.relative_to(ROOT)),
            "public_url": public_url,
            "sha256": sha256(output_path),
            "bytes": output_path.stat().st_size,
        }

        manifest["documents"].append(entry)
        print(
            "generated {doc_id}: {pdf}{public}".format(
                doc_id=entry["doc_id"],
                pdf=entry["pdf"],
                public=" -> " + public_url if public_url else " (internal only)",
            )
        )

    manifest["documents"].sort(key=lambda item: item["doc_id"])
    MANIFEST_PATH.write_text(
        json.dumps(manifest, indent=2, ensure_ascii=False) + "\n",
        encoding="utf-8",
    )
    print(f"wrote {MANIFEST_PATH.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
