"""
PHM Chart Generator & PDF Report Generator
Generates a PDF matching the Long County PHM Report structure.
"""
from __future__ import annotations
import io
import json
import logging
import urllib.request
import urllib.parse
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List, Optional

from reportlab.lib import colors as rl_colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    PageBreak, Image as RLImage, HRFlowable, KeepTogether,
)
from reportlab.platypus.tableofcontents import TableOfContents

from phm_data_analyzer import ChartData

QUICKCHART_URL = "https://quickchart.io/chart"

# ── Colours matching Long County (AllHealth CHOICE branding) ─────────────────
NAVY   = rl_colors.HexColor("#1F3864")
TEAL   = rl_colors.HexColor("#2E86AB")
LTBLUE = rl_colors.HexColor("#D6E4F0")
LTGREY = rl_colors.HexColor("#F2F2F2")
WHITE  = rl_colors.white
BLACK  = rl_colors.black
PALETTE = ["#1F3864","#2E86AB","#A8DADC","#457B9D","#E63946","#F1A208","#6A994E","#BC4749"]


# ── QuickChart helper ─────────────────────────────────────────────────────────

class ChartGenerator:
    def __init__(self, output_dir: Path):
        self.output_dir = output_dir
        self.output_dir.mkdir(parents=True, exist_ok=True)
        self.logger = logging.getLogger("chart_generator")

    def generate_chart(self, chart_data: ChartData, report_id: int, section_key: str) -> Optional[str]:
        if chart_data.chart_type in ("table", "pyramid"):
            return None
        if not chart_data.labels or not chart_data.values:
            return None
        config = self._build_config(chart_data)
        url = self._url(config)
        if not url:
            return None
        fp = self.output_dir / f"chart_{report_id}_{section_key}.png"
        return str(fp) if self._download(url, fp) else None

    def _build_config(self, cd: ChartData) -> dict:
        type_map = {"bar":"bar","horizontalBar":"horizontalBar","line":"line","pie":"pie"}
        ct = type_map.get(cd.chart_type, "bar")
        if ct == "pie":
            ds = [{"data": cd.values, "backgroundColor": PALETTE[:len(cd.values)]}]
        else:
            ds = [{"label": cd.y_axis, "data": cd.values,
                   "backgroundColor": PALETTE[0], "borderColor": PALETTE[0], "borderWidth":1}]
        cfg = {
            "type": ct,
            "data": {"labels": cd.labels, "datasets": ds},
            "options": {
                "plugins": {"title": {"display": True, "text": cd.title, "font":{"size":13}}},
                "scales": {} if ct == "pie" else {
                    "x": {"beginAtZero": True},
                    "y": {"beginAtZero": True},
                },
            }
        }
        return cfg

    def _url(self, config: dict) -> Optional[str]:
        try:
            s = json.dumps(config, separators=(",",":"))
            return f"{QUICKCHART_URL}?c={urllib.parse.quote(s,safe='')}&w=700&h=350&f=png"
        except Exception:
            return None

    def _download(self, url: str, fp: Path) -> bool:
        for _ in range(3):
            try:
                resp = urllib.request.urlopen(url, timeout=30)
                fp.write_bytes(resp.read())
                return True
            except Exception:
                pass
        return False


# ── ReportLab doc template with TOC ─────────────────────────────────────────

class _DocTemplate(SimpleDocTemplate):
    def __init__(self, filename, **kw):
        super().__init__(filename, **kw)
        self.toc = TableOfContents()
        self.toc.levelStyles = [
            ParagraphStyle(name="TOC1", fontName="Helvetica", fontSize=10,
                           leftIndent=20, firstLineIndent=-20, spaceBefore=4, leading=14),
        ]

    def afterFlowable(self, flowable):
        if getattr(flowable, "_toc_entry", None):
            self.notify("TOCEntry", (0, flowable._toc_entry, self.page))


# ── PDF Report Generator ──────────────────────────────────────────────────────

class PDFReportGenerator:
    """Generates PHM PDF reports matching the Long County structure."""

    # Section metadata: key → (number, title, key_finding, recommendation)
    SECTIONS = {
        "med_by_year": (
            1, "Overall Medical Expenditure by Year",
            "Medical expenditure trends reveal year-over-year cost drivers within this population.",
            "Engage high-cost member cohorts through targeted care management and wellness programs."
        ),
        "med_by_quarter": (
            2, "Overall Medical Expenditure by Quarter",
            "Quarterly trends identify seasonal patterns and potential gaps in care coordination.",
            "Deploy targeted outreach during high-utilization quarters to reduce unnecessary spend."
        ),
        "emp_spouse_dep": (
            3, "Medical Expenditure by Relationship (Employee / Spouse / Dependent)",
            "Dependents may represent a disproportionate share of total medical spend.",
            "Implement family-focused wellness initiatives and dependent health-education programs."
        ),
        "gender_exp": (
            4, "Medical Expenditure by Gender",
            "Gender-specific disease prevalence drives differential spending patterns.",
            "Develop gender-targeted screening programs and chronic disease interventions."
        ),
        "risk_groups": (
            5, "Disease Group Risk Stratification",
            "A small proportion of members in high-risk groups account for the majority of costs.",
            "Implement intensive case management for Group 5-7 members to reduce avoidable costs."
        ),
        "chronic_diseases": (
            6, "Chronic Disease Expenditures",
            "Chronic conditions represent the primary driver of sustained healthcare costs.",
            "Focus disease management programs on the top chronic condition categories identified."
        ),
        "diabetes_strat": (
            7, "Diabetes Risk Stratification by Co-morbidities",
            "Diabetic members with multiple co-morbidities face exponentially higher costs.",
            "Provide intensive diabetes coaching and co-morbidity management for high-risk members."
        ),
        "hospital_util": (
            8, "Inpatient / Outpatient / Emergency Room Utilization",
            "Avoidable ER visits and inpatient stays represent significant cost-reduction opportunities.",
            "Implement ER diversion programs and post-discharge follow-up to reduce readmissions."
        ),
        "provider_type": (
            9, "Expenditure by Provider / Service Type",
            "Specialist utilization without primary care coordination drives unnecessary costs.",
            "Encourage primary care-led coordination and appropriate specialty referral pathways."
        ),
        "demographics": (
            10, "Age / Gender Demographics",
            "Age-gender distribution shapes current and future population health risk profiles.",
            "Tailor preventive and chronic care programs to the dominant age-gender cohorts."
        ),
        "breast_screening": (
            11, "Breast Cancer Screening Compliance",
            "Breast cancer screening rates below HEDIS benchmarks indicate significant gaps in care.",
            "Implement reminder programs and incentives to close breast cancer screening gaps."
        ),
        "cervical_screening": (
            12, "Cervical Cancer Screening Compliance",
            "Cervical cancer screening compliance impacts early detection and long-term outcomes.",
            "Educate eligible female members and remove barriers to cervical cancer screening."
        ),
        "colon_screening": (
            13, "Colon Cancer Screening Compliance",
            "Colon cancer screening compliance remains below national HEDIS benchmarks.",
            "Promote colorectal cancer screening through member outreach and provider alerts."
        ),
        "catastrophic": (
            14, "Catastrophic Claims (>= $100,000)",
            "Catastrophic claimants drive disproportionate costs and require intensive management.",
            "Engage catastrophic claimants with dedicated case managers and treatment navigation."
        ),
        "pharm_by_year": (
            15, "Overall Pharmacy Expenditure by Year",
            "Pharmacy costs trend upward annually driven by specialty and brand drug utilization.",
            "Implement step-therapy and generic substitution programs to manage pharmacy spend."
        ),
        "pharm_by_quarter": (
            16, "Overall Pharmacy Expenditure by Quarter",
            "Quarterly pharmacy patterns reveal refill timing and adherence opportunities.",
            "Align pharmacy outreach with high-spend quarters to improve medication adherence."
        ),
        "pharm_relationship": (
            17, "Pharmacy Expenditure by Relationship",
            "Dependent pharmacy spend may indicate pediatric or chronic prescription trends.",
            "Target pharmacy benefit utilization programs by relationship tier for maximum impact."
        ),
        "brand_generic": (
            18, "Brand vs. Generic Pharmaceutical Expenditure",
            "High brand drug utilization presents an immediate cost-reduction opportunity.",
            "Enforce generic-first policies and educate prescribers on equivalent generic options."
        ),
        "medication_mpr": (
            19, "Medication Possession Ratio (MPR) Compliance",
            "Low MPR indicates medication non-adherence, a key driver of avoidable complications.",
            "Deploy pharmacist-led adherence programs targeting members with MPR below 80%."
        ),
    }

    def __init__(self, output_dir: Path):
        self.output_dir = output_dir
        self.output_dir.mkdir(parents=True, exist_ok=True)
        self.logger = logging.getLogger("pdf_generator")
        self._init_styles()

    def _init_styles(self):
        base = getSampleStyleSheet()
        S = {}
        def add(name, **kw):
            parent = kw.pop("parent", base["Normal"])
            S[name] = ParagraphStyle(name=name, parent=parent, **kw)
        add("CoverTitle",    fontName="Helvetica-Bold", fontSize=26, alignment=TA_CENTER,
            textColor=NAVY, spaceAfter=16)
        add("CoverSub",      fontName="Helvetica",      fontSize=14, alignment=TA_CENTER,
            textColor=TEAL, spaceAfter=8)
        add("CoverClient",   fontName="Helvetica-Bold", fontSize=18, alignment=TA_CENTER,
            textColor=NAVY, spaceAfter=0)
        add("SectionHead",   fontName="Helvetica-Bold", fontSize=14,
            textColor=WHITE, spaceAfter=6, spaceBefore=0)
        add("SubHead",       fontName="Helvetica-Bold", fontSize=11,
            textColor=NAVY, spaceAfter=4, spaceBefore=8)
        add("Body",          fontName="Helvetica",      fontSize=9,
            leading=13, spaceAfter=4)
        add("BodyBold",      fontName="Helvetica-Bold", fontSize=9,
            leading=13, spaceAfter=4)
        add("Small",         fontName="Helvetica",      fontSize=8, leading=11)
        add("TOCEntry",      fontName="Helvetica",      fontSize=10, leftIndent=20)
        add("FindingLabel",  fontName="Helvetica-Bold", fontSize=9,
            textColor=WHITE)
        add("FindingText",   fontName="Helvetica",      fontSize=9,
            textColor=WHITE, leading=12)
        self.S = S

    # ── Public API ────────────────────────────────────────────────────────────

    def generate_pdf(self, report_id: int, report_name: str, client_name: str,
                     year: int, charts: Dict[str, ChartData],
                     chart_files: Dict[str, str],
                     metadata: Optional[Dict[str, Any]] = None) -> Optional[str]:
        fp = self.output_dir / f"PHM_Report_{report_id}_{year}.pdf"
        try:
            doc = _DocTemplate(str(fp), pagesize=letter,
                               leftMargin=0.6*inch, rightMargin=0.6*inch,
                               topMargin=0.6*inch, bottomMargin=0.5*inch)
            story = []

            # 1. Cover page
            self._cover(story, client_name, metadata)

            # 2. Table of Contents
            story.append(self._section_header_table("Table of Contents"))
            story.append(doc.toc)
            story.append(PageBreak())

            # 3. Executive Summary (risk group table + intro text)
            self._executive_summary(story, charts.get("risk_groups"))

            # 4. One page per section
            for key, info in self.SECTIONS.items():
                sec_num, sec_title, key_finding, recommendation = info
                cd = charts.get(key)
                cf = chart_files.get(key)
                if cd or cf:
                    self._section_page(story, sec_num, sec_title,
                                       key_finding, recommendation, cd, cf)

            doc.multiBuild(story)
            self.logger.info(f"PDF generated: {fp}")
            return str(fp)
        except Exception as e:
            self.logger.error(f"PDF generation failed: {e}", exc_info=True)
            return None

    # ── Internal builders ─────────────────────────────────────────────────────

    def _cover(self, story, client_name: str, meta: Optional[dict]):
        story.append(Spacer(1, 1.5*inch))
        story.append(Paragraph("AllHealth CHOICE", self.S["CoverTitle"]))
        story.append(Spacer(1, 0.2*inch))
        story.append(HRFlowable(width="80%", thickness=2, color=TEAL, hAlign="CENTER"))
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph("Population Health Management Report", self.S["CoverSub"]))
        story.append(Spacer(1, 0.8*inch))
        if meta:
            ms = meta.get("medical_start_date", "")
            me = meta.get("medical_end_date", "")
            ps = meta.get("pharmacy_start_date", "")
            pe = meta.get("pharmacy_end_date", "")
            if ms or me:
                story.append(Paragraph(f"Medical Data: {ms} to {me}", self.S["CoverSub"]))
            if ps or pe:
                story.append(Paragraph(f"Pharmacy Data: {ps} to {pe}", self.S["CoverSub"]))
        story.append(Spacer(1, 0.5*inch))
        story.append(Paragraph("Prepared for:", self.S["CoverSub"]))
        story.append(Paragraph(client_name, self.S["CoverClient"]))
        story.append(PageBreak())

    def _section_header_table(self, title: str) -> Table:
        """Navy header bar containing the section title."""
        t = Table([[Paragraph(title, self.S["SectionHead"])]], colWidths=[7.3*inch])
        t.setStyle(TableStyle([
            ("BACKGROUND", (0,0), (-1,-1), NAVY),
            ("TOPPADDING",    (0,0), (-1,-1), 8),
            ("BOTTOMPADDING", (0,0), (-1,-1), 8),
            ("LEFTPADDING",   (0,0), (-1,-1), 10),
        ]))
        return t

    def _executive_summary(self, story, risk_cd: Optional[ChartData]):
        hdr = self._section_header_table("Executive Summary")
        hdr._toc_entry = "Executive Summary"
        story.append(hdr)
        story.append(Spacer(1, 0.1*inch))

        intro = (
            "The following report is the result of an analysis of archival medical and pharmacy "
            "utilization data. The intent of this analysis is to yield a better understanding of "
            "the epidemiology currently influencing this population and to suggest population health "
            "management opportunities. Archival data was processed through proprietary algorithms to "
            "properly risk-stratify the population."
        )
        story.append(Paragraph(intro, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        # Disease group definition table (static, matches Long County)
        story.append(Paragraph("Disease Group Definitions:", self.S["SubHead"]))
        def_data = [
            ["Disease Group", "Definition"],
            ["Group 1", "No chronic disease and less than $1,500 in medical expenditures per 12 months"],
            ["Group 2", "No chronic disease and $1,500 or more in medical expenditures per 12 months"],
            ["Group 3", "One Chronic Disease"],
            ["Group 4", "Two Chronic Diseases"],
            ["Group 5", "Three Chronic Diseases"],
            ["Group 6", "Four Chronic Diseases"],
            ["Group 7", "Five or More Chronic Diseases"],
        ]
        t = self._make_table(def_data, col_widths=[1.4*inch, 5.9*inch])
        story.append(t)
        story.append(Spacer(1, 0.15*inch))

        # Dynamic Risk Group Summary table from Snowflake
        if risk_cd and risk_cd.data:
            story.append(Paragraph("Risk Group Summary:", self.S["SubHead"]))
            pivot = self._pivot_risk_data(risk_cd.data)
            story.append(pivot)

        story.append(PageBreak())

    def _pivot_risk_data(self, rows) -> Table:
        """Pivot risk group rows into Year columns."""
        years = sorted({str(r.get("FILE_YEAR", "")) for r in rows})
        groups = sorted({str(r.get("RISK_GROUP", "")) for r in rows})
        # Build lookup
        lookup: Dict[tuple, dict] = {}
        for r in rows:
            lookup[(str(r["RISK_GROUP"]), str(r["FILE_YEAR"]))] = r

        # Header rows
        header1 = ["Risk Group"] + [y for y in years for _ in range(3)]
        header2 = [""] + ["Total $", "Mean $", "N"] * len(years)
        data = [header1, header2]
        for g in groups:
            row = [g]
            for y in years:
                r = lookup.get((g, y), {})
                row += [
                    f"${float(r.get('TOTAL_AMT') or 0):,.0f}",
                    f"${float(r.get('MEAN_AMT') or 0):,.0f}",
                    f"{int(r.get('N') or 0):,}",
                ]
            data.append(row)

        ncols = len(header1)
        col_w = [1.1*inch] + [0.87*inch]*(ncols-1)
        t = Table(data, colWidths=col_w, repeatRows=2)
        style = [
            ("BACKGROUND",    (0,0), (-1,1), NAVY),
            ("TEXTCOLOR",     (0,0), (-1,1), WHITE),
            ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
            ("FONTSIZE",      (0,0), (-1,-1), 7),
            ("ALIGN",         (0,0), (-1,-1), "CENTER"),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0,2), (-1,-1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0,0), (-1,-1), 4),
            ("BOTTOMPADDING", (0,0), (-1,-1), 4),
        ]
        # Span year headers
        for i, _ in enumerate(years):
            c = 1 + i*3
            style.append(("SPAN", (c,0), (c+2,0)))
        t.setStyle(TableStyle(style))
        return t

    def _section_page(self, story, num: int, title: str,
                      key_finding: str, recommendation: str,
                      cd: Optional[ChartData], chart_file: Optional[str]):
        # Section header bar
        hdr = self._section_header_table(f"Section {num}: {title}")
        hdr._toc_entry = f"Section {num}: {title}"
        story.append(hdr)
        story.append(Spacer(1, 0.1*inch))

        # Data table
        if cd and cd.data:
            self._render_data_table(story, cd)
            story.append(Spacer(1, 0.1*inch))

        # Chart image
        if chart_file and Path(chart_file).exists():
            story.append(RLImage(chart_file, width=6.5*inch, height=3.0*inch))
            story.append(Spacer(1, 0.1*inch))

        # Demographics pyramid (text fallback)
        if cd and cd.chart_type == "pyramid" and cd.data:
            self._render_pyramid_table(story, cd)
            story.append(Spacer(1, 0.1*inch))

        # Key Finding box (teal background)
        self._key_finding_box(story, key_finding)
        story.append(Spacer(1, 0.08*inch))

        # Recommended Solution
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(recommendation, self.S["Body"]))
        story.append(PageBreak())

    def _render_data_table(self, story, cd: ChartData):
        if not cd.data:
            return
        headers = [k.replace("_", " ").title() for k in cd.data[0].keys()]
        rows_out = [headers]
        for row in cd.data[:40]:  # cap at 40 rows
            formatted = []
            for k, v in row.items():
                kl = k.lower()
                if v is None:
                    formatted.append("—")
                elif isinstance(v, float) or ("amt" in kl and isinstance(v, (int, float))):
                    formatted.append(f"${float(v):,.2f}")
                elif isinstance(v, int) and "n" == kl:
                    formatted.append(f"{v:,}")
                elif "pct" in kl or "mpr" in kl or "rate" in kl:
                    formatted.append(f"{float(v):.1f}%")
                else:
                    formatted.append(str(v))
            rows_out.append(formatted)

        ncols = len(headers)
        col_w = [7.3 * inch / ncols] * ncols
        t = self._make_table(rows_out, col_widths=col_w)
        story.append(t)

    def _render_pyramid_table(self, story, cd: ChartData):
        """Age/gender breakdown table as proxy for population pyramid."""
        age_groups = sorted({str(r.get("AGE_GROUP","")) for r in cd.data})
        genders = sorted({str(r.get("GENDER","")) for r in cd.data})
        lookup = {(str(r["AGE_GROUP"]), str(r["GENDER"])): int(r.get("N") or 0) for r in cd.data}
        headers = ["Age Group"] + genders
        rows_out = [headers]
        for ag in age_groups:
            rows_out.append([ag] + [f"{lookup.get((ag,g),0):,}" for g in genders])
        ncols = len(headers)
        col_w = [7.3 * inch / ncols] * ncols
        story.append(self._make_table(rows_out, col_widths=col_w))

    def _make_table(self, data, col_widths=None) -> Table:
        t = Table(data, colWidths=col_widths, repeatRows=1, hAlign="LEFT")
        t.setStyle(TableStyle([
            ("BACKGROUND",    (0,0), (-1,0), NAVY),
            ("TEXTCOLOR",     (0,0), (-1,0), WHITE),
            ("FONTNAME",      (0,0), (-1,0), "Helvetica-Bold"),
            ("FONTNAME",      (0,1), (-1,-1), "Helvetica"),
            ("FONTSIZE",      (0,0), (-1,-1), 8),
            ("ALIGN",         (0,0), (-1,-1), "CENTER"),
            ("ALIGN",         (0,0), (0,-1), "LEFT"),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0,1), (-1,-1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0,0), (-1,-1), 4),
            ("BOTTOMPADDING", (0,0), (-1,-1), 4),
            ("LEFTPADDING",   (0,0), (-1,-1), 5),
        ]))
        return t

    def _key_finding_box(self, story, text: str):
        data = [[
            Paragraph("Key Finding:", self.S["FindingLabel"]),
            Paragraph(text, self.S["FindingText"]),
        ]]
        t = Table(data, colWidths=[1.0*inch, 6.3*inch])
        t.setStyle(TableStyle([
            ("BACKGROUND",    (0,0), (-1,-1), TEAL),
            ("TOPPADDING",    (0,0), (-1,-1), 8),
            ("BOTTOMPADDING", (0,0), (-1,-1), 8),
            ("LEFTPADDING",   (0,0), (-1,-1), 8),
            ("VALIGN",        (0,0), (-1,-1), "TOP"),
        ]))
        story.append(t)