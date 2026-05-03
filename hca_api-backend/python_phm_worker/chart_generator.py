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

            # 3. Executive Summary
            self._executive_summary(story, charts.get("risk_groups"))
            story.append(PageBreak())

            # 4. Sections 2, 3, 4
            self._section_2_demographics(story, charts)
            story.append(PageBreak())
            self._section_3_medical_by_year(story, charts)
            story.append(PageBreak())
            self._section_4_medical_by_quarter(story, charts)
            story.append(PageBreak())

            # 5. Sections 5, 6
            self._section_5_employee_expenditures(story, charts)
            story.append(PageBreak())
            self._section_6_gender_expenditures(story, charts)

            doc.multiBuild(story)
            self.logger.info(f"PDF generated: {fp}")
            return str(fp)
        except Exception as e:
            self.logger.error(f"PDF generation failed: {e}", exc_info=True)
            return None

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
    def _fetch_quickchart(self, cfg: dict, filename: str) -> Optional[str]:
        import json, urllib.parse, urllib.request
        try:
            s = json.dumps(cfg, separators=(",",":"))
            url = f"https://quickchart.io/chart?c={urllib.parse.quote(s, safe='')}&w=700&h=350&f=png"
            fp = self.output_dir / filename
            for _ in range(3):
                try:
                    resp = urllib.request.urlopen(url, timeout=30)
                    fp.write_bytes(resp.read())
                    return str(fp)
                except:
                    pass
        except:
            pass
        return None

    def _section_2_demographics(self, story, charts):
        story.append(self._section_header_table("2. Demographic Information (Age and Gender)"))
        story.append(Spacer(1, 0.2*inch))
        
        # Chart 1: Mean Age and N for Total Population
        story.append(Paragraph("• The mean (average) age for the total population (including Employees, Spouses, and Dependents) was:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        cd_rel = charts.get("demographics_rel_age")
        if cd_rel and cd_rel.data:
            years = sorted(list(set(str(r["YR"]) for r in cd_rel.data)))
            emp_age = [float(next((r["MEAN_AGE"] for r in cd_rel.data if str(r["YR"])==y and r["RELATIONSHIP"]=='EMPLOYEE'), 0)) for y in years]
            emp_n = [int(next((r["N"] for r in cd_rel.data if str(r["YR"])==y and r["RELATIONSHIP"]=='EMPLOYEE'), 0)) for y in years]
            dep_age = [float(next((r["MEAN_AGE"] for r in cd_rel.data if str(r["YR"])==y and r["RELATIONSHIP"]=='DEPENDENT'), 0)) for y in years]
            dep_n = [int(next((r["N"] for r in cd_rel.data if str(r["YR"])==y and r["RELATIONSHIP"]=='DEPENDENT'), 0)) for y in years]
            spo_age = [float(next((r["MEAN_AGE"] for r in cd_rel.data if str(r["YR"])==y and r["RELATIONSHIP"]=='SPOUSE'), 0)) for y in years]
            spo_n = [int(next((r["N"] for r in cd_rel.data if str(r["YR"])==y and r["RELATIONSHIP"]=='SPOUSE'), 0)) for y in years]
            
            cfg1 = {
                "type": "bar",
                "data": {
                    "labels": years,
                    "datasets": [
                        {"type": "line", "label": "DEPENDENT - AVERAGE AGE", "data": dep_age, "borderColor": "#888", "fill": False},
                        {"type": "line", "label": "DEPENDENT - N", "data": dep_n, "borderColor": "#555", "fill": False},
                        {"type": "bar", "label": "EMPLOYEE - AVERAGE AGE", "data": emp_age, "backgroundColor": "#5A9AD4"},
                        {"type": "bar", "label": "EMPLOYEE - N", "data": emp_n, "backgroundColor": "#2B3A5A"},
                        {"type": "line", "label": "SPOUSE - AVERAGE AGE", "data": spo_age, "borderColor": "#ccc", "fill": False},
                        {"type": "line", "label": "SPOUSE - N", "data": spo_n, "borderColor": "#aaa", "fill": False}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "top", "anchor": "end", "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img1 = self._fetch_quickchart(cfg1, "chart_demo1.png")
            if img1:
                story.append(RLImage(img1, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))
                
        # Chart 2: Employees only
        story.append(Paragraph("• For employees only, the mean age was:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        if cd_rel and cd_rel.data:
            cfg2 = {
                "type": "horizontalBar",
                "data": {
                    "labels": years,
                    "datasets": [
                        {"label": "EMPLOYEE - AVERAGE AGE", "data": emp_age, "backgroundColor": "#227EE4"},
                        {"label": "EMPLOYEE - N", "data": emp_n, "backgroundColor": "#8192A6"}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end", "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img2 = self._fetch_quickchart(cfg2, "chart_demo2.png")
            if img2:
                story.append(RLImage(img2, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))
                
        story.append(PageBreak())
        
        # Chart 3: Number of Individuals by Age - Total
        story.append(Paragraph("• Number of Individuals by Age - Total Population (Employee, Spouse & Dependent):", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        cd_age = charts.get("demographics_age_group")
        if cd_age and cd_age.data:
            lbls = [r["AGE_GROUP"] for r in cd_age.data]
            mean_age = [float(r["MEAN_AGE"] or 0) for r in cd_age.data]
            n_val = [int(r["N"] or 0) for r in cd_age.data]
            
            cfg3 = {
                "type": "horizontalBar",
                "data": {
                    "labels": lbls,
                    "datasets": [
                        {"type": "line", "label": "MEMBER AVERAGE AGE", "data": mean_age, "borderColor": "#666", "fill": False},
                        {"type": "horizontalBar", "label": "N", "data": n_val, "backgroundColor": "#227EE4"}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end", "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img3 = self._fetch_quickchart(cfg3, "chart_demo3.png")
            if img3:
                story.append(RLImage(img3, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))
                
        # Chart 4: Gender breakdown
        story.append(Paragraph("• The gender breakdown for the total population (Employee, Spouse & Dependent) was:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        cd_gen = charts.get("demographics_gender_pct")
        if cd_gen and cd_gen.data:
            years_g = sorted(list(set(str(r["YR"]) for r in cd_gen.data)))
            f_n = [int(next((r["N"] for r in cd_gen.data if str(r["YR"])==y and r["GENDER"]=='F'), 0)) for y in years_g]
            m_n = [int(next((r["N"] for r in cd_gen.data if str(r["YR"])==y and r["GENDER"]=='M'), 0)) for y in years_g]
            
            cfg4 = {
                "type": "horizontalBar",
                "data": {
                    "labels": years_g,
                    "datasets": [
                        {"label": "Female - N", "data": f_n, "backgroundColor": "#2B3A5A"},
                        {"label": "Male - N", "data": m_n, "backgroundColor": "#227EE4"}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end", "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img4 = self._fetch_quickchart(cfg4, "chart_demo4.png")
            if img4:
                story.append(RLImage(img4, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))

    def _section_3_medical_by_year(self, story, charts):
        story.append(self._section_header_table("3. Overall Medical Expenditures by Year"))
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph("Medical expenditures* (based on paid claims) were as follows:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        cd_med = charts.get("med_by_year")
        if cd_med and cd_med.data:
            years = [str(r["YR"]) for r in cd_med.data]
            tot = [float(r["TOTAL_AMT"] or 0) for r in cd_med.data]
            mean = [float(r["MEAN_AMT"] or 0) for r in cd_med.data]
            n_val = [int(r["N"] or 0) for r in cd_med.data]
            
            # Table
            t_data = [["Medical records Reporting Year", "Medical records TOTAL $", "Medical records MEAN $", "Medical records N"]]
            sum_tot = 0
            sum_n = 0
            for i, y in enumerate(years):
                t_data.append([y, f"${tot[i]:,.0f}", f"${mean[i]:,.0f}", f"{n_val[i]:,}"])
                sum_tot += tot[i]
                sum_n += n_val[i]
                
            t_data.append(["Total", f"${sum_tot:,.0f}", f"${(sum_tot/sum_n if sum_n else 0):,.0f}", f"{sum_n:,}"])
            t = Table(t_data, colWidths=[2.5*inch, 1.6*inch, 1.6*inch, 1.6*inch])
            style = [
                ("BACKGROUND",    (0,0), (-1,0), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,0), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,0), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 8),
                ("ALIGN",         (1,0), (-1,-1), "RIGHT"),
                ("ALIGN",         (0,0), (0,-1), "LEFT"),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0,1), (-1,-1), [WHITE, LTGREY]),
                ("TOPPADDING",    (0,0), (-1,-1), 4),
                ("BOTTOMPADDING", (0,0), (-1,-1), 4),
            ]
            t.setStyle(TableStyle(style))
            story.append(t)
            story.append(Spacer(1, 0.1*inch))
            
            story.append(Paragraph("*Medical expenditures do not include pharmacy-related expenditures.", ParagraphStyle("small", parent=self.S["Body"], fontSize=8, fontName="Helvetica-Oblique")))
            story.append(Paragraph("**N is a statistical notation that identifies the number of people in a population.", ParagraphStyle("small", parent=self.S["Body"], fontSize=8, fontName="Helvetica-Oblique")))
            story.append(Spacer(1, 0.2*inch))
            
            # Chart 1: Total
            story.append(Paragraph("<b>Total Amount Paid - Medical - Total Population</b>", ParagraphStyle("cen", parent=self.S["Body"], alignment=1)))
            cfg_tot = {
                "type": "horizontalBar",
                "data": {
                    "labels": years,
                    "datasets": [
                        {"label": "TOTAL $", "data": tot, "backgroundColor": "#227EE4"},
                        {"label": "N", "data": n_val, "backgroundColor": "#8192A6"}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end", "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img_tot = self._fetch_quickchart(cfg_tot, "chart_medyr_tot.png")
            if img_tot:
                story.append(RLImage(img_tot, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))
                
            story.append(PageBreak())
            
            # Chart 2: Mean
            story.append(Paragraph("<b>Mean Amount Paid - Medical - Total Population</b>", ParagraphStyle("cen", parent=self.S["Body"], alignment=1)))
            cfg_mean = {
                "type": "horizontalBar",
                "data": {
                    "labels": years,
                    "datasets": [
                        {"label": "MEAN $", "data": mean, "backgroundColor": "#227EE4"},
                        {"label": "N", "data": n_val, "backgroundColor": "#8192A6"}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end", "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img_mean = self._fetch_quickchart(cfg_mean, "chart_medyr_mean.png")
            if img_mean:
                story.append(RLImage(img_mean, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))

    def _section_4_medical_by_quarter(self, story, charts):
        story.append(self._section_header_table("4. Overall Medical Expenditures by Quarter"))
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph("Medical expenditures were as follows:", self.S["Body"]))
        story.append(Spacer(1, 0.2*inch))
        
        cd_q = charts.get("med_by_quarter")
        if cd_q and cd_q.data:
            lbls = [f"{r['YR']}-Q{r['QTR']}" for r in cd_q.data]
            tot = [float(r["TOTAL_AMT"] or 0) for r in cd_q.data]
            mean = [float(r["MEAN_AMT"] or 0) for r in cd_q.data]
            n_val = [int(r["N"] or 0) for r in cd_q.data]
            
            story.append(Paragraph("<b>Total Amount Paid - Medical - Total Population</b>", ParagraphStyle("cen", parent=self.S["Body"], alignment=1)))
            cfg_tot = {
                "type": "bar",
                "data": {
                    "labels": lbls,
                    "datasets": [
                        {"type": "bar", "label": "TOTAL $", "data": tot, "backgroundColor": "#227EE4"},
                        {"type": "line", "label": "N", "data": n_val, "borderColor": "#8192A6", "fill": False}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "top", "anchor": "end", "font": {"weight": "bold", "size": 8}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img_tot = self._fetch_quickchart(cfg_tot, "chart_medq_tot.png")
            if img_tot:
                story.append(RLImage(img_tot, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))
                
            story.append(Paragraph("<b>Mean Amount Paid - Medical - Total Population</b>", ParagraphStyle("cen", parent=self.S["Body"], alignment=1)))
            cfg_mean = {
                "type": "bar",
                "data": {
                    "labels": lbls,
                    "datasets": [
                        {"type": "bar", "label": "MEAN $", "data": mean, "backgroundColor": "#227EE4"},
                        {"type": "line", "label": "N", "data": n_val, "borderColor": "#8192A6", "fill": False}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "top", "anchor": "end", "font": {"weight": "bold", "size": 8}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img_mean = self._fetch_quickchart(cfg_mean, "chart_medq_mean.png")
            if img_mean:
                story.append(RLImage(img_mean, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))

    def _section_5_employee_expenditures(self, story, charts):
        story.append(self._section_header_table("5. Employee/ Spouse/ Dependent Expenditures"))
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph("Medical expenditures related to Employees, Spouses, and Dependents were as follows:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        cd_emp = charts.get("emp_spouse_dep")
        if cd_emp and cd_emp.data:
            years = sorted(list(set(str(r["YR"]) for r in cd_emp.data)))
            
            data_by_rel = {"EMPLOYEE": {"tot": [], "mean": [], "n": []}, 
                           "SPOUSE": {"tot": [], "mean": [], "n": []}, 
                           "DEPENDENT": {"tot": [], "mean": [], "n": []}}
            
            for rel in ["EMPLOYEE", "SPOUSE", "DEPENDENT"]:
                for y in years:
                    row = next((r for r in cd_emp.data if str(r["YR"]) == y and str(r.get("RELATIONSHIP","")) == rel), None)
                    data_by_rel[rel]["tot"].append(float(row["TOTAL_AMT"]) if row and row.get("TOTAL_AMT") is not None else 0)
                    data_by_rel[rel]["mean"].append(float(row["MEAN_AMT"]) if row and row.get("MEAN_AMT") is not None else 0)
                    n_val = int(row["N"]) if row and row.get("N") is not None else 0
                    data_by_rel[rel]["n"].append(n_val)
                    if data_by_rel[rel]["mean"][-1] == 0 and n_val > 0:
                         data_by_rel[rel]["mean"][-1] = data_by_rel[rel]["tot"][-1] / n_val
            
            headers = ["Medical records\\nReporting Year"] + [y for y in years]
            t_data = [headers]
            
            sub_headers = ["Medical records\\nRELATIONSHIP TO\\nEMPLOYEE"]
            for _ in years:
                sub_headers.extend(["Medical\\nrecords\\nTOTAL $", "Medical\\nrecords\\nMEAN $", "Medical\\nrecords\\nN"])
            
            t_data[0] = ["Medical records\\nReporting Year"]
            for y in years:
                t_data[0].extend([y, "", ""])
            
            t_data.append(sub_headers)
            
            for rel in ["EMPLOYEE", "SPOUSE", "DEPENDENT"]:
                row = [rel]
                for i in range(len(years)):
                    row.extend([f"${data_by_rel[rel]['tot'][i]:,.0f}", f"${data_by_rel[rel]['mean'][i]:,.0f}", f"{data_by_rel[rel]['n'][i]:,}"])
                t_data.append(row)
                
            col_widths = [1.5*inch] + [0.65*inch] * (len(years) * 3)
            t = Table(t_data, colWidths=col_widths)
            
            style = [
                ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 7),
                ("ALIGN",         (1,0), (-1,-1), "RIGHT"),
                ("ALIGN",         (0,0), (0,-1), "LEFT"),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("SPAN",          (0,0), (0,1)),
            ]
            col_idx = 1
            for _ in years:
                style.append(("SPAN", (col_idx, 0), (col_idx+2, 0)))
                col_idx += 3
            t.setStyle(TableStyle(style))
            story.append(t)
            story.append(Spacer(1, 0.2*inch))
            
            story.append(Paragraph("<b>Total Amount Paid - Medical - Employee / Spouse / Dependent</b>", ParagraphStyle("cen", parent=self.S["Body"], alignment=1)))
            cfg_emp = {
                "type": "horizontalBar",
                "data": {
                    "labels": years,
                    "datasets": [
                        {"label": "EMPLOYEE - Total $", "data": data_by_rel["EMPLOYEE"]["tot"], "backgroundColor": "#2B3A5A"},
                        {"label": "EMPLOYEE - Mean $", "data": data_by_rel["EMPLOYEE"]["mean"], "backgroundColor": "#227EE4"},
                        {"type": "line", "label": "EMPLOYEE - N", "data": data_by_rel["EMPLOYEE"]["n"], "borderColor": "#8192A6", "fill": False}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end", "font": {"weight": "bold", "size": 8}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img_emp = self._fetch_quickchart(cfg_emp, "chart_sec5.png")
            if img_emp:
                story.append(RLImage(img_emp, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))

    def _section_6_gender_expenditures(self, story, charts):
        story.append(self._section_header_table("6. Gender Related Expenditures"))
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph("Medical expenditures related to Males and Females were as follows:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        cd_gen = charts.get("gender_exp")
        if cd_gen and cd_gen.data:
            years = sorted(list(set(str(r["YR"]) for r in cd_gen.data)))
            
            data_by_gen = {"F": {"tot": [], "mean": [], "n": []}, 
                           "M": {"tot": [], "mean": [], "n": []}}
            
            for gen in ["F", "M"]:
                for y in years:
                    row = next((r for r in cd_gen.data if str(r["YR"]) == y and str(r.get("GENDER","")) == gen), None)
                    tot = float(row["TOTAL_AMT"]) if row and row.get("TOTAL_AMT") is not None else 0
                    n_val = int(row["N"]) if row and row.get("N") is not None else 0
                    mean = tot / n_val if n_val > 0 else 0
                    data_by_gen[gen]["tot"].append(tot)
                    data_by_gen[gen]["n"].append(n_val)
                    data_by_gen[gen]["mean"].append(mean)
            
            t_data = [["Medical records\\nMEMBER GENDER"]]
            for g in ["F", "M"]:
                t_data[0].extend([g, "", ""])
            
            sub_headers = ["Medical records\\nReporting Year"]
            for _ in ["F", "M"]:
                sub_headers.extend(["Medical\\nrecords\\nTOTAL $", "Medical\\nrecords\\nMEAN $", "Medical\\nrecords\\nN"])
            t_data.append(sub_headers)
            
            for i, y in enumerate(years):
                row = [y]
                for g in ["F", "M"]:
                    row.extend([f"${data_by_gen[g]['tot'][i]:,.0f}", f"${data_by_gen[g]['mean'][i]:,.0f}", f"{data_by_gen[g]['n'][i]:,}"])
                t_data.append(row)
                
            col_widths = [1.5*inch] + [0.95*inch] * 6
            t = Table(t_data, colWidths=col_widths)
            
            style = [
                ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 8),
                ("ALIGN",         (1,0), (-1,-1), "RIGHT"),
                ("ALIGN",         (0,0), (0,-1), "LEFT"),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("SPAN",          (0,0), (0,1)),
                ("SPAN",          (1,0), (3,0)),
                ("SPAN",          (4,0), (6,0)),
            ]
            t.setStyle(TableStyle(style))
            story.append(t)
            story.append(Spacer(1, 0.2*inch))
            
            story.append(Paragraph("<b>Total Amount Paid - Gender - Total Population</b>", ParagraphStyle("cen", parent=self.S["Body"], alignment=1)))
            cfg_gen = {
                "type": "horizontalBar",
                "data": {
                    "labels": years,
                    "datasets": [
                        {"label": "Female - Total $", "data": data_by_gen["F"]["tot"], "backgroundColor": "#2B3A5A"},
                        {"type": "line", "label": "Female - N", "data": data_by_gen["F"]["n"], "borderColor": "#8192A6", "fill": False},
                        {"label": "Male - Total $", "data": data_by_gen["M"]["tot"], "backgroundColor": "#227EE4"},
                        {"type": "line", "label": "Male - N", "data": data_by_gen["M"]["n"], "borderColor": "#5A9AD4", "fill": False}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end", "font": {"weight": "bold", "size": 8}},
                        "legend": {"position": "bottom"}
                    }
                }
            }
            img_gen = self._fetch_quickchart(cfg_gen, "chart_sec6.png")
            if img_gen:
                story.append(RLImage(img_gen, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))
