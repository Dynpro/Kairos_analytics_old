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
            24, "Medication Compliance",
            "Low MPR indicates medication non-adherence, a key driver of avoidable complications.",
            "Deploy pharmacist-led adherence programs targeting members with MPR below 80%."
        ),
        "risk_groups_sec11": (
            11, "Disease Group Risk Stratification",
            "A small proportion of high-risk members account for a disproportionate share of costs.",
            "Implement intensive case management for Group 5–7 members."
        ),
        "lifestyle": (
            12, "Expenditures Related to Lifestyle Modifiable & Preventive Utilization",
            "Lifestyle-modifiable conditions represent a significant and preventable share of costs.",
            "Implement evidence-based wellness programs targeting obesity, tobacco, and stress."
        ),
        "health_disparities": (
            13, "Estimated Lost Time & Cost due to Health Disparities",
            "High-frequency unspecified diagnoses indicate poor patient-physician communication.",
            "Implement personal electronic health records and encourage resolved diagnoses."
        ),
        "breast_screening": (
            14, "Preventive Screening Compliance",
            "All cancer screening rates are significantly below HEDIS national benchmarks.",
            "Increase screening awareness through outreach, mailings, and benefit incentives."
        ),
        "screening_value": (
            15, "Value of Preventive Screenings",
            "Preventive screenings identified cancer diagnoses enabling early-stage treatment.",
            "Continue investing in preventive screening programs and track outcomes."
        ),
        "msk_work": (
            16, "Potentially Work-Related Musculoskeletal Expenditures",
            "MSK conditions are a leading driver of medical spend and lost productivity.",
            "Implement pre-employment physical testing and ergonomic assessment programs."
        ),
        "catastrophic": (
            17, "Catastrophic Claims",
            "Catastrophic claimants drive disproportionate medical costs.",
            "Engage catastrophic claimants with dedicated case managers."
        ),
        "inpatient_er": (
            18, "Inpatient, Outpatient & Emergency Room Expenditures",
            "Avoidable inpatient stays and ER visits represent significant cost-reduction opportunities.",
            "Implement ER diversion programs and post-discharge follow-up protocols."
        ),
        "avoidable_er": (
            19, "Avoidable Emergency Room Visits",
            "Avoidable ER visits represent unnecessary cost and are largely preventable.",
            "Assign PCPs to frequent ER utilizers; distribute self-care guides."
        ),
        "pcp_specialty": (
            20, "Primary Care Physician & Specialty Expenditures",
            "Specialist utilization without PCP coordination drives unnecessary cost.",
            "Encourage PCP-led coordination and appropriate specialty referral pathways."
        ),
        "pharm_by_year": (
            21, "Overall Pharmacy Expenditures by Year",
            "Pharmacy costs trend upward driven by specialty and brand drug utilization.",
            "Implement step-therapy and generic substitution programs."
        ),
        "pharm_by_quarter": (
            22, "Overall Pharmacy Expenditures by Quarter",
            "Quarterly pharmacy patterns reveal refill timing and adherence opportunities.",
            "Align pharmacy outreach with high-spend quarters to improve adherence."
        ),
        "pharm_relationship": (
            23, "Employee/ Spouse/ Dependent Pharmacy Expenditures",
            "Dependent pharmacy spend may indicate pediatric or chronic prescription trends.",
            "Target pharmacy benefit utilization programs by relationship tier."
        ),
        "brand_generic": (
            25, "Brand vs. Generic Medication Usage",
            "High brand drug utilization presents an immediate cost-reduction opportunity.",
            "Enforce generic-first policies and educate prescribers on generic options."
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

    def _wrap_cell(self, text: Any, font_name="Helvetica", font_size=8, alignment=TA_LEFT, text_color=BLACK):
        if text is None: return ""
        # Clean up literal \n and \\n for Paragraph. 
        # Also handle pipe | characters by adding a space or breaking to allow wrapping.
        s = str(text).replace("\\n", "<br/>").replace("\n", "<br/>").replace("|", "<br/>")
        if not s: return ""
        style = ParagraphStyle(
            "CellWrap",
            fontName=font_name,
            fontSize=font_size,
            leading=font_size * 1.4,  # Increased leading for better vertical separation
            alignment=alignment,
            textColor=text_color,
            wordWrap="LTR" # Ensure standard word wrap
        )
        return Paragraph(s, style)

    # ── Public API ────────────────────────────────────────────────────────────

    def generate_pdf(self, report_id: int, report_name: str, client_name: str,
                     years: List[int], charts: Dict[str, ChartData],
                     chart_files: Dict[str, str],
                     metadata: Optional[Dict[str, Any]] = None) -> Optional[str]:
        self.report_years = sorted([str(y) for y in years])
        year_display = years[-1] if years else 2025
        fp = self.output_dir / f"PHM_Report_{report_id}_{year_display}.pdf"
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
            self._executive_summary(story, charts)

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
            story.append(PageBreak())

            # 6. Sections 7–10
            self._section_7_diagnostic_categories(story, charts)
            story.append(PageBreak())
            self._section_8_chronic_diseases(story, charts)
            story.append(PageBreak())
            self._section_9_diabetes_expenditures(story, charts)
            story.append(PageBreak())
            self._section_10_diabetes_ebm(story, charts)
            story.append(PageBreak())

            # 7. Sections 11–20
            self._section_11_risk_groups(story, charts)
            story.append(PageBreak())
            self._section_12_lifestyle(story, charts)
            story.append(PageBreak())
            self._section_13_health_disparities(story, charts)
            story.append(PageBreak())
            self._section_14_preventive_screening(story, charts)
            story.append(PageBreak())
            self._section_15_screening_value(story, charts)
            story.append(PageBreak())
            self._section_16_musculoskeletal(story, charts)
            story.append(PageBreak())
            self._section_17_catastrophic(story, charts)
            story.append(PageBreak())
            self._section_18_inpatient_er(story, charts)
            story.append(PageBreak())
            self._section_19_avoidable_er(story, charts)
            story.append(PageBreak())
            self._section_20_pcp_specialty(story, charts)
            story.append(PageBreak())

            # 8. Sections 21–25 (Pharmacy)
            self._section_21_pharmacy_by_year(story, charts)
            story.append(PageBreak())
            self._section_22_pharmacy_by_quarter(story, charts)
            story.append(PageBreak())
            self._section_23_pharmacy_relationship(story, charts)
            story.append(PageBreak())
            self._section_24_medication_mpr(story, charts)
            story.append(PageBreak())
            self._section_25_brand_generic(story, charts)
            story.append(PageBreak())

            # 9. Appendices 1–4
            self._appendix_1_disease_groups(story)
            story.append(PageBreak())
            self._appendix_2_diagnostic_categories(story)
            story.append(PageBreak())
            self._appendix_3_diabetes_complications(story)
            story.append(PageBreak())
            self._appendix_4_screening_eligibility(story)

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

    def _executive_summary(self, story, charts):
        hdr = self._section_header_table("1. Executive Summary")
        hdr._toc_entry = "1. Executive Summary"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))

        # Introduction
        story.append(Paragraph("<b>Introduction:</b>", self.S["SubHead"]))
        story.append(Spacer(1, 0.1*inch))
        intro_text1 = (
            "The following report is the result of an analysis of archival medical and pharmacy "
            "utilization data. The intent of this analysis is to yield a better understanding of "
            "the epidemiology currently influencing this population and to suggest population health "
            "management opportunities that can address the specific risk impacting this population. "
            "In order to accomplish this task, archival data was processed through proprietary "
            "algorithms in order to properly risk-stratify the population. The risk of a "
            "population has a direct relationship to current and future spending patterns. "
            "Variables that are the building blocks of risk and/or disease include, but are not limited to:"
        )
        story.append(Paragraph(intro_text1, self.S["Body"]))
        
        # Bullet list
        bullet_style = ParagraphStyle("Bullet", parent=self.S["Body"], leftIndent=20, bulletIndent=10)
        story.append(Paragraph("<bullet>&bull;</bullet>Age, Gender, Lifestyle, Genetics, Chronic Illness, Co-Morbidities, Multi-Morbidities, Medication Compliance/Non-Compliance, Compliance/Non-Compliance to Evidence-Based Guidelines, Gaps in Care, etc.", bullet_style))
        story.append(Spacer(1, 0.1*inch))

        intro_text2 = (
            "The majority of the aforementioned variables were utilized to investigate risk "
            "stratifications within the population. The overall health of a population is determined "
            "by multiple factors; however, an individual's lifestyle is a powerful predictor of "
            "leading causes of morbidity and disability."
        )
        story.append(Paragraph(intro_text2, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        intro_text3 = "This analysis explored multiple areas of interest within the data, including the following research questions:"
        story.append(Paragraph(intro_text3, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        num_style = ParagraphStyle("NumList", parent=self.S["Body"], leftIndent=20, firstLineIndent=-15)
        questions = [
            "1. What is the cost burden of lifestyle modifiable risk factors within the employee population?",
            "2. What is the relationship of age and gender to various disease states?",
            "3. What are the gaps in care associated with suggested preventive measures for this population?",
            "4. What is the relationship between drug compliance and non-compliance, as related to disease severity?",
            "5. What is the financial burden associated with chronic disease within this population?",
            "6. What is the level of HEDIS compliance (i.e., evidence-based & preventive medicine) within this population?",
            "7. What is the expense related to specific co-morbidities (i.e., hypertension, hyperlipidemia, depression, etc.) within this population?",
            "8. What variables best predict and explain future high spenders within this population?",
            "9. What are actionable solutions that can be implemented to mitigate existing and future health risks?"
        ]
        for q in questions:
            story.append(Paragraph(q, num_style))
        
        story.append(Spacer(1, 0.2*inch))

        story.append(Paragraph("<b>Key Findings and Solutions for Consideration:</b>", self.S["SubHead"]))
        story.append(Spacer(1, 0.1*inch))
        story.append(Paragraph("The following key findings resulted from the analysis of archival health care data.", self.S["Body"]))
        story.append(Spacer(1, 0.2*inch))

        # Key Finding 1: Chronic Disease
        story.append(Paragraph("<b>Key Finding 1: Chronic Disease</b>", self.S["SubHead"]))
        story.append(Spacer(1, 0.1*inch))
        kf1_text = (
            "<b>Key Finding:</b> Patterns of risk generally occur within any given population. In order "
            "to better understand these patterns, the population was risk stratified into five distinct groups:"
        )
        story.append(Paragraph(kf1_text, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        # Disease Group Table
        def_data = [
            ["Group", "Description"],
            ["Disease Group-1", "No chronic disease and less than $1,500 medical expenditures per 12 months"],
            ["Disease Group-2", "No chronic disease and $1,500 or more medical expenditures per 12 months"],
            ["Disease Group-3", "One Chronic Disease"],
            ["Disease Group-4", "Two Chronic Disease"],
            ["Disease Group-5", "Three Chronic Disease"],
            ["Disease Group-6", "Four Chronic Disease"],
            ["Disease Group-7", "Five or More Chronic Disease"],
        ]
        t1 = Table(def_data, colWidths=[1.8*inch, 5.5*inch])
        t1.setStyle(TableStyle([
            ("BACKGROUND",    (0,0), (-1,0), LTBLUE),
            ("TEXTCOLOR",     (0,0), (-1,0), rl_colors.grey),
            ("FONTNAME",      (0,0), (-1,0), "Helvetica-Bold"),
            ("FONTNAME",      (0,1), (-1,-1), "Helvetica"),
            ("FONTSIZE",      (0,0), (-1,-1), 8),
            ("ALIGN",         (0,0), (-1,-1), "LEFT"),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("TEXTCOLOR",     (0,1), (0,-1), rl_colors.black),
            ("TEXTCOLOR",     (1,1), (1,-1), rl_colors.grey),
            ("TOPPADDING",    (0,0), (-1,-1), 4),
            ("BOTTOMPADDING", (0,0), (-1,-1), 4),
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
        ]))
        story.append(t1)
        story.append(Spacer(1, 0.15*inch))

        kf1_post_table = (
            "Total amount paid, Mean amount paid & Total patients (N) within the population for Risk "
            "Groups & Years were as follows (shown in chart below). As you would see in the analysis "
            "below; regarding the economic differences between each group; it reveals that mean "
            "expenditures increased as an individual incrementally progressed from a lower risk group "
            "to higher risk groups; from Group 3 - 7."
        )
        story.append(Paragraph(kf1_post_table, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        story.append(Paragraph(
            "<i>**N is a statistical notation that identifies the number of people in a population. Throughout this report, it is used to indicate the number of individuals incorporated into each analysis.</i>",
            self.S["Body"]
        ))
        
        story.append(PageBreak())

        # Page 4
        risk_cd = charts.get("risk_groups")
        if risk_cd and risk_cd.data:
            pivot = self._pivot_risk_data(risk_cd.data)
            story.append(pivot)
            story.append(Spacer(1, 0.2*inch))

        story.append(Paragraph("Based on the chronic diseases included in the aforementioned Disease Group Risk Stratification, below are the top 3 Chronic diseases across all population who have a chronic disease. It would be estimated that an additional 10 to 15 percent of the population have chronic illness and have not yet been diagnosed, due to gaps in care.", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        story.append(Paragraph("The top 3 most expensive chronic diseases across populations & years were as follows (shown in chart below):", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        # Top 3 most expensive chronic diseases
        cd_chronic = charts.get("chronic_diseases")
        if cd_chronic and cd_chronic.data:
            years = sorted({str(r.get("FILE_YEAR", "")) for r in cd_chronic.data})
            lookup: Dict[tuple, dict] = {}
            cat_totals: Dict[str, float] = {}
            for r in cd_chronic.data:
                c = str(r.get("CHRONIC_CAT", ""))
                cat_totals[c] = cat_totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
                lookup[(c, str(r.get("FILE_YEAR", "")))] = r
            
            cats_by_total = [k for k, _ in sorted(cat_totals.items(), key=lambda x: x[1], reverse=True)[:3]]
            
            # Header rows - simplified to prevent overflow
            hdr1 = [self._wrap_cell("Reporting Year", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1 += [self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey)]
            
            hdr2 = [self._wrap_cell("CHRONIC CATEGORY", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for _ in years:
                hdr2 += [self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)]
            t_data_exp = [hdr1, hdr2]
            
            for cat in cats_by_total:
                row = [self._wrap_cell(cat, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                for y in years:
                    r = lookup.get((cat, y), {})
                    amt = float(r.get("TOTAL_AMT") or 0) if r else 0
                    row.append(self._wrap_cell(f"${amt:,.0f}" if (r and amt) else "$0", font_name="Helvetica", font_size=7, alignment=TA_RIGHT))
                t_data_exp.append(row)
            
            ncols = len(hdr1)
            cat_w = 3.0 * inch
            col_w = [cat_w] + [(7.3 * inch - cat_w) / (ncols - 1)] * (ncols - 1)
            t_exp = Table(t_data_exp, colWidths=col_w, repeatRows=2)
            t_exp.setStyle(TableStyle([
                ("BACKGROUND",    (0, 0), (-1, 1), LTBLUE),
                ("TEXTCOLOR",     (0, 0), (-1, 1), rl_colors.grey),
                ("FONTNAME",      (0, 0), (-1, 1), "Helvetica-Bold"),
                ("FONTSIZE",      (0, 0), (-1, -1), 7),
                ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
                ("TEXTCOLOR",     (0, 2), (-1, -1), rl_colors.grey),
                ("TOPPADDING",    (0, 0), (-1, -1), 3),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 3),
                ("LEFTPADDING",   (0, 0), (-1, -1), 2),
                ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
                ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ]))
            story.append(t_exp)
            story.append(Spacer(1, 0.2*inch))

            story.append(Paragraph("The top 3 most frequent chronic diseases across populations were:", self.S["Body"]))
            story.append(Spacer(1, 0.1*inch))
            
            story.append(PageBreak()) # Next page for the freq table according to Image 3 (Page 5)
            
            # Freq table
            cat_n_totals: Dict[str, int] = {}
            for r in cd_chronic.data:
                c = str(r.get("CHRONIC_CAT", ""))
                cat_n_totals[c] = cat_n_totals.get(c, 0) + int(r.get("N") or 0)
            cats_by_freq = [k for k, _ in sorted(cat_n_totals.items(), key=lambda x: x[1], reverse=True)[:3]]
            
            hdr1_freq = [self._wrap_cell("Reporting Year", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1_freq += [self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey)]
            
            hdr2_freq = [self._wrap_cell("CHRONIC CATEGORY", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for _ in years:
                hdr2_freq += [self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)]
            t_data_freq = [hdr1_freq, hdr2_freq]
            
            for cat in cats_by_freq:
                row = [self._wrap_cell(cat, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                for y in years:
                    r = lookup.get((cat, y), {})
                    n = int(r.get("N") or 0) if r else 0
                    row.append(self._wrap_cell(str(n) if (r and n) else "0", font_name="Helvetica", font_size=7, alignment=TA_RIGHT))
                t_data_freq.append(row)
                
            t_freq = Table(t_data_freq, colWidths=col_w, repeatRows=2)
            t_freq.setStyle(TableStyle([
                ("BACKGROUND",    (0, 0), (-1, 1), LTBLUE),
                ("TEXTCOLOR",     (0, 0), (-1, 1), rl_colors.grey),
                ("FONTNAME",      (0, 0), (-1, 1), "Helvetica-Bold"),
                ("FONTSIZE",      (0, 0), (-1, -1), 7),
                ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
                ("TEXTCOLOR",     (0, 2), (-1, -1), rl_colors.grey),
                ("TOPPADDING",    (0, 0), (-1, -1), 3),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 3),
                ("LEFTPADDING",   (0, 0), (-1, -1), 2),
                ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
                ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ]))
            story.append(t_freq)
            story.append(Spacer(1, 0.2*inch))

        rec_sol_kf1 = (
            "<b>Recommended Solution:</b> The impact of chronic disease, co-morbidities, and disease-specific "
            "complications magnifies the impact of an individual's mean and overall expenditures. This type of "
            "stratification (i.e., the aforementioned Disease Group Risk Stratification) clearly shows that a "
            "relatively similar group of individuals drives a large percentage of overall expenditures. A "
            "population health management strategy that targets the low-risk or emerging risk portion of the "
            "population would potentially yield the highest return on investment.<br/><br/>"
            "The majority of wellness program strategies often do not implement programs that are sensitive to "
            "the clinical side of population health management and just concentrate on lifestyle modification "
            "(e.g., exercise, nutrition, stress management, etc.). However, in order to be effective with the "
            "chronic population, clinical strategies must be a part of the overall population health management "
            "strategy. Further analyses were conducted to identify the importance of chronic disease as a "
            "predictor of future spending."
        )
        story.append(Paragraph(rec_sol_kf1, self.S["Body"]))
        story.append(Spacer(1, 0.2*inch))

        # Key Finding 2: Diabetes Complications and Co-Morbidities
        story.append(Paragraph("<b>Key Finding 2: Diabetes Complications and Co-Morbidities</b>", self.S["SubHead"]))
        story.append(Spacer(1, 0.1*inch))
        kf2_text = "<b>Key Finding:</b> The top three Diabetes-specific complications are shown in the chart below in order of their Total Spend across years."
        story.append(Paragraph(kf2_text, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        cd_comp = charts.get("diabetes_complications")
        if cd_comp and cd_comp.data:
            years = sorted({str(r.get("FILE_YEAR", "")) for r in cd_comp.data})
            lookup: Dict[tuple, dict] = {}
            comp_totals: Dict[str, float] = {}
            for r in cd_comp.data:
                c = str(r.get("COMPLICATION", ""))
                comp_totals[c] = comp_totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
                lookup[(c, str(r.get("FILE_YEAR", "")))] = r
            
            top3_comps = [k for k, _ in sorted(comp_totals.items(), key=lambda x: x[1], reverse=True)[:3]]
            
            hdr1_comp = [self._wrap_cell("Reporting Year", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1_comp += [self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), ""] # Span placeholder
            hdr1_comp.append(self._wrap_cell("Total", font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey))

            hdr2_comp = [self._wrap_cell("DIABETES SPECIFIC COMPLICATIONS", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for _ in years:
                hdr2_comp += [
                    self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
                ]
            hdr2_comp.append(self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey))
            
            t_data_comp = [hdr1_comp, hdr2_comp]
            
            for comp in top3_comps:
                row = [self._wrap_cell(comp, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                grand = 0.0
                for y in years:
                    r = lookup.get((comp, y), {})
                    v = float(r.get("TOTAL_AMT") or 0)
                    n = int(r.get("N") or 0)
                    grand += v
                    row.append(self._wrap_cell(f"${v:,.0f}" if r and v else "$0", font_name="Helvetica", font_size=7, alignment=TA_RIGHT))
                    row.append(self._wrap_cell(str(n), font_name="Helvetica", font_size=7, alignment=TA_RIGHT))
                row.append(self._wrap_cell(f"${grand:,.0f}", font_name="Helvetica", font_size=7, alignment=TA_RIGHT))
                t_data_comp.append(row)
                
            ncols = len(hdr2_comp)
            cat_w = 2.4 * inch
            col_w = [cat_w] + [(7.3 * inch - cat_w) / (ncols - 1)] * (ncols - 1)
            t_comp = Table(t_data_comp, colWidths=col_w, repeatRows=2)
            style = [
                ("BACKGROUND",    (0, 0), (-1, 1), LTBLUE),
                ("TEXTCOLOR",     (0, 0), (-1, 1), rl_colors.grey),
                ("FONTNAME",      (0, 0), (-1, 1), "Helvetica-Bold"),
                ("FONTSIZE",      (0, 0), (-1, -1), 7),
                ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
                ("TEXTCOLOR",     (0, 2), (-1, -1), rl_colors.grey),
                ("TOPPADDING",    (0, 0), (-1, -1), 3),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 3),
                ("LEFTPADDING",   (0, 0), (-1, -1), 2),
                ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
                ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ]
            # Spans for years
            for i, _ in enumerate(years):
                c = 1 + i*2
                style.append(("SPAN", (c, 0), (c+1, 0)))
            t_comp.setStyle(TableStyle(style))
            story.append(t_comp)
            story.append(Spacer(1, 0.2*inch))

        kf2_post = (
            "Diabetes-specific complications are associated with uncontrolled diabetes and sometimes with "
            "undiagnosed diabetes. For example, a diagnosis of Idiopathic Neuropathy means \"of no known cause\"; "
            "however, it is often associated with an undiagnosed case of diabetes. Wellness programming that "
            "includes biometric screenings would identify individuals with undiagnosed diabetes.<br/><br/>"
            "An analysis was conducted to determine the number of individuals with diabetes who were compliant "
            "with evidence-based guidelines for diabetes. The analysis revealed that there were a large number of "
            "individuals with a diagnosis of diabetes who are non-compliant to evidence-based medications related "
            "to diabetes management. Systems are available that can mail specific clinical \"to dos\" to each member's "
            "home and monitor on-going compliance to these directions; this strategy also impacts the spouse and "
            "dependent children."
        )
        story.append(PageBreak())
        
        story.append(Paragraph(kf2_post, self.S["Body"]))
        story.append(Spacer(1, 0.2*inch))

        rec_sol_kf2 = "<b>Recommended Solution:</b> Establish evidence-based medicine guidelines (i.e., HEDIS goals) for the population that relate to diabetes management:"
        story.append(Paragraph(rec_sol_kf2, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        db_bullets = [
            "Hemoglobin A1c (HbA1c) testing",
            "Hemoglobin A1c control (<7.0%)",
            "Retinal eye exam performed",
            "Screening for neuropathy",
            "Blood Pressure control (<130/80 mm/Hg)",
            "Medical attention for nephropathy"
        ]
        for b in db_bullets:
            story.append(Paragraph(f"<bullet>&bull;</bullet>{b}", bullet_style))
            
        story.append(Spacer(1, 0.2*inch))

        # Key Finding 3: Preventive Screenings
        story.append(Paragraph("<b>Key Finding 3: Preventive Screenings</b>", self.S["SubHead"]))
        story.append(Spacer(1, 0.1*inch))
        kf3_text = "<b>Key Finding:</b><br/><br/>Preventive screenings for breast cancer, cervical cancer, and colorectal cancer were well below HEDIS National Guidelines. NCQA reports the following national screening rates:"
        story.append(Paragraph(kf3_text, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        scr_bullets = [
            "Breast Cancer Screening: Commercial HMO screening rate at 73.7% and Commercial PPO at 71.6%",
            "Cervical Cancer Screening: Commercial HMO screening rate at 76.2% and Commercial PPO at 74.2%",
            "Colorectal Cancer Screening: Commercial HMO screening rate at 65.0% and Commercial PPO at 61.8%"
        ]
        for b in scr_bullets:
            story.append(Paragraph(f"<bullet>&bull;</bullet>{b}", bullet_style))
            
        story.append(Spacer(1, 0.1*inch))
        story.append(Paragraph("Actual screening rates for the population across years were as follows:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        cd_screen = charts.get("preventive_screening")
        if cd_screen and cd_screen.data:
            years = sorted({str(r.get("YEAR", "")) for r in cd_screen.data})
            lookup: Dict[tuple, dict] = {}
            for r in cd_screen.data:
                lookup[(str(r.get("CANCER_SCREENING", "")), str(r.get("YEAR", "")))] = r
                
            hdr_scr = ["EBR Measures Year", "EBR Measures Breast Cancer\nScreening Rate", "EBR Measures Cervical Cancer\nScreening Rate", "EBR Measures Colon Cancer\nScreening Rate"]
            t_data_scr = [hdr_scr]
            
            for y in years:
                br_r = lookup.get(("BREAST CANCER", y), {})
                cv_r = lookup.get(("CERVICAL CANCER", y), {})
                cl_r = lookup.get(("COLON CANCER", y), {})
                
                br_val = f"{float(br_r.get('SCREENING_RATE_PCT') or 0):.1f}%" if br_r else ""
                cv_val = f"{float(cv_r.get('SCREENING_RATE_PCT') or 0):.1f}%" if cv_r else ""
                cl_val = f"{float(cl_r.get('SCREENING_RATE_PCT') or 0):.1f}%" if cl_r else ""
                
                t_data_scr.append([y, br_val, cv_val, cl_val])
                
            t_scr = Table(t_data_scr, colWidths=[1.5*inch, 2.0*inch, 2.0*inch, 1.8*inch])
            t_scr.setStyle(TableStyle([
                ("BACKGROUND",    (0, 0), (-1, 0), "#d5c9c1"),
                ("TEXTCOLOR",     (0, 0), (-1, 0), rl_colors.grey),
                ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
                ("FONTSIZE",      (0, 0), (-1, -1), 8),
                ("ALIGN",         (0, 0), (-1, -1), "RIGHT"),
                ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
                ("TEXTCOLOR",     (0, 1), (-1, -1), rl_colors.grey),
                ("TOPPADDING",    (0, 0), (-1, -1), 4),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
                ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ]))
            story.append(t_scr)
            story.append(Spacer(1, 0.2*inch))

        rec_sol_kf3 = (
            "<b>Recommended Solution:</b> Increase the awareness of age/gender-specific preventive screenings "
            "within the population. Education in combination with various incentives would increase the "
            "population's compliance with preventive screenings. Increased compliance to preventive screenings "
            "would identify diseases in the early stage, thus improving treatment outcomes and decreasing "
            "future expenditures.<br/><br/>"
            "Establish at least five HEDIS (Healthcare Effectiveness and Information Set) goals for the "
            "population. HEDIS is one of the most widely recognized healthcare performance measures in the "
            "United States. Suggested goals are as follows:"
        )
        story.append(Paragraph(rec_sol_kf3, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        goals = [
            ("<b>Goal 1:</b> Increase the number of individuals between the ages of 18 to 75 who have a diagnosis of diabetes and are compliant with the following evidence-based medicine guidelines:", [
                "Hemoglobin A1c (HbA1c) testing",
                "HbA1c poor control (>9.0%)",
                "HbA1c control (<8.0%)",
                "HbA1c control (<7.0%) for a selected population",
                "Eye exam (retinal) performed",
                "LDL-C screening",
                "LDL-C control (<100 mg/dl)",
                "Medical attention for nephropathy",
                "BP control (<130/80 mm Hg)"
            ]),
            ("<b>Goal 2:</b> Increase the number of individuals between the ages of 18 to 74 who had an outpatient visit and had their body mass index (BMI) documented.", []),
            ("<b>Goal 3:</b> Increase the percentage of women between the ages of 40 to 69 who had a mammogram to screen for breast cancer.", []),
            ("<b>Goal 4:</b> Increase the percentage of women between the ages of 21 to 64 who received one or more Pap tests to screen for cervical cancer.", []),
            ("<b>Goal 5:</b> Increase the percentage of individuals between the ages of 50 to 75 who had an appropriate screening for colorectal cancer.", [])
        ]
        
        for g_title, g_bullets in goals:
            if "Goal 2:" in g_title:
                story.append(PageBreak())
            story.append(Paragraph(g_title, self.S["Body"]))
            if g_bullets:
                story.append(Spacer(1, 0.05*inch))
                for b in g_bullets:
                    story.append(Paragraph(f"<bullet>&bull;</bullet>{b}", bullet_style))
            story.append(Spacer(1, 0.1*inch))

        # Key Finding 4: Musculoskeletal Diagnosis
        story.append(Paragraph("<b>Key Finding 4: Musculoskeletal Diagnosis</b>", self.S["SubHead"]))
        story.append(Spacer(1, 0.1*inch))
        kf4_text = "<b>Key Finding:</b> It is our experience that; expenditures for Musculoskeletal-related diagnosis category are among the highest in any given population. The chart below shows the relative position of Musculoskeletal as a Disease category among Top 10 Diagnostic categories by Total Expenditure."
        story.append(Paragraph(kf4_text, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        cd_diag = charts.get("diag_category")
        if cd_diag and cd_diag.data:
            years = sorted({str(r.get("YR", "")) for r in cd_diag.data})
            lookup: Dict[tuple, dict] = {}
            cat_totals: Dict[str, float] = {}
            for r in cd_diag.data:
                c = str(r.get("CATEGORY", ""))
                cat_totals[c] = cat_totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
                lookup[(c, str(r.get("YR", "")))] = r
            
            top10_cats = [k for k, _ in sorted(cat_totals.items(), key=lambda x: x[1], reverse=True)[:10]]
            
            hdr1_diag = [self._wrap_cell("Reporting Year", font_name="Helvetica-Bold", font_size=6, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1_diag += [self._wrap_cell(y, font_name="Helvetica-Bold", font_size=6, alignment=TA_CENTER, text_color=rl_colors.grey), ""]
            
            hdr2_diag = [self._wrap_cell("DIAGNOSTIC CATEGORY", font_name="Helvetica-Bold", font_size=6, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for _ in years:
                hdr2_diag += [
                    self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=6, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("N", font_name="Helvetica-Bold", font_size=6, alignment=TA_RIGHT, text_color=rl_colors.grey)
                ]
            t_data_diag = [hdr1_diag, hdr2_diag]
            
            for cat in top10_cats:
                row = [self._wrap_cell(cat, font_name="Helvetica", font_size=6, alignment=TA_LEFT)]
                for y in years:
                    r = lookup.get((cat, y), {})
                    amt = float(r.get("TOTAL_AMT") or 0) if r else 0
                    n = int(r.get("N") or 0) if r else 0
                    row += [
                        self._wrap_cell(f"${amt:,.0f}" if (r and amt) else "$0", font_name="Helvetica", font_size=6, alignment=TA_RIGHT),
                        self._wrap_cell(str(n), font_name="Helvetica", font_size=6, alignment=TA_RIGHT)
                    ]
                t_data_diag.append(row)
                
            ncols = len(hdr2_diag)
            cat_w = 2.0 * inch
            col_w = [cat_w] + [(7.3 * inch - cat_w) / (ncols - 1)] * (ncols - 1)
            t_diag = Table(t_data_diag, colWidths=col_w, repeatRows=2)
            
            style = [
                ("BACKGROUND",    (0, 0), (-1, 1), LTBLUE),
                ("TEXTCOLOR",     (0, 0), (-1, 1), rl_colors.grey),
                ("FONTNAME",      (0, 0), (-1, 1), "Helvetica-Bold"),
                ("FONTSIZE",      (0, 0), (-1, -1), 6),
                ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
                ("TEXTCOLOR",     (0, 2), (-1, -1), rl_colors.grey),
                ("TOPPADDING",    (0, 0), (-1, -1), 3),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 3),
                ("LEFTPADDING",   (0, 0), (-1, -1), 2),
                ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
                ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ]
            for i, _ in enumerate(years):
                c = 1 + i * 2
                style.append(("SPAN", (c, 0), (c + 1, 0)))
            t_diag.setStyle(TableStyle(style))
            story.append(t_diag)
            story.append(Spacer(1, 0.2*inch))

        kf4_post = "A further analysis was completed to investigate which Musculoskeletal & Connective Tissue claims could potentially be work-related. Work-related musculoskeletal claims are usually associated with jobs or crafts that require manual material handling, frequent bending and twisting, static work posture, or whole body vibration. The results of this analysis were as follows (shown in chart below):"
        story.append(Paragraph(kf4_post, self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        cd_msk = charts.get("msk_work")
        if cd_msk and cd_msk.data:
            years = sorted({str(r.get("YR", "")) for r in cd_msk.data})
            lookup: Dict[tuple, dict] = {}
            parts = []
            seen = set()
            for r in sorted(cd_msk.data, key=lambda x: -float(x.get("TOTAL_AMT") or 0)):
                bp = str(r.get("BODY_PART", ""))
                if bp and bp not in seen:
                    parts.append(bp)
                    seen.add(bp)
                lookup[(bp, str(r.get("YR", "")))] = r
            
            # Slice to top 5 just to avoid a massive table spanning pages
            top_parts = parts[:5]
                
            # Simplified headers
            hdr1_msk = [self._wrap_cell("Body Part", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1_msk.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), "", ""])
            
            hdr2_msk = [""]
            for _ in years:
                hdr2_msk.extend([
                    self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
                ])
            t_data_msk = [hdr1_msk, hdr2_msk]
            
            for bp in top_parts:
                row = [self._wrap_cell(bp, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                for y in years:
                    r = lookup.get((bp, y), {})
                    row += [
                        self._wrap_cell(f"${float(r.get('TOTAL_AMT') or 0):,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(f"${float(r.get('MEAN_AMT') or 0):,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(str(int(r.get("N") or 0)) if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    ]
                t_data_msk.append(row)
                
            ncols = len(hdr1_msk)
            bp_w = 2.0 * inch
            col_w = [bp_w] + [(7.3 * inch - bp_w) / (ncols - 1)] * (ncols - 1)
            t_msk = Table(t_data_msk, colWidths=col_w, repeatRows=2)
            style = [
                ("BACKGROUND",    (0, 0), (-1, 1), LTBLUE),
                ("TEXTCOLOR",     (0, 0), (-1, 1), rl_colors.grey),
                ("FONTNAME",      (0, 0), (-1, 1), "Helvetica-Bold"),
                ("FONTSIZE",      (0, 0), (-1, -1), 7),
                ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0, 2), (-1, -1), [WHITE, LTGREY]),
                ("TOPPADDING",    (0, 0), (-1, -1), 2),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 2),
                ("LEFTPADDING",   (0, 0), (-1, -1), 2),
                ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
                ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
                ("SPAN",          (0, 0), (0, 1)),
            ]
            col_idx = 1
            for _ in years:
                style.append(("SPAN", (col_idx, 0), (col_idx + 2, 0)))
                col_idx += 3
            t_msk.setStyle(TableStyle(style))
            story.append(t_msk)
            story.append(Spacer(1, 0.2*inch))
        else:
            years = ["2022", "2023", "2024"]
            t_data_msk = [["Medical records\nReporting Year"] + years]
            t_msk = Table(t_data_msk, colWidths=[1.5*inch, 2.0*inch, 2.0*inch, 1.8*inch])
            t_msk.setStyle(TableStyle([
                ("BACKGROUND",    (0, 0), (-1, -1), LTBLUE),
                ("TEXTCOLOR",     (0, 0), (-1, -1), rl_colors.grey),
                ("FONTNAME",      (0, 0), (-1, -1), "Helvetica-Bold"),
                ("FONTSIZE",      (0, 0), (-1, -1), 8),
                ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
                ("TOPPADDING",    (0, 0), (-1, -1), 4),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
                ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ]))
            story.append(t_msk)
            
        story.append(PageBreak())

    def _pivot_risk_data(self, rows) -> Table:
        """Pivot risk group rows into Year columns."""
        years = self.report_years
        groups = sorted({str(r.get("RISK_GROUP", "")) for r in rows})
        # Build lookup
        lookup: Dict[tuple, dict] = {}
        for r in rows:
            lookup[(str(r["RISK_GROUP"]), str(r["FILE_YEAR"]))] = r

        # Header rows - simplified headers to prevent overflow
        header1 = [self._wrap_cell("File Year", font_name="Helvetica-Bold", font_size=5.5, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for y in years:
            header1 += [
                self._wrap_cell(y, font_name="Helvetica-Bold", font_size=5.5, alignment=TA_CENTER, text_color=rl_colors.grey),
                "", ""
            ]

        header2 = [self._wrap_cell("Risk Group", font_name="Helvetica-Bold", font_size=5.5, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for _ in years:
            header2 += [
                self._wrap_cell("Total $", font_name="Helvetica-Bold", font_size=5.5, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("Mean $", font_name="Helvetica-Bold", font_size=5.5, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("N", font_name="Helvetica-Bold", font_size=5.5, alignment=TA_RIGHT, text_color=rl_colors.grey),
            ]

        data = [header1, header2]
        for g in groups:
            row = [self._wrap_cell(g, font_name="Helvetica", font_size=5.5, alignment=TA_LEFT)]
            for y in years:
                r = lookup.get((g, y), {})
                row += [
                    self._wrap_cell(f"${float(r.get('TOTAL_AMT') or 0):,.0f}", font_name="Helvetica", font_size=5.5, alignment=TA_RIGHT),
                    self._wrap_cell(f"${float(r.get('MEAN_AMT') or 0):,.0f}", font_name="Helvetica", font_size=5.5, alignment=TA_RIGHT),
                    self._wrap_cell(f"{int(r.get('N') or 0):,}", font_name="Helvetica", font_size=5.5, alignment=TA_RIGHT),
                ]
            data.append(row)

        ncols = len(header1)
        # Optimized column widths to prevent overlapping
        # First column for Risk Group: 1.0 inch
        # Data columns: Total $ (0.62), Mean $ (0.52), N (0.35) -> 1.49 per year
        # For 4 years: 1.0 + 4*1.49 = 6.96 inch (fits within 7.3 inch)
        col_w = [1.0 * inch]
        for _ in range(len(years)):
            col_w += [0.62 * inch, 0.52 * inch, 0.35 * inch]
        
        # Ensure col_w matches ncols
        if len(col_w) < ncols:
             col_w += [0.4 * inch] * (ncols - len(col_w))
             
        t = Table(data, colWidths=col_w, repeatRows=2)
        style = [
            ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
            ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
            ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
            ("FONTSIZE",      (0,0), (-1,-1), 5.5),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 2), (-1, -1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0,0), (-1,-1), 2),
            ("BOTTOMPADDING", (0,0), (-1,-1), 2),
            ("LEFTPADDING",   (0,0), (-1,-1), 2),
            ("RIGHTPADDING",  (0,0), (-1,-1), 2),
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
            ("SPAN",          (0,0), (0,1)),
        ]
        # Span year headers
        col_idx = 1
        for _ in years:
            style.append(("SPAN", (col_idx, 0), (col_idx+2, 0)))
            col_idx += 3
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
        # Wrap all strings in Paragraph for automatic wrapping
        wrapped_data = []
        for r_idx, row in enumerate(data):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx == 0)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = WHITE if is_header else BLACK
                    # Headers usually centered except first column
                    al = TA_CENTER if (r_idx == 0 and c_idx > 0) else TA_LEFT
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=8, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_data.append(wrapped_row)

        t = Table(wrapped_data, colWidths=col_widths, repeatRows=1, hAlign="LEFT")
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
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
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
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
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
        hdr = self._section_header_table("2. Demographic Information (Age and Gender)")
        hdr._toc_entry = "2. Demographic Information (Age and Gender)"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))

        # Chart 1: Mean Age and N by Relationship per Year
        story.append(Paragraph("The mean (average) age for the total population (including Employees, Spouses, and Dependents) was:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        cd_rel = charts.get("demographics_rel_age")
        if cd_rel and cd_rel.data:
            years = sorted(list(set(str(r["YR"]) for r in cd_rel.data)))

            def _get(rel, y, key, cast=float):
                row = next((r for r in cd_rel.data if str(r["YR"]) == y and str(r.get("RELATIONSHIP","")) == rel), None)
                return cast(row[key]) if row and row.get(key) is not None else 0

            emp_age = [round(_get("EMPLOYEE", y, "MEAN_AGE"), 1) for y in years]
            emp_n   = [_get("EMPLOYEE", y, "N", int) for y in years]
            dep_age = [round(_get("DEPENDENT", y, "MEAN_AGE"), 1) for y in years]
            dep_n   = [_get("DEPENDENT", y, "N", int) for y in years]
            spo_age = [round(_get("SPOUSE", y, "MEAN_AGE"), 1) for y in years]
            spo_n   = [_get("SPOUSE", y, "N", int) for y in years]

            cfg1 = {
                "type": "horizontalBar",
                "data": {
                    "labels": years,
                    "datasets": [
                        {"type": "line", "label": "DEPENDENT - AVERAGE AGE", "data": dep_age,
                         "borderColor": "#888888", "backgroundColor": "transparent", "fill": False, "pointRadius": 4},
                        {"type": "line", "label": "DEPENDENT - N", "data": dep_n,
                         "borderColor": "#555555", "backgroundColor": "transparent", "fill": False, "pointRadius": 4},
                        {"type": "horizontalBar", "label": "EMPLOYEE - AVERAGE AGE", "data": emp_age, "backgroundColor": "#5A9AD4"},
                        {"type": "horizontalBar", "label": "EMPLOYEE - N", "data": emp_n, "backgroundColor": "#2B3A5A"},
                        {"type": "line", "label": "SPOUSE - AVERAGE AGE", "data": spo_age,
                         "borderColor": "#cccccc", "backgroundColor": "transparent", "fill": False, "pointRadius": 4},
                        {"type": "line", "label": "SPOUSE - N", "data": spo_n,
                         "borderColor": "#aaaaaa", "backgroundColor": "transparent", "fill": False, "pointRadius": 4},
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end",
                                       "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    },
                    "title": {"display": True, "text": "MEMBER AVERAGE AGE"}
                }
            }
            img1 = self._fetch_quickchart(cfg1, "chart_demo1.png")
            if img1:
                story.append(RLImage(img1, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))

        # Chart 2: Employees only
        story.append(Paragraph("For employees only, the mean age was:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        if cd_rel and cd_rel.data:
            cfg2 = {
                "type": "horizontalBar",
                "data": {
                    "labels": years,
                    "datasets": [
                        {"label": "EMPLOYEE - AVERAGE AGE", "data": emp_age, "backgroundColor": "#227EE4"},
                        {"label": "EMPLOYEE - N",           "data": emp_n,   "backgroundColor": "#8192A6"}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end",
                                       "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    },
                    "title": {"display": True, "text": "EMPLOYEE - N"}
                }
            }
            img2 = self._fetch_quickchart(cfg2, "chart_demo2.png")
            if img2:
                story.append(RLImage(img2, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))

        story.append(PageBreak())

        # Chart 3: Number of Individuals by Age Group
        story.append(Paragraph("Number of Individuals by Age - Total Population (Employee, Spouse & Dependent):", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        cd_age = charts.get("demographics_age_group")
        if cd_age and cd_age.data:
            lbls     = [str(r["AGE_GROUP"]) for r in cd_age.data]
            mean_age = [round(float(r["MEAN_AGE"] or 0), 1) for r in cd_age.data]
            n_val    = [int(r["N"] or 0) for r in cd_age.data]

            cfg3 = {
                "type": "horizontalBar",
                "data": {
                    "labels": lbls,
                    "datasets": [
                        {"type": "horizontalBar", "label": "N", "data": n_val, "backgroundColor": "#227EE4"},
                        {"type": "line", "label": "MEMBER AVERAGE AGE", "data": mean_age,
                         "borderColor": "#555555", "backgroundColor": "transparent", "fill": False, "pointRadius": 4}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end",
                                       "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    },
                    "title": {"display": True, "text": "MEMBER AVERAGE AGE"}
                }
            }
            img3 = self._fetch_quickchart(cfg3, "chart_demo3.png")
            if img3:
                story.append(RLImage(img3, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))

        # Chart 4: Gender breakdown - bars = % of total, lines = N
        story.append(Paragraph("The gender breakdown for the total population (Employee, Spouse & Dependent) was:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))

        cd_gen = charts.get("demographics_gender_pct")
        if cd_gen and cd_gen.data:
            years_g = sorted(list(set(str(r["YR"]) for r in cd_gen.data)))
            f_n = [int(next((r["N"] for r in cd_gen.data if str(r["YR"])==y and r.get("GENDER")=="F"), 0)) for y in years_g]
            m_n = [int(next((r["N"] for r in cd_gen.data if str(r["YR"])==y and r.get("GENDER")=="M"), 0)) for y in years_g]
            totals = [f_n[i] + m_n[i] for i in range(len(years_g))]
            f_pct = [round(f_n[i]/totals[i]*100, 1) if totals[i] else 0 for i in range(len(years_g))]
            m_pct = [round(m_n[i]/totals[i]*100, 1) if totals[i] else 0 for i in range(len(years_g))]

            cfg4 = {
                "type": "horizontalBar",
                "data": {
                    "labels": years_g,
                    "datasets": [
                        {"type": "horizontalBar", "label": "Female - % of Total N", "data": f_pct, "backgroundColor": "#2B3A5A"},
                        {"type": "line", "label": "Female - N", "data": f_n,
                         "borderColor": "#2B3A5A", "backgroundColor": "transparent", "fill": False, "pointRadius": 4},
                        {"type": "horizontalBar", "label": "Male - % of Total N", "data": m_pct, "backgroundColor": "#227EE4"},
                        {"type": "line", "label": "Male - N", "data": m_n,
                         "borderColor": "#227EE4", "backgroundColor": "transparent", "fill": False, "pointRadius": 4},
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end",
                                       "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    },
                    "title": {"display": True, "text": "N"}
                }
            }
            img4 = self._fetch_quickchart(cfg4, "chart_demo4.png")
            if img4:
                story.append(RLImage(img4, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.2*inch))
    def _section_3_medical_by_year(self, story, charts):
        hdr = self._section_header_table("3. Overall Medical Expenditures by Year")
        hdr._toc_entry = "3. Overall Medical Expenditures by Year"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph("Medical expenditures* (based on paid claims) were as follows:", self.S["Body"]))
        story.append(Spacer(1, 0.1*inch))
        
        cd_med = charts.get("med_by_year")
        if cd_med and cd_med.data:
            years = self.report_years
            lookup = {str(r["YR"]): r for r in cd_med.data}
            
            # Table - simplified headers
            t_data = [[
                self._wrap_cell("Reporting Year", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT, text_color=rl_colors.grey),
                self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("N", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ]]
            sum_tot = 0
            sum_n = 0
            tot = []
            n_val = []
            mean = []
            
            for y in years:
                r = lookup.get(y, {})
                t_v = float(r.get("TOTAL_AMT") or 0)
                n_v = int(r.get("N") or 0)
                m_v = float(r.get("MEAN_AMT") or (t_v / n_v if n_v > 0 else 0))
                
                t_data.append([
                    self._wrap_cell(y, font_name="Helvetica", font_size=8, alignment=TA_LEFT),
                    self._wrap_cell(f"${t_v:,.0f}", font_name="Helvetica", font_size=8, alignment=TA_RIGHT),
                    self._wrap_cell(f"${m_v:,.0f}", font_name="Helvetica", font_size=8, alignment=TA_RIGHT),
                    self._wrap_cell(f"{n_v:,}", font_name="Helvetica", font_size=8, alignment=TA_RIGHT)
                ])
                sum_tot += t_v
                sum_n += n_v
                tot.append(t_v)
                n_val.append(n_v)
                mean.append(m_v)

            t_data.append([
                self._wrap_cell("Total", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT),
                self._wrap_cell(f"${sum_tot:,.0f}", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT),
                self._wrap_cell(f"${(sum_tot/sum_n if sum_n else 0):,.0f}", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT),
                self._wrap_cell(f"{sum_n:,}", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT)
            ])

            t = Table(t_data, colWidths=[2.5*inch, 1.6*inch, 1.6*inch, 1.6*inch])
            style = [
                ("BACKGROUND",    (0,0), (-1,0), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,0), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,0), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 8),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0,1), (-1,-2), [WHITE, LTGREY]),
                ("BACKGROUND",    (0,-1), (-1,-1), LTBLUE),
                ("TOPPADDING",    (0,0), (-1,-1), 3),
                ("BOTTOMPADDING", (0,0), (-1,-1), 3),
                ("LEFTPADDING",   (0,0), (-1,-1), 2),
                ("RIGHTPADDING",  (0,0), (-1,-1), 2),
                ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
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
        hdr = self._section_header_table("4. Overall Medical Expenditures by Quarter")
        hdr._toc_entry = "4. Overall Medical Expenditures by Quarter"
        story.append(hdr)
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
        hdr = self._section_header_table("5. Employee/ Spouse/ Dependent Expenditures")
        hdr._toc_entry = "5. Employee/ Spouse/ Dependent Expenditures"
        story.append(hdr)
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
            
            # Simplified headers
            hdr1 = [self._wrap_cell("Relationship", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), "", ""])
            t_data = [hdr1]
            
            hdr2 = [""]
            for _ in years:
                hdr2.extend([
                    self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
                ])
            t_data.append(hdr2)
            
            for rel in ["EMPLOYEE", "SPOUSE", "DEPENDENT"]:
                row = [self._wrap_cell(rel, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                for i in range(len(years)):
                    row.extend([
                        self._wrap_cell(f"${data_by_rel[rel]['tot'][i]:,.0f}", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(f"${data_by_rel[rel]['mean'][i]:,.0f}", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(f"{data_by_rel[rel]['n'][i]:,}", font_name="Helvetica", font_size=7, alignment=TA_RIGHT)
                    ])
                t_data.append(row)
                
            _first_w = 1.2 * inch
            _n_data_cols = len(years) * 3
            _data_col_w = (7.3 * inch - _first_w) / max(_n_data_cols, 1)
            col_widths = [_first_w] + [_data_col_w] * _n_data_cols
            t = Table(t_data, colWidths=col_widths)

            style = [
                ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 7),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("SPAN",          (0,0), (0,1)),
                ("TOPPADDING",    (0,0), (-1,-1), 3),
                ("BOTTOMPADDING", (0,0), (-1,-1), 3),
                ("LEFTPADDING",   (0,0), (-1,-1), 2),
                ("RIGHTPADDING",  (0,0), (-1,-1), 2),
                ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
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
        hdr = self._section_header_table("6. Gender Related Expenditures")
        hdr._toc_entry = "6. Gender Related Expenditures"
        story.append(hdr)
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
            
            # Simplified headers
            hdr1 = [self._wrap_cell("Reporting Year", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for g in ["Female", "Male"]:
                hdr1.extend([self._wrap_cell(g, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), "", ""])
            t_data = [hdr1]
            
            hdr2 = [""]
            for _ in ["Female", "Male"]:
                hdr2.extend([
                    self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
                ])
            t_data.append(hdr2)
            
            for i, y in enumerate(years):
                row = [self._wrap_cell(y, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                for g in ["F", "M"]:
                    row.extend([
                        self._wrap_cell(f"${data_by_gen[g]['tot'][i]:,.0f}", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(f"${data_by_gen[g]['mean'][i]:,.0f}", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(f"{data_by_gen[g]['n'][i]:,}", font_name="Helvetica", font_size=7, alignment=TA_RIGHT)
                    ])
                t_data.append(row)
            
            _first_w = 1.0 * inch
            _n_data_cols = 6
            _data_col_w = (7.3 * inch - _first_w) / _n_data_cols
            col_widths = [_first_w] + [_data_col_w] * _n_data_cols
            t = Table(t_data, colWidths=col_widths)
            
            style = [
                ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 7),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("SPAN",          (0,0), (0,1)),
                ("SPAN",          (1,0), (3,0)),
                ("SPAN",          (4,0), (6,0)),
                ("TOPPADDING",    (0,0), (-1,-1), 3),
                ("BOTTOMPADDING", (0,0), (-1,-1), 3),
                ("LEFTPADDING",   (0,0), (-1,-1), 2),
                ("RIGHTPADDING",  (0,0), (-1,-1), 2),
                ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
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

    # ── Section 7: Diagnostic Category Expenditures ──────────────────────────

    def _section_7_diagnostic_categories(self, story, charts):
        hdr = self._section_header_table("7. Diagnostic Category Expenditures")
        hdr._toc_entry = "7. Diagnostic Category Expenditures"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "Diagnostic categories* ranked by expense were as follows:",
            self.S["Body"]
        ))
        story.append(Paragraph(
            "*Refer to Appendix 2 for examples of Diagnostic Categories.",
            ParagraphStyle("italic7", parent=self.S["Body"], fontSize=8, fontName="Helvetica-Oblique")
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("diag_category")
        if not cd or not cd.data:
            story.append(Paragraph("No diagnostic category data available.", self.S["Body"]))
            return

        years = self.report_years

        # --- Total $ pivot table ---
        story.append(Paragraph("<b>Total Amount Paid - Diagnostic Category - Total Population</b>",
                               ParagraphStyle("cen7", parent=self.S["Body"], alignment=TA_CENTER)))
        story.append(Spacer(1, 0.05*inch))

        cats = []
        seen = set()
        for r in sorted(cd.data, key=lambda x: -float(x.get("TOTAL_AMT") or 0)):
            c = str(r.get("CATEGORY", ""))
            if c and c not in seen:
                cats.append(c)
                seen.add(c)
        
        # Limit to top 20 categories to prevent exceeding 2 pages
        cats = cats[:20]

        lookup: Dict[tuple, dict] = {}
        for r in cd.data:
            lookup[(str(r.get("CATEGORY", "")), str(r.get("YR", "")))] = r

        # Simplified headers
        hdr1 = [self._wrap_cell("Diagnostic Category", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for y in years:
            hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), ""])
        
        hdr2 = [""]
        for _ in years:
            hdr2.extend([
                self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ])
        t_data = [hdr1, hdr2]
        
        for cat in cats:
            row = [self._wrap_cell(cat, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
            for y in years:
                r = lookup.get((cat, y), {})
                row += [
                    self._wrap_cell(f"${float(r.get('TOTAL_AMT') or 0):,.0f}" if r else "$0", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(f"{int(r.get('N') or 0):,}" if r else "0", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                ]
            t_data.append(row)

        # Wrap data
        wrapped_data = []
        for r_idx, row in enumerate(t_data):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx < 2)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = rl_colors.grey if is_header else BLACK
                    al = TA_LEFT if c_idx == 0 else TA_RIGHT
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_data.append(wrapped_row)

        cat_w = 3.0 * inch
        _n_data_cols = len(years) * 2
        _data_col_w = (7.3 * inch - cat_w) / _n_data_cols
        col_w = [cat_w] + [_data_col_w] * _n_data_cols
        t = Table(t_data, colWidths=col_w, repeatRows=2)
        style = [
            ("BACKGROUND",    (0, 0), (-1, 1), LTBLUE),
            ("TEXTCOLOR",     (0, 0), (-1, 1), rl_colors.grey),
            ("FONTNAME",      (0, 0), (-1, 1), "Helvetica-Bold"),
            ("FONTSIZE",      (0, 0), (-1, -1), 7),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 2), (-1, -1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0, 0), (-1, -1), 2),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 2),
            ("LEFTPADDING",   (0, 0), (-1, -1), 2),
            ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ("SPAN",          (0, 0), (0, 1)), # Span the first column
        ]
        col_idx = 1
        for _ in years:
            style.append(("SPAN", (col_idx, 0), (col_idx + 1, 0)))
            col_idx += 2
        t.setStyle(TableStyle(style))
        story.append(t)
        story.append(Spacer(1, 0.2*inch))

        # --- Total $ chart ---
        top_cats_by_total: Dict[str, float] = {}
        for r in cd.data:
            c = str(r.get("CATEGORY", ""))
            top_cats_by_total[c] = top_cats_by_total.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
        top10 = sorted(top_cats_by_total.items(), key=lambda x: x[1], reverse=True)[:10]
        top10_labels = [x[0] for x in top10]
        top10_vals   = [x[1] for x in top10]

        cfg_tot = {
            "type": "horizontalBar",
            "data": {
                "labels": top10_labels,
                "datasets": [{"label": "Total $", "data": top10_vals, "backgroundColor": "#227EE4"}]
            },
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "right", "anchor": "end",
                                   "font": {"weight": "bold", "size": 8}},
                    "legend": {"position": "bottom"}
                },
                "title": {"display": True, "text": "Total Amount Paid - Diagnostic Category - Total Population"}
            }
        }
        img_tot = self._fetch_quickchart(cfg_tot, "chart_sec7_tot.png")
        if img_tot:
            story.append(RLImage(img_tot, width=7.0*inch, height=3.5*inch))
            story.append(Spacer(1, 0.2*inch))

        story.append(PageBreak())

        # --- Mean $ pivot table ---
        story.append(Paragraph("<b>Mean Amount Paid - Diagnostic Category - Total Population</b>",
                               ParagraphStyle("cen7m", parent=self.S["Body"], alignment=TA_CENTER)))
        story.append(Spacer(1, 0.05*inch))

        # We will re-sort the 'cats' array based on MEAN_AMT to match the "Mean Amount Paid" table requirement
        cats_mean = []
        seen_mean = set()
        for r in sorted(cd.data, key=lambda x: -float(x.get("MEAN_AMT") or 0)):
            c = str(r.get("CATEGORY", ""))
            if c and c not in seen_mean:
                cats_mean.append(c)
                seen_mean.add(c)
        cats_mean = cats_mean[:20]

        # Simplified headers
        hdr1m = [self._wrap_cell("Diagnostic Category", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for y in years:
            hdr1m.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), ""])
            
        hdr2m = [""]
        for _ in years:
            hdr2m.extend([
                self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ])
        t_data_m = [hdr1m, hdr2m]
        
        for cat in cats_mean:
            row = [self._wrap_cell(cat, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
            for y in years:
                r = lookup.get((cat, y), {})
                row += [
                    self._wrap_cell(f"${float(r.get('MEAN_AMT') or 0):,.0f}" if r else "$0", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(f"{int(r.get('N') or 0):,}" if r else "0", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                ]
            t_data_m.append(row)

        tm = Table(t_data_m, colWidths=col_w, repeatRows=2)
        tm.setStyle(TableStyle(style))
        story.append(tm)
        story.append(Spacer(1, 0.2*inch))

        # --- Mean $ chart ---
        top_cats_by_mean: Dict[str, float] = {}
        for r in cd.data:
            c = str(r.get("CATEGORY", ""))
            n = int(r.get("N") or 0)
            amt = float(r.get("MEAN_AMT") or 0)
            if n > 0:
                top_cats_by_mean[c] = top_cats_by_mean.get(c, 0) + amt
        top10m = sorted(top_cats_by_mean.items(), key=lambda x: x[1], reverse=True)[:10]

        cfg_mean = {
            "type": "horizontalBar",
            "data": {
                "labels": [x[0] for x in top10m],
                "datasets": [{"label": "Mean $", "data": [x[1] for x in top10m], "backgroundColor": "#2B3A5A"}]
            },
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "right", "anchor": "end",
                                   "font": {"weight": "bold", "size": 8}},
                    "legend": {"position": "bottom"}
                },
                "title": {"display": True, "text": "Mean Amount Paid - Diagnostic Category - Total Population"}
            }
        }
        img_mean = self._fetch_quickchart(cfg_mean, "chart_sec7_mean.png")
        if img_mean:
            story.append(RLImage(img_mean, width=7.0*inch, height=3.5*inch))
            story.append(Spacer(1, 0.2*inch))

    # ── Section 8: Chronic Disease Expenditures ──────────────────────────────

    def _section_8_chronic_diseases(self, story, charts):
        hdr = self._section_header_table("8. Chronic Disease Expenditures")
        hdr._toc_entry = "8. Chronic Disease Expenditures"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "Chronic diseases* ranked by expense were as follows:",
            self.S["Body"]
        ))
        story.append(Paragraph(
            "*For this calculation, if an individual has multiple chronic diseases, "
            "they will be counted for each chronic disease.",
            ParagraphStyle("italic8", parent=self.S["Body"], fontSize=8, fontName="Helvetica-Oblique")
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("chronic_diseases")
        if not cd or not cd.data:
            story.append(Paragraph("No chronic disease data available.", self.S["Body"]))
            return

        years = self.report_years

        lookup: Dict[tuple, dict] = {}
        for r in cd.data:
            lookup[(str(r.get("CHRONIC_CAT", "")), str(r.get("FILE_YEAR", "")))] = r

        # Shared table style for both pivots
        # Shared table style
        def _pivot_style(col_w, years):
            style = [
                ("BACKGROUND",    (0, 0), (-1, 1), LTBLUE),
                ("TEXTCOLOR",     (0, 0), (-1, 1), rl_colors.grey),
                ("FONTNAME",      (0, 0), (-1, 1), "Helvetica-Bold"),
                ("FONTSIZE",      (0, 0), (-1, -1), 7),
                ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0, 2), (-1, -1), [WHITE, LTGREY]),
                ("TOPPADDING",    (0, 0), (-1, -1), 2),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 2),
                ("LEFTPADDING",   (0, 0), (-1, -1), 2),
                ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
                ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
                ("SPAN",          (0, 0), (0, 1)),
            ]
            col_idx = 1
            for _ in years:
                style.append(("SPAN", (col_idx, 0), (col_idx + 1, 0)))
                col_idx += 2
            return style

        cat_w = 2.8 * inch
        col_w = [cat_w] + [(7.3 * inch - cat_w) / (1 + len(years) * 2 - 1)] * (len(years) * 2)

        # --- Total $ pivot (sorted by total $ descending) ---
        cat_totals: Dict[str, float] = {}
        for r in cd.data:
            c = str(r.get("CHRONIC_CAT", ""))
            cat_totals[c] = cat_totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
        cats_by_total = [k for k, _ in sorted(cat_totals.items(), key=lambda x: x[1], reverse=True)]

        # Simplified headers
        hdr1 = [self._wrap_cell("Chronic Category", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for y in years:
            hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), ""])
            
        hdr2 = [""]
        for _ in years:
            hdr2.extend([
                self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ])
        t_data = [hdr1, hdr2]
        
        for cat in cats_by_total[:10]: # limit to top 10
            row = [self._wrap_cell(cat, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
            for y in years:
                r = lookup.get((cat, y), {})
                amt = float(r.get("TOTAL_AMT") or 0) if r else 0
                n   = int(r.get("N") or 0) if r else 0
                row += [
                    self._wrap_cell(f"${amt:,.0f}" if (r and amt) else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(str(n) if (r and n) else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT)
                ]
            t_data.append(row)

        t = Table(t_data, colWidths=col_w, repeatRows=2)
        t.setStyle(TableStyle(_pivot_style(col_w, years)))
        story.append(t)
        story.append(Spacer(1, 0.15*inch))

        # --- Mean $ pivot (sorted by mean $ descending, matching Long County page 20/21) ---
        story.append(Paragraph(
            "<b>Mean Amount Paid - Chronic Category - Total Population</b>",
            ParagraphStyle("cen8m", parent=self.S["Body"], alignment=TA_CENTER)
        ))
        story.append(Spacer(1, 0.05*inch))

        cat_means: Dict[str, float] = {}
        for r in cd.data:
            c = str(r.get("CHRONIC_CAT", ""))
            n = int(r.get("N") or 0)
            amt = float(r.get("TOTAL_AMT") or 0)
            if n > 0:
                cat_means[c] = cat_means.get(c, 0) + (amt / n)
        cats_by_mean = [k for k, _ in sorted(cat_means.items(), key=lambda x: x[1], reverse=True)]

        # Simplified headers
        hdr1m = [self._wrap_cell("Chronic Category", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for y in years:
            hdr1m.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), ""])
            
        hdr2m = [""]
        for _ in years:
            hdr2m.extend([
                self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ])
        t_data_m = [hdr1m, hdr2m]
        
        for cat in cats_by_mean[:10]:
            row = [self._wrap_cell(cat, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
            for y in years:
                r = lookup.get((cat, y), {})
                n   = int(r.get("N") or 0) if r else 0
                amt = float(r.get("TOTAL_AMT") or 0) if r else 0
                mean = (amt / n) if n > 0 else 0
                row += [
                    self._wrap_cell(f"${mean:,.0f}" if (r and mean) else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(str(n) if (r and n) else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT)
                ]
            t_data_m.append(row)

        tm = Table(t_data_m, colWidths=col_w, repeatRows=2)
        tm.setStyle(TableStyle(_pivot_style(col_w, years)))
        story.append(tm)
        story.append(Spacer(1, 0.15*inch))

        # --- Frequency ranking table (N only, sorted by total N descending) ---
        story.append(Paragraph(
            "Chronic diseases* ranked by frequency were as follows:",
            self.S["Body"]
        ))
        story.append(Paragraph(
            "* For this calculation, if an individual has multiple chronic diseases, "
            "they will be counted for each chronic disease.",
            ParagraphStyle("italic8f", parent=self.S["Body"], fontSize=8, fontName="Helvetica-Oblique")
        ))
        story.append(Spacer(1, 0.05*inch))

        cat_n_totals: Dict[str, int] = {}
        for r in cd.data:
            c = str(r.get("CHRONIC_CAT", ""))
            cat_n_totals[c] = cat_n_totals.get(c, 0) + int(r.get("N") or 0)
        cats_by_freq = [k for k, _ in sorted(cat_n_totals.items(), key=lambda x: x[1], reverse=True)]

        # Simplified headers
        hdr_f = [self._wrap_cell("Chronic Category", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for y in years:
            hdr_f.append(self._wrap_cell(f"N {y}", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey))
        t_data_f = [hdr_f]
        
        for cat in cats_by_freq[:10]:
            row = [self._wrap_cell(cat, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
            for y in years:
                r = lookup.get((cat, y), {})
                n = int(r.get("N") or 0) if r else 0
                row.append(self._wrap_cell(str(n) if (r and n) else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT))
            t_data_f.append(row)

        ncols_f = len(hdr_f)
        col_w_f = [3.0 * inch] + [(7.3 * inch - 3.0 * inch) / (ncols_f - 1)] * (ncols_f - 1)
        tf = Table(t_data_f, colWidths=col_w_f, repeatRows=1)
        tf.setStyle(TableStyle([
            ("BACKGROUND",    (0, 0), (-1, 0), LTBLUE),
            ("TEXTCOLOR",     (0, 0), (-1, 0), rl_colors.grey),
            ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE",      (0, 0), (-1, -1), 7),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 1), (-1, -1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0, 0), (-1, -1), 2),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 2),
            ("LEFTPADDING",   (0, 0), (-1, -1), 2),
            ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
        ]))
        story.append(tf)

    # ── Section 9: Diabetes Expenditures & Related Risk Stratification ───────

    def _section_9_diabetes_expenditures(self, story, charts):
        hdr = self._section_header_table("9. Diabetes Expenditures & Related Risk Stratification")
        hdr._toc_entry = "9. Diabetes Expenditures & Related Risk Stratification"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "Diabetes expensive complications* ranked by expense were as follows:",
            self.S["Body"]
        ))
        story.append(Paragraph(
            "*Refer to Appendix 3 for examples of Complications of Diabetes.",
            ParagraphStyle("italic9", parent=self.S["Body"], fontSize=8, fontName="Helvetica-Oblique")
        ))
        story.append(Spacer(1, 0.1*inch))

        cd_comp = charts.get("diabetes_complications")
        cd_comorbid = charts.get("diabetes_comorbids")
        cd_strat = charts.get("diabetes_strat")

        # --- Complications pivot table ---
        if cd_comp and cd_comp.data:
            years = sorted({str(r.get("FILE_YEAR", "")) for r in cd_comp.data})
            comps = []
            seen: set = set()
            for r in sorted(cd_comp.data, key=lambda x: -float(x.get("TOTAL_AMT") or 0)):
                c = str(r.get("COMPLICATION", ""))
                if c and c not in seen:
                    comps.append(c)
                    seen.add(c)

            lookup: Dict[tuple, dict] = {}
            for r in cd_comp.data:
                lookup[(str(r.get("COMPLICATION", "")), str(r.get("FILE_YEAR", "")))] = r

            # Simplified headers
            hdr1 = [self._wrap_cell("Complication", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), ""])
            hdr1.append(self._wrap_cell("Grand Total", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey))

            hdr2 = [""]
            for _ in years:
                hdr2.extend([
                    self._wrap_cell("Total $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
                ])
            hdr2.append(self._wrap_cell("Total $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey))
            t_data = [hdr1, hdr2]
            
            for comp in comps:
                row = [self._wrap_cell(comp, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                grand = 0.0
                for y in years:
                    r = lookup.get((comp, y), {})
                    v = float(r.get("TOTAL_AMT") or 0)
                    grand += v
                    row += [
                        self._wrap_cell(f"${v:,.0f}" if r else "—", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(f"{int(r.get('N') or 0):,}" if r else "—", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    ]
                row.append(self._wrap_cell(f"${grand:,.0f}", font_name="Helvetica", font_size=7, alignment=TA_RIGHT))
                t_data.append(row)

            ncols = len(hdr2)
            comp_w = 2.4 * inch
            col_w = [comp_w] + [(7.3 * inch - comp_w) / (ncols - 1)] * (ncols - 1)
            t = Table(t_data, colWidths=col_w, repeatRows=2)
            style = [
                ("BACKGROUND",    (0, 0), (-1, 1), LTBLUE),
                ("TEXTCOLOR",     (0, 0), (-1, 1), rl_colors.grey),
                ("FONTNAME",      (0, 0), (-1, 1), "Helvetica-Bold"),
                ("FONTSIZE",      (0, 0), (-1, -1), 7),
                ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0, 2), (-1, -1), [WHITE, LTGREY]),
                ("TOPPADDING",    (0, 0), (-1, -1), 2),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 2),
                ("LEFTPADDING",   (0, 0), (-1, -1), 2),
                ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
                ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
                ("SPAN",          (0, 0), (0, 1)),
                ("SPAN",          (-1, 0), (-1, 1)),
            ]
            col_idx = 1
            for _ in years:
                style.append(("SPAN", (col_idx, 0), (col_idx + 1, 0)))
                col_idx += 2
            t.setStyle(TableStyle(style))
            story.append(t)
            story.append(Spacer(1, 0.1*inch))

            # Complications chart
            story.append(Paragraph("<b>Total Amount Paid - Complications of Diabetes - Total Population</b>",
                                   ParagraphStyle("cen9", parent=self.S["Body"], alignment=TA_CENTER)))
            comp_totals = {c: 0.0 for c in comps}
            for r in cd_comp.data:
                c = str(r.get("COMPLICATION", ""))
                comp_totals[c] = comp_totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)

            cfg_comp = {
                "type": "horizontalBar",
                "data": {
                    "labels": list(comp_totals.keys()),
                    "datasets": [{"label": "Total $", "data": list(comp_totals.values()),
                                  "backgroundColor": "#227EE4"}]
                },
                "options": {
                    "plugins": {
                        "datalabels": {
                            "display": True, "align": "right", "anchor": "end",
                            "font": {"weight": "bold", "size": 9},
                            "formatter": "function(v){return '$'+v.toLocaleString();}"
                        },
                        "legend": {"position": "bottom"}
                    },
                    "title": {"display": True, "text": "Total Amount Paid - Complications of Diabetes"}
                }
            }
            img_comp = self._fetch_quickchart(cfg_comp, "chart_sec9_comp.png")
            if img_comp:
                story.append(RLImage(img_comp, width=7.0*inch, height=3.0*inch))
                story.append(Spacer(1, 0.15*inch))

        story.append(PageBreak())

        # --- Co-morbidities chart ---
        story.append(Paragraph(
            "Number of Individuals with a Diagnosis of Diabetes in Combination with Co-Morbidities",
            self.S["SubHead"]
        ))
        story.append(Spacer(1, 0.05*inch))

        if cd_comorbid and cd_comorbid.data:
            cb_years = sorted({str(r.get("FILE_YEAR", "")) for r in cd_comorbid.data})
            comorbids_set: Dict[str, bool] = {}
            for r in cd_comorbid.data:
                comorbids_set[str(r.get("COMORBID", ""))] = True
            cb_list = list(comorbids_set.keys())

            cb_lookup: Dict[tuple, int] = {}
            for r in cd_comorbid.data:
                cb_lookup[(str(r.get("COMORBID", "")), str(r.get("FILE_YEAR", "")))] = int(r.get("N") or 0)

            # Build datasets per year for a grouped chart
            datasets = []
            colors = ["#2B3A5A", "#227EE4", "#5A9AD4", "#A8DADC"]
            for i, y in enumerate(cb_years):
                datasets.append({
                    "label": str(y),
                    "data": [cb_lookup.get((cb, y), 0) for cb in cb_list],
                    "backgroundColor": colors[i % len(colors)],
                })

            cfg_cb = {
                "type": "horizontalBar",
                "data": {"labels": cb_list, "datasets": datasets},
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end",
                                       "font": {"weight": "bold", "size": 8}},
                        "legend": {"position": "bottom"}
                    },
                    "title": {"display": True,
                              "text": "Number of Individuals with Diabetes + Co-Morbidities"}
                }
            }
            img_cb = self._fetch_quickchart(cfg_cb, "chart_sec9_cb.png")
            if img_cb:
                story.append(RLImage(img_cb, width=7.0*inch, height=3.5*inch))
                story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "Diabetes-specific complications are associated with uncontrolled and sometimes "
            "undiagnosed diabetes. Diabetic members with multiple co-morbidities face "
            "exponentially higher costs and require intensive management.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Establish evidence-based medicine guidelines (HEDIS goals) for diabetes management: "
            "Hemoglobin A1c (HbA1c) testing, HbA1c control (<7.0%), retinal eye exam, "
            "screening for neuropathy, blood pressure control (<130/80 mm/Hg), and medical "
            "attention for nephropathy. Deploy intensive diabetes coaching for high-risk members.",
            self.S["Body"]
        ))

    # ── Section 10: Diabetes Non-Compliance to Evidence-Based Medicine ────────

    def _section_10_diabetes_ebm(self, story, charts):
        hdr = self._section_header_table("10. Diabetes Non-Compliance to Evidence-Based Medicine")
        hdr._toc_entry = "10. Diabetes Non-Compliance to Evidence-Based Medicine"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following data identifies the number of individuals who had a diagnosis of "
            "diabetes and were non-compliant with evidence-based medicine guidelines.",
            self.S["Body"]
        ))
        story.append(Paragraph(
            "*Eligibility is defined as having a diagnosis of Diabetes or diagnosis of Diabetes "
            "with End Organ Damage.",
            ParagraphStyle("italic10", parent=self.S["Body"], fontSize=8, fontName="Helvetica-Oblique")
        ))
        story.append(Spacer(1, 0.15*inch))

        cd = charts.get("diabetes_ebm")
        if not cd or not cd.data:
            story.append(Paragraph("No diabetes EBM compliance data available.", self.S["Body"]))
            return

        years = [str(r.get("YEAR", "")) for r in cd.data]

        # --- Medication non-compliance table ---
        story.append(Paragraph("<b>Medication Non-Compliance</b>", self.S["SubHead"]))
        story.append(Spacer(1, 0.05*inch))

        med_cols = [
            ("NO_ACE",    "Diabetes,\nNo Ace Inhibitor"),
            ("NO_ARB",    "Diabetes,\nNo ARB"),
            ("NO_DRI",    "Diabetes,\nNo DRI"),
            ("NO_STATIN", "Diabetes,\nNo Statin Drug"),
        ]
        # Simplified headers
        hdr_med = [self._wrap_cell("Year", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for _, label in med_cols:
            hdr_med.append(self._wrap_cell(label.replace("\n", " "), font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey))
        t_data_med = [hdr_med]
        
        totals_med = {col: 0 for col, _ in med_cols}
        for r in cd.data:
            row = [self._wrap_cell(str(r.get("YEAR", "")), font_name="Helvetica", font_size=8, alignment=TA_LEFT)]
            for col, _ in med_cols:
                v = int(r.get(col) or 0)
                totals_med[col] = totals_med.get(col, 0) + v
                row.append(self._wrap_cell(str(v) if v else "—", font_name="Helvetica", font_size=8, alignment=TA_CENTER))
            t_data_med.append(row)

        uniq_row = [self._wrap_cell("Total", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT)]
        for col, _ in med_cols:
            uniq_row.append(self._wrap_cell(str(totals_med[col]), font_name="Helvetica-Bold", font_size=8, alignment=TA_CENTER))
        t_data_med.append(uniq_row)

        ncols_med = len(hdr_med)
        col_w_med = [1.0 * inch] + [(7.3 * inch - 1.0 * inch) / (ncols_med - 1)] * (ncols_med - 1)
        t_med = Table(t_data_med, colWidths=col_w_med, repeatRows=1)
        t_med.setStyle(TableStyle([
            ("BACKGROUND",    (0, 0), (-1, 0), LTBLUE),
            ("TEXTCOLOR",     (0, 0), (-1, 0), rl_colors.grey),
            ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE",      (0, 0), (-1, -1), 8),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 1), (-1, -2), [WHITE, LTGREY]),
            ("FONTNAME",      (0, -1), (-1, -1), "Helvetica-Bold"),
            ("TOPPADDING",    (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
            ("LEFTPADDING",   (0, 0), (-1, -1), 2),
            ("RIGHTPADDING",  (0, 0), (-1, -1), 2),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
        ]))
        story.append(t_med)
        story.append(Spacer(1, 0.2*inch))

        # --- Exam/test non-compliance table ---
        story.append(Paragraph("<b>Exam & Test Non-Compliance</b>", self.S["SubHead"]))
        story.append(Spacer(1, 0.05*inch))

        exam_cols = [
            ("NO_FOOT_EXAM", "Diabetes,\nNo Foot Exam"),
            ("NO_EYE_EXAM",  "Diabetes,\nNo Eye Exam"),
            ("NO_HBA1C",     "Diabetes,\nNo HbA1c"),
        ]
        hdr_exam = ["Year"] + [label for _, label in exam_cols]
        t_data_exam = [hdr_exam]
        totals_exam = {col: 0 for col, _ in exam_cols}
        for r in cd.data:
            row = [str(r.get("YEAR", ""))]
            for col, _ in exam_cols:
                v = int(r.get(col) or 0)
                totals_exam[col] = totals_exam.get(col, 0) + v
                row.append(str(v) if v else "—")
            t_data_exam.append(row)

        uniq_row_e = ["Total"]
        for col, _ in exam_cols:
            uniq_row_e.append(str(totals_exam[col]))
        t_data_exam.append(uniq_row_e)

        # Wrap data
        wrapped_data_exam = []
        for r_idx, row in enumerate(t_data_exam):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx == 0 or r_idx == len(t_data_exam)-1)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = rl_colors.grey if (r_idx == 0) else BLACK
                    al = TA_LEFT if c_idx == 0 else TA_CENTER
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=8, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_data_exam.append(wrapped_row)

        ncols_exam = len(hdr_exam)
        col_w_exam = [1.0 * inch] + [(7.3 * inch - 1.0 * inch) / (ncols_exam - 1)] * (ncols_exam - 1)
        t_exam = Table(wrapped_data_exam, colWidths=col_w_exam, repeatRows=1)
        t_exam.setStyle(TableStyle([
            ("BACKGROUND",    (0, 0), (-1, 0), LTBLUE),
            ("TEXTCOLOR",     (0, 0), (-1, 0), rl_colors.grey),
            ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTNAME",      (0, 1), (-1, -1), "Helvetica"),
            ("FONTSIZE",      (0, 0), (-1, -1), 8),
            ("ALIGN",         (0, 0), (-1, -1), "CENTER"),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 1), (-1, -2), [WHITE, LTGREY]),
            ("BACKGROUND",    (0, -1), (-1, -1), LTBLUE),
            ("FONTNAME",      (0, -1), (-1, -1), "Helvetica-Bold"),
            ("TOPPADDING",    (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
        ]))
        story.append(t_exam)
        story.append(Spacer(1, 0.2*inch))

        # --- Combined bar chart: all EBM measures across years ---
        ebm_measure_labels = [label.replace("\n", " ") for _, label in med_cols + exam_cols]
        datasets_ebm = []
        colors_ebm = ["#2B3A5A", "#227EE4", "#5A9AD4"]
        for i, r in enumerate(cd.data):
            y = str(r.get("YEAR", ""))
            vals = []
            for col, _ in med_cols + exam_cols:
                vals.append(int(r.get(col) or 0))
            datasets_ebm.append({
                "label": y,
                "data": vals,
                "backgroundColor": colors_ebm[i % len(colors_ebm)],
            })

        cfg_ebm = {
            "type": "bar",
            "data": {
                "labels": ebm_measure_labels,
                "datasets": datasets_ebm,
            },
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "top", "anchor": "end",
                                   "font": {"weight": "bold", "size": 8}},
                    "legend": {"position": "bottom"}
                },
                "title": {"display": True,
                          "text": "Diabetes Non-Compliance to Evidence-Based Medicine"},
                "scales": {
                    "yAxes": [{"ticks": {"beginAtZero": True}}]
                }
            }
        }
        img_ebm = self._fetch_quickchart(cfg_ebm, "chart_sec10_ebm.png")
        if img_ebm:
            story.append(RLImage(img_ebm, width=7.0*inch, height=3.5*inch))
            story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "A large number of individuals with a diagnosis of diabetes are non-compliant with "
            "evidence-based medications and clinical exams related to diabetes management. "
            "Systems that monitor ongoing compliance and mail clinical action items to each "
            "member's home can significantly improve adherence.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Establish evidence-based medicine (HEDIS) goals for the population related to "
            "diabetes management: Hemoglobin A1c (HbA1c) testing, HbA1c control (<7.0%), "
            "retinal eye exam performed, screening for neuropathy, blood pressure control "
            "(<130/80 mm Hg), and medical attention for nephropathy. "
            "Implement a mail-out reminder system to members' home addresses and connect "
            "non-compliance to benefit plan incentives/disincentives.",
            self.S["Body"]
        ))

    # ── Section 11: Disease Group Risk Stratification ─────────────────────────

    def _section_11_risk_groups(self, story, charts):
        hdr = self._section_header_table("11. Disease Group Risk Stratification")
        hdr._toc_entry = "11. Disease Group Risk Stratification"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The population was stratified into seven disease-risk groups based on their "
            "chronic disease burden and annual medical expenditures. "
            "Groups 1–2 represent relatively healthy individuals; "
            "Groups 3–7 carry one or more chronic conditions.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.15*inch))

        cd = charts.get("risk_groups")
        if not cd or not cd.data:
            story.append(Paragraph("No risk group data available.", self.S["Body"]))
            return

        # Pivot: risk group × year
        story.append(self._pivot_risk_data(cd.data))
        story.append(Spacer(1, 0.2*inch))

        # Chart: total $ per risk group (summed across years)
        years = self.report_years
        groups = sorted({str(r.get("RISK_GROUP", "")) for r in cd.data})
        lookup: Dict[tuple, dict] = {}
        for r in cd.data:
            lookup[(str(r["RISK_GROUP"]), str(r["FILE_YEAR"]))] = r

        colors = ["#1F3864","#2E86AB","#A8DADC","#457B9D","#E63946","#F1A208","#6A994E"]
        datasets = []
        for i, y in enumerate(years):
            datasets.append({
                "label": str(y),
                "data": [float(lookup.get((g, y), {}).get("TOTAL_AMT") or 0) for g in groups],
                "backgroundColor": colors[i % len(colors)],
            })

        cfg = {
            "type": "bar",
            "data": {"labels": groups, "datasets": datasets},
            "options": {
                "plugins": {
                    "datalabels": {"display": False},
                    "legend": {"position": "bottom"}
                },
                "title": {"display": True, "text": "Total Amount Paid - Disease Group Risk Stratification"},
                "scales": {"yAxes": [{"ticks": {"beginAtZero": True}}]}
            }
        }
        img = self._fetch_quickchart(cfg, "chart_sec11.png")
        if img:
            story.append(RLImage(img, width=7.0*inch, height=3.2*inch))
            story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "A small proportion of members in Groups 5–7 account for a disproportionate share "
            "of total medical expenditures. Early identification and intensive case management "
            "of emerging high-risk members can significantly reduce costs.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Implement intensive case management for Group 5–7 members. Target Group 2–3 "
            "members with population health management strategies combining clinical and "
            "lifestyle programs to prevent progression to higher risk groups.",
            self.S["Body"]
        ))

    # ── Section 12: Lifestyle Modifiable & Preventive Utilization ─────────────

    def _section_12_lifestyle(self, story, charts):
        hdr = self._section_header_table(
            "12. Expenditures Related to Lifestyle Modifiable & Preventive Utilization")
        hdr._toc_entry = "12. Expenditures Related to Lifestyle Modifiable & Preventive Utilization"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following section identifies expenditures related to lifestyle-modifiable "
            "conditions (obesity, tobacco use, alcohol use, substance abuse, sedentary "
            "lifestyle, stress/mental health) and preventive utilization.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("lifestyle")
        if not cd or not cd.data:
            story.append(Paragraph("No lifestyle-modifiable expenditure data available.", self.S["Body"]))
        else:
            years = self.report_years
            cats = []
            seen: set = set()
            for r in sorted(cd.data, key=lambda x: -float(x.get("TOTAL_AMT") or 0)):
                c = str(r.get("CATEGORY", ""))
                if c and c not in seen:
                    cats.append(c)
                    seen.add(c)

            lookup: Dict[tuple, dict] = {}
            for r in cd.data:
                lookup[(str(r.get("CATEGORY", "")), str(r.get("YR", "")))] = r

            hdr1 = ["Category"] + [y for y in years for _ in range(3)]
            hdr2 = [""] + ["Total $", "Mean $", "N"] * len(years)
            t_data = [hdr1, hdr2]
            for cat in cats:
                row = [cat]
                for y in years:
                    r = lookup.get((cat, y), {})
                    row += [
                        f"${float(r.get('TOTAL_AMT') or 0):,.0f}" if r else "",
                        f"${float(r.get('MEAN_AMT') or 0):,.0f}" if r else "",
                        str(int(r.get("N") or 0)) if r else "",
                    ]
                t_data.append(row)

            # Wrap data
            wrapped_data = []
            for r_idx, row in enumerate(t_data):
                wrapped_row = []
                for c_idx, cell in enumerate(row):
                    if isinstance(cell, str) and cell:
                        is_header = (r_idx < 2)
                        fn = "Helvetica-Bold" if is_header else "Helvetica"
                        tc = rl_colors.grey if is_header else BLACK
                        al = TA_LEFT if c_idx == 0 else TA_RIGHT
                        wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                    else:
                        wrapped_row.append(cell)
                wrapped_data.append(wrapped_row)

            ncols = len(hdr1)
            cat_w = 2.5 * inch
            col_w = [cat_w] + [(7.3 * inch - cat_w) / (ncols - 1)] * (ncols - 1)
            t = Table(wrapped_data, colWidths=col_w, repeatRows=2)
            style = [
                ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 7),
                ("ALIGN",         (0,0), (-1,-1), "CENTER"),
                ("ALIGN",         (0,0), (0,-1), "LEFT"),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0,2), (-1,-1), [WHITE, LTGREY]),
                ("TOPPADDING",    (0,0), (-1,-1), 3),
                ("BOTTOMPADDING", (0,0), (-1,-1), 3),
                ("LEFTPADDING",   (0,0), (-1,-1), 4),
                ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
            ]
            for i, _ in enumerate(years):
                c = 1 + i * 3
                style.append(("SPAN", (c,0), (c+2,0)))
            t.setStyle(TableStyle(style))
            story.append(t)
            story.append(Spacer(1, 0.15*inch))

            # Chart
            cat_totals: Dict[str, float] = {}
            for r in cd.data:
                c = str(r.get("CATEGORY", ""))
                cat_totals[c] = cat_totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
            sorted_cats = sorted(cat_totals.items(), key=lambda x: x[1], reverse=True)
            cfg = {
                "type": "horizontalBar",
                "data": {
                    "labels": [x[0] for x in sorted_cats],
                    "datasets": [{"label": "Total $", "data": [x[1] for x in sorted_cats],
                                  "backgroundColor": "#227EE4"}]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end",
                                       "font": {"weight": "bold", "size": 8}},
                        "legend": {"position": "bottom"}
                    },
                    "title": {"display": True, "text": "Total Amount Paid - Lifestyle Modifiable Conditions"}
                }
            }
            img = self._fetch_quickchart(cfg, "chart_sec12.png")
            if img:
                story.append(RLImage(img, width=7.0*inch, height=3.0*inch))
                story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "Lifestyle-modifiable conditions represent a significant and preventable share "
            "of medical expenditures. Addressing root-cause behaviors through targeted "
            "wellness programs can reduce both claims and absenteeism.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Implement evidence-based wellness programs targeting obesity, tobacco cessation, "
            "stress management, and physical activity. Incentivize preventive utilization "
            "through benefit design to reduce downstream chronic disease costs.",
            self.S["Body"]
        ))

    # ── Section 13: Estimated Lost Time & Cost due to Health Disparities ──────

    def _section_13_health_disparities(self, story, charts):
        hdr = self._section_header_table("13. Estimated Lost Time & Cost due to Health Disparities")
        hdr._toc_entry = "13. Estimated Lost Time & Cost due to Health Disparities"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following section estimates productivity loss associated with high-frequency "
            "diagnostic categories. Conditions coded as symptoms, signs, or abnormal clinical "
            "lab findings often indicate poor patient-physician communication and may proxy "
            "for unresolved conditions contributing to absenteeism.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("health_disparities")
        if not cd or not cd.data:
            story.append(Paragraph("No health disparities data available.", self.S["Body"]))
            return

        years = self.report_years
        # Top 10 categories by frequency (N)
        cat_n: Dict[str, int] = {}
        for r in cd.data:
            c = str(r.get("CATEGORY", ""))
            cat_n[c] = cat_n.get(c, 0) + int(r.get("N") or 0)
        top10 = [k for k, _ in sorted(cat_n.items(), key=lambda x: x[1], reverse=True)[:10]]

        lookup: Dict[tuple, dict] = {}
        for r in cd.data:
            lookup[(str(r.get("CATEGORY", "")), str(r.get("YR", "")))] = r

        hdr1 = ["Diagnostic Category"] + [y for y in years for _ in range(2)]
        hdr2 = [""] + ["Total $", "N"] * len(years)
        t_data = [hdr1, hdr2]
        for cat in top10:
            row = [cat]
            for y in years:
                r = lookup.get((cat, y), {})
                row += [
                    f"${float(r.get('TOTAL_AMT') or 0):,.0f}" if r else "",
                    str(int(r.get("N") or 0)) if r else "",
                ]
            t_data.append(row)

        # Wrap data
        wrapped_data = []
        for r_idx, row in enumerate(t_data):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx < 2)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = rl_colors.grey if is_header else BLACK
                    al = TA_LEFT if c_idx == 0 else TA_RIGHT
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_data.append(wrapped_row)

        ncols = len(hdr1)
        cat_w = 2.8 * inch
        col_w = [cat_w] + [(7.3 * inch - cat_w) / (ncols - 1)] * (ncols - 1)
        t = Table(wrapped_data, colWidths=col_w, repeatRows=2)
        style = [
            ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
            ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
            ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
            ("FONTSIZE",      (0,0), (-1,-1), 7),
            ("ALIGN",         (0,0), (-1,-1), "CENTER"),
            ("ALIGN",         (0,0), (0,-1), "LEFT"),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0,2), (-1,-1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0,0), (-1,-1), 3),
            ("BOTTOMPADDING", (0,0), (-1,-1), 3),
            ("LEFTPADDING",   (0,0), (-1,-1), 4),
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
        ]
        for i, _ in enumerate(years):
            c = 1 + i * 2
            style.append(("SPAN", (c,0), (c+1,0)))
        t.setStyle(TableStyle(style))
        story.append(t)
        story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "High utilization of diagnostic categories such as 'Symptoms, Signs, and Abnormal "
            "Clinical Lab Findings' may indicate poor patient-physician communication, "
            "contributing to lost productivity and avoidable costs.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Implement personal electronic health records so patients can document symptoms "
            "prior to physician visits. Encourage physicians to resolve diagnoses rather than "
            "leaving conditions coded as unspecified symptoms or signs.",
            self.S["Body"]
        ))

    # ── Section 14: Preventive Screening Compliance ───────────────────────────

    def _section_14_preventive_screening(self, story, charts):
        hdr = self._section_header_table("14. Preventive Screening Compliance")
        hdr._toc_entry = "14. Preventive Screening Compliance"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following section identifies preventive cancer screening compliance rates "
            "compared to HEDIS national benchmarks.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        HEDIS_BENCHMARKS = {
            "BREAST CANCER":   73.7,
            "CERVICAL CANCER": 76.2,
            "COLON CANCER":    65.0,
        }
        screen_map = {
            "BREAST CANCER":   charts.get("breast_screening"),
            "CERVICAL CANCER": charts.get("cervical_screening"),
            "COLON CANCER":    charts.get("colon_screening"),
        }
        LABELS = {
            "BREAST CANCER":   "Breast Cancer Screening Compliance",
            "CERVICAL CANCER": "Cervical Cancer Screening Compliance",
            "COLON CANCER":    "Colorectal Cancer Screening Compliance",
        }

        for cancer_key, cd in screen_map.items():
            story.append(Paragraph(f"<b>{LABELS[cancer_key]}</b>", self.S["SubHead"]))
            if not cd or not cd.data:
                story.append(Paragraph("No data available.", self.S["Body"]))
                story.append(Spacer(1, 0.1*inch))
                continue

            years = [str(r.get("YEAR", "")) for r in cd.data]
            eligible = [int(r.get("ELIGIBLE_N") or 0) for r in cd.data]
            screened = [int(r.get("SCREENED_N") or 0) for r in cd.data]
            rates    = [float(r.get("SCREENING_RATE_PCT") or 0) for r in cd.data]
            benchmark = HEDIS_BENCHMARKS[cancer_key]

            hdr = ["Year", "Eligible N", "Screened N", "Screening Rate %",
                   f"HEDIS Benchmark ({benchmark}%)"]
            t_data = [hdr]
            for i, y in enumerate(years):
                t_data.append([y, f"{eligible[i]:,}", f"{screened[i]:,}",
                                f"{rates[i]:.1f}%", f"{benchmark:.1f}%"])

            col_w = [1.0*inch, 1.1*inch, 1.1*inch, 1.5*inch, 2.6*inch]
            t = self._make_table(t_data, col_widths=col_w)
            story.append(t)
            story.append(Spacer(1, 0.1*inch))

            # Chart: bars = screening rate per year, line = HEDIS benchmark
            cfg = {
                "type": "bar",
                "data": {
                    "labels": years,
                    "datasets": [
                        {"type": "bar",  "label": "Screening Rate %",
                         "data": rates, "backgroundColor": "#227EE4"},
                        {"type": "line", "label": f"HEDIS Benchmark ({benchmark}%)",
                         "data": [benchmark] * len(years),
                         "borderColor": "#E63946", "borderDash": [6,3],
                         "backgroundColor": "transparent", "fill": False, "pointRadius": 0}
                    ]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "top", "anchor": "end",
                                       "font": {"weight": "bold", "size": 9}},
                        "legend": {"position": "bottom"}
                    },
                    "scales": {"yAxes": [{"ticks": {"beginAtZero": True, "max": 100}}]}
                }
            }
            img = self._fetch_quickchart(cfg, f"chart_sec14_{cancer_key.split()[0].lower()}.png")
            if img:
                story.append(RLImage(img, width=7.0*inch, height=2.8*inch))
                story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "All cancer screening rates are significantly below HEDIS national benchmarks. "
            "Early detection through preventive screening can dramatically reduce treatment "
            "costs and improve member outcomes.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Increase screening awareness through member outreach, targeted mailings, and "
            "benefit design incentives. Remove access barriers such as cost-sharing for "
            "preventive screenings. Engage providers with performance alerts for eligible "
            "but unscreened patients.",
            self.S["Body"]
        ))

    # ── Section 15: Value of Preventive Screenings ────────────────────────────

    def _section_15_screening_value(self, story, charts):
        hdr = self._section_header_table("15. Value of Preventive Screenings")
        hdr._toc_entry = "15. Value of Preventive Screenings"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following section identifies the value of preventive screenings by "
            "tracking cancer diagnoses identified after a screening event.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("screening_value")
        if not cd or not cd.data:
            story.append(Paragraph("No preventive screening value data available.", self.S["Body"]))
        else:
            years = self.report_years
            cancers = sorted({str(r.get("CANCER_SCREENING", "")) for r in cd.data})
            lookup: Dict[tuple, dict] = {}
            for r in cd.data:
                lookup[(str(r.get("CANCER_SCREENING", "")), str(r.get("YEAR", "")))] = r

            # Simplified headers
            hdr1 = [self._wrap_cell("Cancer Screening", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), ""])
            
            hdr2 = [""]
            for _ in years:
                hdr2.extend([
                    self._wrap_cell("Diagnoses Identified", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("Treatment Cost", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
                ])
            t_data = [hdr1, hdr2]
            
            for cancer in cancers:
                row = [self._wrap_cell(cancer, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                for y in years:
                    r = lookup.get((cancer, y), {})
                    row += [
                        self._wrap_cell(str(int(r.get("DIAGNOSED_N") or 0)) if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(f"${float(r.get('TREATMENT_COST') or 0):,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    ]
                t_data.append(row)

            # Wrap data
            wrapped_data = []
            for r_idx, row in enumerate(t_data):
                wrapped_row = []
                for c_idx, cell in enumerate(row):
                    if isinstance(cell, str) and cell:
                        is_header = (r_idx < 2)
                        fn = "Helvetica-Bold" if is_header else "Helvetica"
                        tc = rl_colors.grey if is_header else BLACK
                        al = TA_LEFT if c_idx == 0 else TA_RIGHT
                        wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                    else:
                        wrapped_row.append(cell)
                wrapped_data.append(wrapped_row)

            ncols = len(hdr1)
            cat_w = 2.3 * inch
            col_w = [cat_w] + [(7.3 * inch - cat_w) / (ncols - 1)] * (ncols - 1)
            t = Table(t_data, colWidths=col_w, repeatRows=2)
            style = [
                ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 7),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0,2), (-1,-1), [WHITE, LTGREY]),
                ("TOPPADDING",    (0,0), (-1,-1), 2),
                ("BOTTOMPADDING", (0,0), (-1,-1), 2),
                ("LEFTPADDING",   (0,0), (-1,-1), 2),
                ("RIGHTPADDING",  (0,0), (-1,-1), 2),
                ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
                ("SPAN",          (0,0), (0,1)),
            ]
            col_idx = 1
            for _ in years:
                style.append(("SPAN", (col_idx, 0), (col_idx + 1, 0)))
                col_idx += 2
            t.setStyle(TableStyle(style))
            story.append(t)
            story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "Preventive screenings identified cancer diagnoses that enabled early-stage "
            "treatment at significantly lower costs. The value of early detection far "
            "outweighs the cost of screening programs.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Continue investing in preventive screening programs. Track outcomes of "
            "diagnosed members to measure total cost offset. Promote the value of "
            "screenings to members to increase compliance rates.",
            self.S["Body"]
        ))

    # ── Section 16: Work-Related Musculoskeletal Expenditures ─────────────────

    def _section_16_musculoskeletal(self, story, charts):
        hdr = self._section_header_table(
            "16. Potentially Work-Related Musculoskeletal Expenditures")
        hdr._toc_entry = "16. Potentially Work-Related Musculoskeletal Expenditures"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following section identifies musculoskeletal expenditures that may be "
            "potentially work-related based on body-part and diagnosis classification.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("msk_work")
        if not cd or not cd.data:
            story.append(Paragraph("No work-related musculoskeletal data available.", self.S["Body"]))
        else:
            years = self.report_years
            parts = []
            seen: set = set()
            for r in sorted(cd.data, key=lambda x: -float(x.get("TOTAL_AMT") or 0)):
                bp = str(r.get("BODY_PART", ""))
                if bp and bp not in seen:
                    parts.append(bp)
                    seen.add(bp)

            lookup: Dict[tuple, dict] = {}
            for r in cd.data:
                lookup[(str(r.get("BODY_PART", "")), str(r.get("YR", "")))] = r

            # Simplified headers
            hdr1 = [self._wrap_cell("Body Part", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), "", ""])
            
            hdr2 = [""]
            for _ in years:
                hdr2.extend([
                    self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
                ])
            t_data = [hdr1, hdr2]
            for bp in parts:
                row = [self._wrap_cell(bp, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                for y in years:
                    r = lookup.get((bp, y), {})
                    row += [
                        self._wrap_cell(f"${float(r.get('TOTAL_AMT') or 0):,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(f"${float(r.get('MEAN_AMT') or 0):,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(str(int(r.get("N") or 0)) if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    ]
                t_data.append(row)

            # Wrap data
            wrapped_data = []
            for r_idx, row in enumerate(t_data):
                wrapped_row = []
                for c_idx, cell in enumerate(row):
                    if isinstance(cell, str) and cell:
                        is_header = (r_idx < 2)
                        fn = "Helvetica-Bold" if is_header else "Helvetica"
                        tc = rl_colors.grey if is_header else BLACK
                        al = TA_LEFT if c_idx == 0 else TA_RIGHT
                        wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                    else:
                        wrapped_row.append(cell)
                wrapped_data.append(wrapped_row)

            ncols = len(hdr1)
            bp_w = 1.8 * inch
            col_w = [bp_w] + [(7.3 * inch - bp_w) / (ncols - 1)] * (ncols - 1)
            t = Table(t_data, colWidths=col_w, repeatRows=2)
            style = [
                ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 7),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0,2), (-1,-1), [WHITE, LTGREY]),
                ("TOPPADDING",    (0,0), (-1,-1), 2),
                ("BOTTOMPADDING", (0,0), (-1,-1), 2),
                ("LEFTPADDING",   (0,0), (-1,-1), 2),
                ("RIGHTPADDING",  (0,0), (-1,-1), 2),
                ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
                ("SPAN",          (0,0), (0,1)),
            ]
            col_idx = 1
            for _ in years:
                style.append(("SPAN", (col_idx, 0), (col_idx+2, 0)))
                col_idx += 3
            t.setStyle(TableStyle(style))
            story.append(t)
            story.append(Spacer(1, 0.2*inch))

            # Chart
            bp_totals: Dict[str, float] = {}
            for r in cd.data:
                bp = str(r.get("BODY_PART", ""))
                bp_totals[bp] = bp_totals.get(bp, 0) + float(r.get("TOTAL_AMT") or 0)
            sorted_bp = sorted(bp_totals.items(), key=lambda x: x[1], reverse=True)
            cfg = {
                "type": "horizontalBar",
                "data": {
                    "labels": [x[0] for x in sorted_bp],
                    "datasets": [{"label": "Total $", "data": [x[1] for x in sorted_bp],
                                  "backgroundColor": "#227EE4"}]
                },
                "options": {
                    "plugins": {
                        "datalabels": {"display": True, "align": "right", "anchor": "end",
                                       "font": {"weight": "bold", "size": 8}},
                        "legend": {"position": "bottom"}
                    },
                    "title": {"display": True,
                              "text": "Total Amount Paid - Potentially Work-Related MSK"}
                }
            }
            img = self._fetch_quickchart(cfg, "chart_sec16.png")
            if img:
                story.append(RLImage(img, width=7.0*inch, height=3.0*inch))
                story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "Musculoskeletal conditions represent a leading driver of both medical expenditure "
            "and lost productivity. Work-related MSK injuries are often preventable through "
            "ergonomic programs and pre-employment physical assessments.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Implement pre-employment physical ability testing and conduct job task analysis "
            "for high-risk positions. Deploy ergonomic assessments and early intervention "
            "physical therapy programs to reduce MSK-related lost time and claims.",
            self.S["Body"]
        ))

    # ── Section 17: Catastrophic Claims ──────────────────────────────────────

    def _section_17_catastrophic(self, story, charts):
        hdr = self._section_header_table("17. Catastrophic Claims")
        hdr._toc_entry = "17. Catastrophic Claims"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "Catastrophic claims are defined as individual claims exceeding $100,000. "
            "These high-cost events require dedicated case management to optimize outcomes "
            "and contain costs.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("catastrophic")
        if not cd or not cd.data:
            story.append(Paragraph("No catastrophic claims data available for the selected years.", self.S["Body"]))
        else:
            # Simplified headers
            hdr = [
                self._wrap_cell("Year", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT, text_color=rl_colors.grey),
                self._wrap_cell("Number of Claims", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("Total $", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("Mean $", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ]
            t_data = [hdr]
            sum_claims = 0
            sum_total = 0.0
            for r in cd.data:
                claims = int(r.get("CLAIM_COUNT") or 0)
                tot    = float(r.get("TOTAL_AMT") or 0)
                mean   = float(r.get("MEAN_AMT") or 0)
                sum_claims += claims
                sum_total  += tot
                t_data.append([
                    self._wrap_cell(str(r["YR"]), font_name="Helvetica", font_size=8, alignment=TA_LEFT),
                    self._wrap_cell(f"{claims:,}", font_name="Helvetica", font_size=8, alignment=TA_RIGHT),
                    self._wrap_cell(f"${tot:,.0f}", font_name="Helvetica", font_size=8, alignment=TA_RIGHT),
                    self._wrap_cell(f"${mean:,.0f}", font_name="Helvetica", font_size=8, alignment=TA_RIGHT)
                ])
            mean_overall = sum_total / sum_claims if sum_claims else 0
            t_data.append([
                self._wrap_cell("Total", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT),
                self._wrap_cell(f"{sum_claims:,}", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT),
                self._wrap_cell(f"${sum_total:,.0f}", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT),
                self._wrap_cell(f"${mean_overall:,.0f}", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT)
            ])
            col_w = [1.2*inch, 1.8*inch, 2.0*inch, 2.3*inch]
            t = Table(t_data, colWidths=col_w, repeatRows=1)
            t.setStyle(TableStyle([
                ("BACKGROUND",    (0,0), (-1,0), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,0), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,0), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 8),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0,1), (-1,-2), [WHITE, LTGREY]),
                ("BACKGROUND",    (0,-1), (-1,-1), LTBLUE),
                ("TOPPADDING",    (0,0), (-1,-1), 3),
                ("BOTTOMPADDING", (0,0), (-1,-1), 3),
                ("LEFTPADDING",   (0,0), (-1,-1), 2),
                ("RIGHTPADDING",  (0,0), (-1,-1), 2),
                ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
            ]))
            story.append(t)
            story.append(Spacer(1, 0.2*inch))

            if cd.labels and cd.values:
                cfg = {
                    "type": "bar",
                    "data": {
                        "labels": cd.labels,
                        "datasets": [{"label": "Total $", "data": cd.values,
                                      "backgroundColor": "#E63946"}]
                    },
                    "options": {
                        "plugins": {
                            "datalabels": {"display": True, "align": "top", "anchor": "end",
                                           "font": {"weight": "bold", "size": 9}},
                            "legend": {"position": "bottom"}
                        },
                        "title": {"display": True, "text": "Catastrophic Claims (≥ $100,000)"},
                        "scales": {"yAxes": [{"ticks": {"beginAtZero": True}}]}
                    }
                }
                img = self._fetch_quickchart(cfg, "chart_sec17.png")
                if img:
                    story.append(RLImage(img, width=7.0*inch, height=3.0*inch))
                    story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "Catastrophic claimants drive disproportionate medical costs. Dedicated case "
            "management, treatment navigation, and centers-of-excellence referrals can "
            "improve outcomes and reduce total cost of care.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Engage catastrophic claimants with dedicated case managers. Establish "
            "centers-of-excellence relationships for high-cost conditions such as cancer, "
            "transplants, and complex cardiac procedures.",
            self.S["Body"]
        ))

    # ── Section 18: Inpatient / Outpatient / ER Expenditures ─────────────────

    def _section_18_inpatient_er(self, story, charts):
        hdr = self._section_header_table("18. Inpatient, Outpatient & Emergency Room Expenditures")
        hdr._toc_entry = "18. Inpatient, Outpatient & Emergency Room Expenditures"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following section identifies expenditures related to Inpatient, "
            "Outpatient, and Emergency Room utilization.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("inpatient_er")
        if not cd or not cd.data:
            story.append(Paragraph("No inpatient/outpatient/ER data available.", self.S["Body"]))
            return

        years = self.report_years
        types = ["Inpatient", "Outpatient", "Emergency Room"]

        lookup: Dict[tuple, dict] = {}
        for r in cd.data:
            lookup[(str(r.get("SERVICE_TYPE", "")), str(r.get("YR", "")))] = r

        # Simplified headers
        hdr1 = [self._wrap_cell("Service Type", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for y in years:
            hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), "", ""])
        
        hdr2 = [""]
        for _ in years:
            hdr2.extend([
                self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ])
        t_data = [hdr1, hdr2]
        for stype in types:
            row = [self._wrap_cell(stype, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
            for y in years:
                r = lookup.get((stype, y), {})
                row += [
                    self._wrap_cell(f"${float(r.get('TOTAL_AMT') or 0):,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(f"${float(r.get('MEAN_AMT') or 0):,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(str(int(r.get("N") or 0)) if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                ]
            t_data.append(row)

        ncols = len(hdr1)
        type_w = 1.5 * inch
        col_w = [type_w] + [(7.3 * inch - type_w) / (ncols - 1)] * (ncols - 1)
        t = Table(t_data, colWidths=col_w, repeatRows=2)
        style = [
            ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
            ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
            ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
            ("FONTSIZE",      (0,0), (-1,-1), 7),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0,2), (-1,-1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0,0), (-1,-1), 2),
            ("BOTTOMPADDING", (0,0), (-1,-1), 2),
            ("LEFTPADDING",   (0,0), (-1,-1), 2),
            ("RIGHTPADDING",  (0,0), (-1,-1), 2),
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
            ("SPAN",          (0,0), (0,1)),
        ]
        col_idx = 1
        for _ in years:
            style.append(("SPAN", (col_idx, 0), (col_idx+2, 0)))
            col_idx += 3
        t.setStyle(TableStyle(style))
        story.append(t)
        story.append(Spacer(1, 0.2*inch))

        # Grouped bar chart
        colors = ["#1F3864", "#2E86AB", "#A8DADC"]
        datasets = []
        for i, stype in enumerate(types):
            datasets.append({
                "label": stype,
                "data": [float(lookup.get((stype, y), {}).get("TOTAL_AMT") or 0) for y in years],
                "backgroundColor": colors[i],
            })
        cfg = {
            "type": "bar",
            "data": {"labels": years, "datasets": datasets},
            "options": {
                "plugins": {
                    "datalabels": {"display": False},
                    "legend": {"position": "bottom"}
                },
                "title": {"display": True,
                          "text": "Total Amount Paid - Inpatient / Outpatient / ER"},
                "scales": {"yAxes": [{"ticks": {"beginAtZero": True}}]}
            }
        }
        img = self._fetch_quickchart(cfg, "chart_sec18.png")
        if img:
            story.append(RLImage(img, width=7.0*inch, height=3.2*inch))
            story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "Avoidable inpatient admissions and emergency room visits represent significant "
            "cost-reduction opportunities. Post-discharge follow-up and ER diversion "
            "programs can reduce readmissions and unnecessary utilization.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Implement ER diversion programs and post-discharge follow-up protocols. "
            "Establish 24/7 nurse triage lines and telemedicine access to reduce "
            "non-emergency ER utilization. Target frequent inpatient members with "
            "proactive care management.",
            self.S["Body"]
        ))

    # ── Section 19: Avoidable Emergency Room Visits ───────────────────────────

    def _section_19_avoidable_er(self, story, charts):
        hdr = self._section_header_table("19. Avoidable Emergency Room Visits")
        hdr._toc_entry = "19. Avoidable Emergency Room Visits"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "Avoidable ER visits are defined as emergency room encounters for conditions "
            "that could have been treated in a primary care or urgent care setting. "
            "The following diagnoses represent avoidable ER utilization.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("avoidable_er")
        if not cd or not cd.data:
            story.append(Paragraph("No avoidable ER visit data available for the selected years.", self.S["Body"]))
        else:
            years = self.report_years
            diags = []
            seen: set = set()
            for r in sorted(cd.data, key=lambda x: -float(x.get("TOTAL_AMT") or 0)):
                d = str(r.get("DIAGNOSIS", ""))
                if d and d not in seen:
                    diags.append(d)
                    seen.add(d)

            lookup: Dict[tuple, dict] = {}
            for r in cd.data:
                lookup[(str(r.get("DIAGNOSIS", "")), str(r.get("YR", "")))] = r

            # Simplified headers
            hdr1 = [self._wrap_cell("Diagnosis", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
            for y in years:
                hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), ""])
            hdr1.append(self._wrap_cell("Total", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey))
            
            hdr2 = [""]
            for _ in years:
                hdr2.extend([
                    self._wrap_cell("Total $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                    self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
                ])
            hdr2.append(self._wrap_cell("Total $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey))
            t_data = [hdr1, hdr2]
            
            grand_total = 0.0
            for diag in diags:
                row = [self._wrap_cell(diag, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
                grand = 0.0
                for y in years:
                    r = lookup.get((diag, y), {})
                    v = float(r.get("TOTAL_AMT") or 0)
                    grand += v
                    row += [
                        self._wrap_cell(f"${v:,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                        self._wrap_cell(str(int(r.get("N") or 0)) if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    ]
                row.append(self._wrap_cell(f"${grand:,.0f}", font_name="Helvetica", font_size=7, alignment=TA_RIGHT))
                grand_total += grand
                t_data.append(row)

            # Total row
            tot_row = [self._wrap_cell("Total", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT)]
            for y in years:
                yr_tot = sum(float(lookup.get((d, y), {}).get("TOTAL_AMT") or 0) for d in diags)
                yr_n   = sum(int(lookup.get((d, y), {}).get("N") or 0) for d in diags)
                tot_row += [
                    self._wrap_cell(f"${yr_tot:,.0f}", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(str(yr_n), font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT)
                ]
            tot_row.append(self._wrap_cell(f"${grand_total:,.0f}", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT))
            t_data.append(tot_row)

            ncols = len(hdr1)
            diag_w = 3.0 * inch
            col_w = [diag_w] + [(7.3 * inch - diag_w) / (ncols - 1)] * (ncols - 1)
            t = Table(t_data, colWidths=col_w, repeatRows=2)
            style = [
                ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 7),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0,2), (-1,-2), [WHITE, LTGREY]),
                ("BACKGROUND",    (0,-1), (-1,-1), LTBLUE),
                ("TOPPADDING",    (0,0), (-1,-1), 2),
                ("BOTTOMPADDING", (0,0), (-1,-1), 2),
                ("LEFTPADDING",   (0,0), (-1,-1), 2),
                ("RIGHTPADDING",  (0,0), (-1,-1), 2),
                ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
                ("SPAN",          (0,0), (0,1)),
                ("SPAN",          (-1,0), (-1,1)),
            ]
            col_idx = 1
            for _ in years:
                style.append(("SPAN", (col_idx, 0), (col_idx+1, 0)))
                col_idx += 2
            t.setStyle(TableStyle(style))
            story.append(t)
            story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "Avoidable ER visits represent unnecessary cost to the plan and inconvenience "
            "to members. Assigning a primary care physician to frequent ER utilizers "
            "reduces avoidable ER visits by approximately 58%.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Assign primary care physicians to frequent ER utilizers. Distribute medical "
            "self-care guides to members. Establish 24/7 nurse advice lines and limit "
            "opioid prescribing for non-emergency conditions.",
            self.S["Body"]
        ))

    # ── Section 20: Primary Care Physician & Specialty Expenditures ───────────

    def _section_20_pcp_specialty(self, story, charts):
        hdr = self._section_header_table("20. Primary Care Physician & Specialty Expenditures")
        hdr._toc_entry = "20. Primary Care Physician & Specialty Expenditures"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following section identifies expenditures related to Primary Care Physician "
            "services versus Other/Specialty services.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("pcp_specialty")
        if not cd or not cd.data:
            story.append(Paragraph("No PCP/Specialty data available.", self.S["Body"]))
            return

        years = self.report_years
        classes = ["Primary Care Physician Services", "Other/Specialty Services"]

        lookup: Dict[tuple, dict] = {}
        for r in cd.data:
            lookup[(str(r.get("PROVIDER_CLASS", "")), str(r.get("YR", "")))] = r

        # Simplified headers
        hdr1 = [self._wrap_cell("Provider Type", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for y in years:
            hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), "", ""])
        
        hdr2 = [""]
        for _ in years:
            hdr2.extend([
                self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ])
        t_data = [hdr1, hdr2]
        for pc in classes:
            row = [self._wrap_cell(pc, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
            for y in years:
                r = lookup.get((pc, y), {})
                row += [
                    self._wrap_cell(f"${float(r.get('TOTAL_AMT') or 0):,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(f"${float(r.get('MEAN_AMT') or 0):,.0f}" if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(str(int(r.get("N") or 0)) if r else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                ]
            t_data.append(row)

        ncols = len(hdr1)
        type_w = 2.3 * inch
        col_w = [type_w] + [(7.3 * inch - type_w) / (ncols - 1)] * (ncols - 1)
        t = Table(t_data, colWidths=col_w, repeatRows=2)
        style = [
            ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
            ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
            ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
            ("FONTSIZE",      (0,0), (-1,-1), 7),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0,2), (-1,-1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0,0), (-1,-1), 2),
            ("BOTTOMPADDING", (0,0), (-1,-1), 2),
            ("LEFTPADDING",   (0,0), (-1,-1), 2),
            ("RIGHTPADDING",  (0,0), (-1,-1), 2),
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
            ("SPAN",          (0,0), (0,1)),
        ]
        col_idx = 1
        for _ in years:
            style.append(("SPAN", (col_idx, 0), (col_idx+2, 0)))
            col_idx += 3
        t.setStyle(TableStyle(style))
        story.append(t)
        story.append(Spacer(1, 0.2*inch))

        # Grouped bar chart
        colors = ["#1F3864", "#2E86AB"]
        datasets = []
        for i, pc in enumerate(classes):
            datasets.append({
                "label": pc,
                "data": [float(lookup.get((pc, y), {}).get("TOTAL_AMT") or 0) for y in years],
                "backgroundColor": colors[i],
            })
        cfg = {
            "type": "horizontalBar",
            "data": {"labels": years, "datasets": datasets},
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "right", "anchor": "end",
                                   "font": {"weight": "bold", "size": 8}},
                    "legend": {"position": "bottom"}
                },
                "title": {"display": True,
                          "text": "Total Amount Paid - PCP vs Specialty"}
            }
        }
        img = self._fetch_quickchart(cfg, "chart_sec20.png")
        if img:
            story.append(RLImage(img, width=7.0*inch, height=3.0*inch))
            story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "Specialist utilization without primary care coordination drives unnecessary "
            "cost and duplication of services. A primary care-centered model reduces "
            "total spend while improving care continuity.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Encourage primary care-led care coordination and appropriate specialty referral "
            "pathways. Implement patient-centered medical home models to reduce fragmented "
            "specialty utilization.",
            self.S["Body"]
        ))

    # ── Section 21: Overall Pharmacy Expenditures by Year ────────────────────

    def _section_21_pharmacy_by_year(self, story, charts):
        hdr = self._section_header_table("21. Overall Pharmacy Expenditures by Year")
        hdr._toc_entry = "21. Overall Pharmacy Expenditures by Year"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "Pharmacy expenditures* (based on paid claims) were as follows:",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("pharm_by_year")
        if not cd or not cd.data:
            story.append(Paragraph("No pharmacy by year data available.", self.S["Body"]))
            return

        years  = [str(r["YR"]) for r in cd.data]
        tot    = [float(r.get("TOTAL_AMT") or 0) for r in cd.data]
        mean   = [float(r.get("MEAN_AMT") or 0) for r in cd.data]
        n_val  = [int(r.get("N") or 0) for r in cd.data]

        # Simplified headers
        hdr = [
            self._wrap_cell("Reporting Year", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT, text_color=rl_colors.grey),
            self._wrap_cell("Total $", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey),
            self._wrap_cell("Mean $", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey),
            self._wrap_cell("N", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey)
        ]
        t_data = [hdr]
        sum_tot = 0.0; sum_n = 0
        for i, y in enumerate(years):
            t_data.append([
                self._wrap_cell(y, font_name="Helvetica", font_size=8, alignment=TA_LEFT),
                self._wrap_cell(f"${tot[i]:,.0f}", font_name="Helvetica", font_size=8, alignment=TA_RIGHT),
                self._wrap_cell(f"${mean[i]:,.0f}", font_name="Helvetica", font_size=8, alignment=TA_RIGHT),
                self._wrap_cell(f"{n_val[i]:,}", font_name="Helvetica", font_size=8, alignment=TA_RIGHT)
            ])
            sum_tot += tot[i]; sum_n += n_val[i]
        t_data.append([
            self._wrap_cell("Total", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT),
            self._wrap_cell(f"${sum_tot:,.0f}", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT),
            self._wrap_cell(f"${(sum_tot/sum_n if sum_n else 0):,.0f}", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT),
            self._wrap_cell(f"{sum_n:,}", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT)
        ])

        t = Table(t_data, colWidths=[2.5*inch, 1.6*inch, 1.6*inch, 1.6*inch])
        style = [
            ("BACKGROUND",    (0,0), (-1,0), LTBLUE),
            ("TEXTCOLOR",     (0,0), (-1,0), rl_colors.grey),
            ("FONTNAME",      (0,0), (-1,0), "Helvetica-Bold"),
            ("FONTSIZE",      (0,0), (-1,-1), 8),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0,1), (-1,-2), [WHITE, LTGREY]),
            ("BACKGROUND",    (0,-1), (-1,-1), LTBLUE),
            ("TOPPADDING",    (0,0), (-1,-1), 3),
            ("BOTTOMPADDING", (0,0), (-1,-1), 3),
            ("LEFTPADDING",   (0,0), (-1,-1), 2),
            ("RIGHTPADDING",  (0,0), (-1,-1), 2),
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
        ]
        t.setStyle(TableStyle(style))
        story.append(t)
        story.append(Spacer(1, 0.1*inch))
        story.append(Paragraph(
            "*Pharmacy expenditures do not include medical-related expenditures.",
            ParagraphStyle("small21", parent=self.S["Body"], fontSize=8, fontName="Helvetica-Oblique")
        ))
        story.append(Spacer(1, 0.2*inch))

        # Total chart
        story.append(Paragraph("<b>Total Amount Paid - Pharmacy - Total Population</b>",
                               ParagraphStyle("cen21", parent=self.S["Body"], alignment=TA_CENTER)))
        cfg_tot = {
            "type": "horizontalBar",
            "data": {
                "labels": years,
                "datasets": [
                    {"label": "TOTAL $", "data": tot, "backgroundColor": "#227EE4"},
                    {"label": "N",       "data": n_val, "backgroundColor": "#8192A6"}
                ]
            },
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "right", "anchor": "end",
                                   "font": {"weight": "bold", "size": 9}},
                    "legend": {"position": "bottom"}
                }
            }
        }
        img_tot = self._fetch_quickchart(cfg_tot, "chart_pharm_yr_tot.png")
        if img_tot:
            story.append(RLImage(img_tot, width=7.0*inch, height=3.5*inch))
            story.append(Spacer(1, 0.2*inch))

        story.append(PageBreak())

        # Mean chart
        story.append(Paragraph("<b>Mean Amount Paid - Pharmacy - Total Population</b>",
                               ParagraphStyle("cen21m", parent=self.S["Body"], alignment=TA_CENTER)))
        cfg_mean = {
            "type": "horizontalBar",
            "data": {
                "labels": years,
                "datasets": [
                    {"label": "MEAN $", "data": mean, "backgroundColor": "#227EE4"},
                    {"label": "N",      "data": n_val, "backgroundColor": "#8192A6"}
                ]
            },
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "right", "anchor": "end",
                                   "font": {"weight": "bold", "size": 9}},
                    "legend": {"position": "bottom"}
                }
            }
        }
        img_mean = self._fetch_quickchart(cfg_mean, "chart_pharm_yr_mean.png")
        if img_mean:
            story.append(RLImage(img_mean, width=7.0*inch, height=3.5*inch))
            story.append(Spacer(1, 0.2*inch))

    # ── Section 22: Overall Pharmacy Expenditures by Quarter ─────────────────

    def _section_22_pharmacy_by_quarter(self, story, charts):
        hdr = self._section_header_table("22. Overall Pharmacy Expenditures by Quarter")
        hdr._toc_entry = "22. Overall Pharmacy Expenditures by Quarter"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph("Pharmacy expenditures were as follows:", self.S["Body"]))
        story.append(Spacer(1, 0.2*inch))

        cd = charts.get("pharm_by_quarter")
        if not cd or not cd.data:
            story.append(Paragraph("No pharmacy by quarter data available.", self.S["Body"]))
            return

        lbls  = [f"{r['YR']}-Q{r['QTR']}" for r in cd.data]
        tot   = [float(r.get("TOTAL_AMT") or 0) for r in cd.data]
        mean  = [float(r.get("MEAN_AMT") or 0) for r in cd.data]
        n_val = [int(r.get("N") or 0) for r in cd.data]

        story.append(Paragraph("<b>Total Amount Paid - Pharmacy - Total Population</b>",
                               ParagraphStyle("cen22", parent=self.S["Body"], alignment=TA_CENTER)))
        cfg_tot = {
            "type": "bar",
            "data": {
                "labels": lbls,
                "datasets": [
                    {"type": "bar",  "label": "TOTAL $", "data": tot,   "backgroundColor": "#227EE4"},
                    {"type": "line", "label": "N",        "data": n_val, "borderColor": "#8192A6", "fill": False}
                ]
            },
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "top", "anchor": "end",
                                   "font": {"weight": "bold", "size": 8}},
                    "legend": {"position": "bottom"}
                }
            }
        }
        img_tot = self._fetch_quickchart(cfg_tot, "chart_pharm_q_tot.png")
        if img_tot:
            story.append(RLImage(img_tot, width=7.0*inch, height=3.5*inch))
            story.append(Spacer(1, 0.2*inch))

        story.append(Paragraph("<b>Mean Amount Paid - Pharmacy - Total Population</b>",
                               ParagraphStyle("cen22m", parent=self.S["Body"], alignment=TA_CENTER)))
        cfg_mean = {
            "type": "bar",
            "data": {
                "labels": lbls,
                "datasets": [
                    {"type": "bar",  "label": "MEAN $", "data": mean,  "backgroundColor": "#227EE4"},
                    {"type": "line", "label": "N",       "data": n_val, "borderColor": "#8192A6", "fill": False}
                ]
            },
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "top", "anchor": "end",
                                   "font": {"weight": "bold", "size": 8}},
                    "legend": {"position": "bottom"}
                }
            }
        }
        img_mean = self._fetch_quickchart(cfg_mean, "chart_pharm_q_mean.png")
        if img_mean:
            story.append(RLImage(img_mean, width=7.0*inch, height=3.5*inch))
            story.append(Spacer(1, 0.2*inch))

    # ── Section 23: Employee/Spouse/Dependent Pharmacy Expenditures ───────────

    def _section_23_pharmacy_relationship(self, story, charts):
        hdr = self._section_header_table("23. Employee/ Spouse/ Dependent Pharmacy Expenditures")
        hdr._toc_entry = "23. Employee/ Spouse/ Dependent Pharmacy Expenditures"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "Pharmacy expenditures related to Employees, Spouses, and Dependents were as follows:",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("pharm_relationship")
        if not cd or not cd.data:
            story.append(Paragraph("No pharmacy relationship data available.", self.S["Body"]))
            return

        years = self.report_years
        rels = ["EMPLOYEE", "SPOUSE", "DEPENDENT"]

        data_by_rel: Dict[str, Dict[str, list]] = {
            rel: {"tot": [], "mean": [], "n": []} for rel in rels
        }
        for rel in rels:
            for y in years:
                row = next((r for r in cd.data
                            if str(r.get("YR","")) == y and str(r.get("RELATIONSHIP","")) == rel), None)
                tot  = float(row["TOTAL_AMT"]) if row and row.get("TOTAL_AMT") is not None else 0.0
                n_v  = int(row["N"]) if row and row.get("N") is not None else 0
                mean = tot / n_v if n_v > 0 else 0.0
                data_by_rel[rel]["tot"].append(tot)
                data_by_rel[rel]["n"].append(n_v)
                data_by_rel[rel]["mean"].append(mean)

        hdr1 = ["Pharmacy Records\nRELATIONSHIP TO\nEMPLOYEE"]
        for y in years:
            hdr1.extend([y, "", ""])
        hdr2 = ["Pharmacy Records\nReporting Year"]
        for _ in years:
            hdr2.extend(["Pharmacy\nRecords\nTOTAL $", "Pharmacy\nRecords\nMEAN $", "Pharmacy\nRecords\nN"])
        t_data = [hdr1, hdr2]

        for rel in rels:
            row = [rel]
            for i in range(len(years)):
                row.extend([
                    f"${data_by_rel[rel]['tot'][i]:,.0f}",
                    f"${data_by_rel[rel]['mean'][i]:,.0f}",
                    f"{data_by_rel[rel]['n'][i]:,}",
                ])
            t_data.append(row)

        _first_w = 1.5 * inch
        _n_data_cols = len(years) * 3
        _data_col_w = (7.3 * inch - _first_w) / _n_data_cols
        col_widths = [_first_w] + [_data_col_w] * _n_data_cols
        t = Table(t_data, colWidths=col_widths, repeatRows=2)
        style = [
            ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
            ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
            ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
            ("FONTSIZE",      (0,0), (-1,-1), 7),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0,2), (-1,-1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0,0), (-1,-1), 2),
            ("BOTTOMPADDING", (0,0), (-1,-1), 2),
            ("LEFTPADDING",   (0,0), (-1,-1), 2),
            ("RIGHTPADDING",  (0,0), (-1,-1), 2),
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
            ("SPAN",          (0,0), (0,1)),
        ]
        col_idx = 1
        for _ in years:
            style.append(("SPAN", (col_idx,0), (col_idx+2,0)))
            col_idx += 3
        t.setStyle(TableStyle(style))
        story.append(t)
        story.append(Spacer(1, 0.2*inch))

        # Chart
        story.append(Paragraph(
            "<b>Total Amount Paid - Pharmacy - Employee / Spouse / Dependent</b>",
            ParagraphStyle("cen23", parent=self.S["Body"], alignment=TA_CENTER)
        ))
        colors = ["#2B3A5A", "#227EE4", "#5A9AD4"]
        datasets = []
        for i, rel in enumerate(rels):
            datasets.append({
                "label": f"{rel} - Total $",
                "data": data_by_rel[rel]["tot"],
                "backgroundColor": colors[i],
            })
        cfg = {
            "type": "horizontalBar",
            "data": {"labels": years, "datasets": datasets},
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "right", "anchor": "end",
                                   "font": {"weight": "bold", "size": 8}},
                    "legend": {"position": "bottom"}
                }
            }
        }
        img = self._fetch_quickchart(cfg, "chart_sec23.png")
        if img:
            story.append(RLImage(img, width=7.0*inch, height=3.5*inch))
            story.append(Spacer(1, 0.2*inch))

    # ── Section 24: Medication Compliance (MPR) ───────────────────────────────

    def _section_24_medication_mpr(self, story, charts):
        hdr = self._section_header_table("24. Medication Compliance")
        hdr._toc_entry = "24. Medication Compliance"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))

        # Intro text matching reference
        story.append(Paragraph("<b>Medication Possession Ratio</b> (MPR) is calculated as follows:", self.S["Body"]))
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph(
            "Days of Prescription = Beginning Date of Prescription – End Date of Prescription",
            ParagraphStyle("mpr_indent", parent=self.S["Body"], leftIndent=30)
        ))
        story.append(Spacer(1, 0.04*inch))
        story.append(Paragraph(
            "Medication Possession Ratio = Sum of Days Supply for Prescription ÷ Days of Prescription",
            ParagraphStyle("mpr_indent2", parent=self.S["Body"], leftIndent=30)
        ))
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph(
            "Medication Possession Ratio (MPR) measures the average compliance to prescriptions "
            "for those individuals who received a prescription and refilled it at least once.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))
        story.append(Paragraph(
            "Medication Possession Ratio for All Medication, Statin Medication, and "
            "Hypertension Medication was as follows:",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.12*inch))

        cd = charts.get("medication_mpr")
        if not cd or not cd.data:
            story.append(Paragraph("No medication compliance data available.", self.S["Body"]))
            return

        def _mpr_table(title: str, mpr_key: str, n_key: str = "N"):
            """Build a single MPR table for one medication category."""
            story.append(Paragraph(
                f"<u><b>Medication Possession Ratio for {title}</b></u>",
                ParagraphStyle("mpr_title", parent=self.S["Body"], alignment=TA_CENTER,
                               spaceAfter=4)
            ))
            story.append(Spacer(1, 0.06*inch))

            # Simplified headers
            col_hdr = [
                self._wrap_cell("Medication Possession Ratio Year", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT, text_color=rl_colors.grey),
                self._wrap_cell("Medication Possession Ratio", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("Total Members", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ]
            t_data = [col_hdr]

            total_n = 0
            mpr_vals = []
            for r in cd.data:
                yr  = str(r.get("YEAR", ""))
                mpr = float(r.get(mpr_key) or 0) if r.get(mpr_key) else None
                n   = int(r.get(n_key) or 0)
                total_n += n
                if mpr is not None:
                    mpr_vals.append(mpr)
                t_data.append([
                    self._wrap_cell(yr, font_name="Helvetica", font_size=8, alignment=TA_LEFT),
                    self._wrap_cell(f"{mpr:.1f}%" if mpr is not None else "—", font_name="Helvetica", font_size=8, alignment=TA_RIGHT),
                    self._wrap_cell(str(n), font_name="Helvetica", font_size=8, alignment=TA_RIGHT),
                ])
            avg_mpr = sum(mpr_vals) / len(mpr_vals) if mpr_vals else 0
            t_data.append([
                self._wrap_cell("Total", font_name="Helvetica-Bold", font_size=8, alignment=TA_LEFT),
                self._wrap_cell(f"{avg_mpr:.1f}%", font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT),
                self._wrap_cell(str(total_n), font_name="Helvetica-Bold", font_size=8, alignment=TA_RIGHT)
            ])

            cw = [2.2*inch, 3.0*inch, 2.1*inch]
            tbl = Table(t_data, colWidths=cw, repeatRows=1)
            tbl.setStyle(TableStyle([
                ("BACKGROUND",    (0,0), (-1,0), LTBLUE),
                ("TEXTCOLOR",     (0,0), (-1,0), rl_colors.grey),
                ("FONTNAME",      (0,0), (-1,0), "Helvetica-Bold"),
                ("FONTSIZE",      (0,0), (-1,-1), 8),
                ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
                ("ROWBACKGROUNDS",(0,1), (-1,-2), [WHITE, LTGREY]),
                ("BACKGROUND",    (0,-1), (-1,-1), LTBLUE),
                ("TOPPADDING",    (0,0), (-1,-1), 3),
                ("BOTTOMPADDING", (0,0), (-1,-1), 3),
                ("LEFTPADDING",   (0,0), (-1,-1), 2),
                ("RIGHTPADDING",  (0,0), (-1,-1), 2),
                ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
            ]))
            story.append(tbl)
            story.append(Spacer(1, 0.2*inch))

        _mpr_table("All Medication",       "ALL_MPR")
        _mpr_table("Statin Medication",    "STATIN_MPR")
        _mpr_table("Hypertension Medication", "HTN_MPR")

        self._key_finding_box(story,
            "Low Medication Possession Ratio (MPR) indicates medication non-adherence, "
            "a key driver of avoidable complications, hospitalizations, and increased "
            "long-term costs for chronic disease populations.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Deploy pharmacist-led adherence programs targeting members with MPR below 80%. "
            "Implement mail-out reminder systems and therapeutic equivalent drug overlay "
            "programs. Use value-based formulary incentives to improve medication "
            "adherence across chronic disease populations.",
            self.S["Body"]
        ))

    # ── Section 25: Brand vs. Generic Medication Usage ────────────────────────

    def _section_25_brand_generic(self, story, charts):
        hdr = self._section_header_table("25. Brand vs. Generic Medication Usage")
        hdr._toc_entry = "25. Brand vs. Generic Medication Usage"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "Pharmacy expenditures related to Brand and Generic medication were as follows:",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.1*inch))

        cd = charts.get("brand_generic")
        if not cd or not cd.data:
            story.append(Paragraph("No brand/generic data available.", self.S["Body"]))
            return

        years = self.report_years
        types = ["BRAND", "GENERIC"]

        lookup: Dict[tuple, dict] = {}
        for r in cd.data:
            lookup[(str(r.get("DRUG_TYPE", "")), str(r.get("YR", "")))] = r

        # Simplified headers
        hdr1 = [self._wrap_cell("Drug Type", font_name="Helvetica-Bold", font_size=7, alignment=TA_LEFT, text_color=rl_colors.grey)]
        for y in years:
            hdr1.extend([self._wrap_cell(y, font_name="Helvetica-Bold", font_size=7, alignment=TA_CENTER, text_color=rl_colors.grey), "", ""])
            
        hdr2 = [""]
        for _ in years:
            hdr2.extend([
                self._wrap_cell("TOTAL $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("MEAN $", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey),
                self._wrap_cell("N", font_name="Helvetica-Bold", font_size=7, alignment=TA_RIGHT, text_color=rl_colors.grey)
            ])
        t_data = [hdr1, hdr2]
        
        for dt in types:
            row = [self._wrap_cell(dt, font_name="Helvetica", font_size=7, alignment=TA_LEFT)]
            for y in years:
                r = lookup.get((dt, y), {})
                total = float(r.get("TOTAL_AMT") or 0) if r else 0
                mean  = float(r.get("MEAN_AMT") or 0) if r else 0
                n     = int(r.get("N") or 0) if r else 0
                row += [
                    self._wrap_cell(f"${total:,.0f}" if (r and total) else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(f"${mean:,.0f}" if (r and mean) else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                    self._wrap_cell(str(n) if (r and n) else "", font_name="Helvetica", font_size=7, alignment=TA_RIGHT),
                ]
            t_data.append(row)

        ncols = len(hdr1)
        type_w = 1.5 * inch
        col_w = [type_w] + [(7.3 * inch - type_w) / (ncols - 1)] * (ncols - 1)
        t = Table(t_data, colWidths=col_w, repeatRows=2)
        style = [
            ("BACKGROUND",    (0,0), (-1,1), LTBLUE),
            ("TEXTCOLOR",     (0,0), (-1,1), rl_colors.grey),
            ("FONTNAME",      (0,0), (-1,1), "Helvetica-Bold"),
            ("FONTSIZE",      (0,0), (-1,-1), 7),
            ("GRID",          (0,0), (-1,-1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0,2), (-1,-1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0,0), (-1,-1), 2),
            ("BOTTOMPADDING", (0,0), (-1,-1), 2),
            ("LEFTPADDING",   (0,0), (-1,-1), 2),
            ("RIGHTPADDING",  (0,0), (-1,-1), 2),
            ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
            ("SPAN",          (0,0), (0,1)),
        ]
        for i, _ in enumerate(years):
            c = 1 + i * 3
            style.append(("SPAN", (c,0), (c+2,0)))
        t.setStyle(TableStyle(style))
        story.append(t)
        story.append(Spacer(1, 0.2*inch))

        # Horizontal grouped bar + N line chart matching reference
        brand_total   = [float(lookup.get(("BRAND",   y), {}).get("TOTAL_AMT") or 0) for y in years]
        generic_total = [float(lookup.get(("GENERIC", y), {}).get("TOTAL_AMT") or 0) for y in years]
        brand_n       = [int(lookup.get(("BRAND",   y), {}).get("N") or 0) for y in years]
        generic_n     = [int(lookup.get(("GENERIC", y), {}).get("N") or 0) for y in years]

        cfg = {
            "type": "horizontalBar",
            "data": {
                "labels": years,
                "datasets": [
                    {"type": "horizontalBar", "label": "BRAND - Total $",   "data": brand_total,
                     "backgroundColor": "#2B3A5A", "yAxisID": "y-axis-0"},
                    {"type": "line", "label": "BRAND - N",   "data": brand_n,
                     "borderColor": "#2B3A5A", "backgroundColor": "transparent",
                     "fill": False, "pointRadius": 4, "yAxisID": "y-axis-1"},
                    {"type": "horizontalBar", "label": "GENERIC - Total $", "data": generic_total,
                     "backgroundColor": "#227EE4", "yAxisID": "y-axis-0"},
                    {"type": "line", "label": "GENERIC - N", "data": generic_n,
                     "borderColor": "#227EE4", "backgroundColor": "transparent",
                     "fill": False, "pointRadius": 4, "yAxisID": "y-axis-1"},
                ]
            },
            "options": {
                "plugins": {
                    "datalabels": {"display": True, "align": "right", "anchor": "end",
                                   "font": {"weight": "bold", "size": 9}},
                    "legend": {"position": "bottom"}
                },
                "title": {"display": True, "text": "Brand vs. Generic Medication Usage - Total Population"},
                "scales": {
                    "xAxes": [{"id": "y-axis-0", "ticks": {"beginAtZero": True}}],
                    "yAxes": [
                        {"id": "y-axis-1", "position": "right",
                         "ticks": {"beginAtZero": True}, "gridLines": {"display": False}}
                    ]
                }
            }
        }
        img = self._fetch_quickchart(cfg, "chart_sec25.png")
        if img:
            story.append(Paragraph("<b>Brand vs. Generic Medication Usage - Total Population</b>",
                                   ParagraphStyle("cen25", parent=self.S["Body"], alignment=TA_CENTER)))
            story.append(RLImage(img, width=7.0*inch, height=3.5*inch))
            story.append(Spacer(1, 0.15*inch))

        self._key_finding_box(story,
            "High brand drug utilization represents an immediate cost-reduction opportunity. "
            "Switching from brand to therapeutically equivalent generic drugs can reduce "
            "pharmacy expenditures by 30–60% per prescription.")
        story.append(Spacer(1, 0.08*inch))
        story.append(Paragraph("<b>Recommended Solution:</b>", self.S["BodyBold"]))
        story.append(Paragraph(
            "Enforce generic-first step-therapy policies. Educate prescribers and members "
            "on therapeutically equivalent generic options. Use value-based formulary "
            "incentives to drive generic utilization.",
            self.S["Body"]
        ))

    # ── Appendix 1: Disease Group Definitions ─────────────────────────────────

    def _appendix_1_disease_groups(self, story):
        hdr = self._section_header_table("Appendix 1: Disease Group Definitions")
        hdr._toc_entry = "Appendix 1: Disease Group Definitions"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The population is stratified into seven mutually exclusive disease risk groups "
            "based on the member's chronic disease burden and annual medical expenditures. "
            "The definitions below are used throughout this report.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.15*inch))

        data = [
            ["Disease Group", "Definition"],
            ["Group 1",
             "No chronic disease and less than $1,500 in medical expenditures per 12 months. "
             "This group represents the healthiest and lowest-cost members of the population."],
            ["Group 2",
             "No chronic disease and $1,500 or more in medical expenditures per 12 months. "
             "Members in this group may have acute conditions or high utilization without a "
             "chronic diagnosis."],
            ["Group 3",
             "One Chronic Disease. Members carry a single chronic condition that requires "
             "ongoing management."],
            ["Group 4",
             "Two Chronic Diseases. Members carry two concurrent chronic conditions, "
             "increasing care complexity and cost."],
            ["Group 5",
             "Three Chronic Diseases. Members carry three concurrent chronic conditions. "
             "Intensive disease management is recommended."],
            ["Group 6",
             "Four Chronic Diseases. Members carry four concurrent chronic conditions and "
             "represent a high-risk cohort requiring coordinated care."],
            ["Group 7",
             "Five or More Chronic Diseases. Members in this group carry the highest chronic "
             "disease burden and typically account for a disproportionate share of total "
             "medical expenditures."],
        ]

        # Wrap data
        wrapped_data = []
        for r_idx, row in enumerate(data):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx == 0)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = WHITE if is_header else BLACK
                    al = TA_LEFT if c_idx > 0 else TA_CENTER
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=8, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_data.append(wrapped_row)

        col_w = [1.2*inch, 6.1*inch]
        t = Table(wrapped_data, colWidths=col_w, repeatRows=1)
        t.setStyle(TableStyle([
            ("BACKGROUND",    (0, 0), (-1, 0), NAVY),
            ("TEXTCOLOR",     (0, 0), (-1, 0), WHITE),
            ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE",      (0, 0), (-1, -1), 8),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 1), (-1, -1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
            ("LEFTPADDING",   (0, 0), (-1, -1), 4),
            ("RIGHTPADDING",  (0, 0), (-1, -1), 4),
        ]))
        story.append(t)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "Members are re-stratified annually based on claims data for the reporting period. "
            "A member may move between groups year over year as their health status changes.",
            self.S["Body"]
        ))

    # ── Appendix 2: Examples of Diagnostic Categories ─────────────────────────

    def _appendix_2_diagnostic_categories(self, story):
        hdr = self._section_header_table("Appendix 2: Examples of Diagnostic Categories")
        hdr._toc_entry = "Appendix 2: Examples of Diagnostic Categories"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following table provides examples of ICD-10 diagnosis codes and conditions "
            "that are classified under each Diagnostic Category used in Section 7 of this report. "
            "Categories are based on the ICD-10-CM chapter and block structure.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.15*inch))

        data = [
            ["Diagnostic Category", "ICD-10 Chapter / Block", "Examples of Conditions Included"],
            ["Musculoskeletal",
             "Chapter XIII (M00–M99)",
             "Osteoarthritis, rheumatoid arthritis, back pain, disc disorders, "
             "fractures of spine/extremities, tendinitis, rotator cuff syndrome"],
            ["Neoplasms",
             "Chapter II (C00–D49)",
             "Malignant neoplasms of breast, prostate, colon, lung, skin; "
             "benign tumors; carcinoma in situ"],
            ["Symptoms, Signs & Abnormal\nClinical Lab Findings",
             "Chapter XVIII (R00–R99)",
             "Chest pain NOS, shortness of breath, dizziness, fatigue, "
             "abnormal blood glucose, abnormal ECG, fever of unknown origin"],
            ["Factors Influencing Health Status",
             "Chapter XXI (Z00–Z99)",
             "Preventive visits, immunizations, health screenings, "
             "routine examinations, medication management visits"],
            ["Circulatory System",
             "Chapter IX (I00–I99)",
             "Coronary artery disease, heart failure, hypertension, "
             "atrial fibrillation, stroke, peripheral vascular disease"],
            ["Digestive System",
             "Chapter XI (K00–K95)",
             "GERD, Crohn's disease, ulcerative colitis, cholelithiasis, "
             "appendicitis, hernia, diverticular disease"],
            ["Genitourinary System",
             "Chapter XIV (N00–N99)",
             "Chronic kidney disease, urinary tract infections, benign "
             "prostatic hyperplasia, kidney stones, incontinence"],
            ["Respiratory System",
             "Chapter X (J00–J99)",
             "Asthma, COPD, pneumonia, acute bronchitis, "
             "sinusitis, sleep apnea, influenza"],
            ["Pregnancy & Childbirth",
             "Chapter XV (O00–O9A)",
             "Normal delivery, high-risk pregnancy, gestational diabetes, "
             "preeclampsia, cesarean delivery"],
            ["Endocrine / Nutritional / Metabolic",
             "Chapter IV (E00–E89)",
             "Diabetes mellitus type 1 & 2, obesity, hyperlipidemia, "
             "hypothyroidism, gout, metabolic syndrome"],
            ["Mental & Behavioral Disorders",
             "Chapter V (F01–F99)",
             "Depression, anxiety disorders, bipolar disorder, "
             "substance use disorders, ADHD, PTSD"],
            ["Nervous System",
             "Chapter VI (G00–G99)",
             "Migraine, epilepsy, Parkinson's disease, multiple sclerosis, "
             "neuropathy, carpal tunnel syndrome"],
            ["Injury & Poisoning",
             "Chapter XIX (S00–T88)",
             "Fractures, lacerations, contusions, burns, "
             "drug poisoning, adverse effects of medications"],
            ["Eye & Adnexa",
             "Chapter VII (H00–H59)",
             "Cataract, glaucoma, macular degeneration, "
             "diabetic retinopathy, conjunctivitis"],
            ["Infectious & Parasitic\nDiseases",
             "Chapter I (A00–B99)",
             "Septicemia, HIV disease, viral hepatitis, "
             "pneumonia due to organism, Lyme disease"],
        ]

        # Wrap data
        wrapped_data = []
        for r_idx, row in enumerate(data):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx == 0)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = WHITE if is_header else BLACK
                    al = TA_LEFT
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_data.append(wrapped_row)

        col_w = [1.8*inch, 1.7*inch, 3.8*inch]
        t = Table(wrapped_data, colWidths=col_w, repeatRows=1)
        t.setStyle(TableStyle([
            ("BACKGROUND",    (0, 0), (-1, 0), NAVY),
            ("TEXTCOLOR",     (0, 0), (-1, 0), WHITE),
            ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE",      (0, 0), (-1, -1), 7),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 1), (-1, -1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
            ("LEFTPADDING",   (0, 0), (-1, -1), 4),
            ("RIGHTPADDING",  (0, 0), (-1, -1), 4),
        ]))
        story.append(t)

    # ── Appendix 3: Examples of Complications of Diabetes ─────────────────────

    def _appendix_3_diabetes_complications(self, story):
        hdr = self._section_header_table("Appendix 3: Examples of Complications of Diabetes")
        hdr._toc_entry = "Appendix 3: Examples of Complications of Diabetes"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following table identifies the diabetes-specific complication categories "
            "used in Section 9 of this report, along with representative ICD-10 diagnosis "
            "codes and clinical descriptions.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.15*inch))

        data = [
            ["Complication Category", "ICD-10 Codes (Examples)", "Clinical Description"],
            ["Cardiovascular",
             "I20–I25, I50, I63–I64,\nE11.40–E11.49",
             "Coronary artery disease, heart failure, stroke, and other "
             "cardiovascular events occurring in the context of diabetes. "
             "The most common and costly complication category."],
            ["Peripheral Vascular\nDisease",
             "E11.51–E11.52, I70–I79,\nE10.51–E10.52",
             "Peripheral arterial occlusive disease, intermittent claudication, "
             "and other vascular compromise affecting the limbs. "
             "Associated with elevated risk of lower-extremity amputation."],
            ["Neuropathy",
             "E11.40, E11.41, E11.42,\nG63.2, G90.09",
             "Diabetic peripheral neuropathy, autonomic neuropathy, and "
             "painful diabetic neuropathy. Contributes to foot complications "
             "and reduced quality of life."],
            ["Nephropathy /\nChronic Kidney Disease",
             "E11.21, E11.22, N18.1–N18.6,\nN08",
             "Diabetic kidney disease ranging from microalbuminuria to "
             "end-stage renal disease (ESRD) requiring dialysis or transplant. "
             "Significant cost driver."],
            ["Retinopathy /\nEye Complications",
             "E11.311–E11.359,\nH28, H36.0",
             "Diabetic background retinopathy, proliferative retinopathy, "
             "diabetic macular edema, and other eye complications that "
             "can lead to vision loss."],
            ["Foot Complications",
             "E11.610–E11.649,\nL97.1–L97.9, M86",
             "Diabetic foot ulcers, Charcot foot, foot infections, and "
             "osteomyelitis. Often preventable with regular foot exams and "
             "podiatric care."],
            ["Hypoglycemia",
             "E11.641–E11.649,\nE16.0–E16.2",
             "Low blood sugar events including severe hypoglycemia requiring "
             "assistance. Associated with medication non-compliance and "
             "inadequate monitoring."],
            ["Hyperglycemic Crisis",
             "E11.00–E11.01,\nE13.10–E13.11",
             "Diabetic ketoacidosis (DKA) and hyperosmolar hyperglycemic "
             "state (HHS). Typically represent uncontrolled or newly "
             "diagnosed diabetes and require acute hospitalization."],
            ["Infection / Sepsis",
             "A41, B37, L03, L08,\nE11.618–E11.638",
             "Diabetes-related increased susceptibility to infections "
             "including cellulitis, candidiasis, urinary tract infections, "
             "and sepsis. Diabetes impairs immune response."],
            ["End-Stage Renal\nDisease / Dialysis",
             "N18.6, Z99.2, Z49.01,\nZ49.02",
             "Patients with ESRD requiring chronic hemodialysis or "
             "peritoneal dialysis. Represents the most severe stage of "
             "diabetic nephropathy."],
        ]

        # Wrap data
        wrapped_data = []
        for r_idx, row in enumerate(data):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx == 0)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = WHITE if is_header else BLACK
                    al = TA_LEFT
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_data.append(wrapped_row)

        col_w = [1.7*inch, 1.8*inch, 3.8*inch]
        t = Table(wrapped_data, colWidths=col_w, repeatRows=1)
        t.setStyle(TableStyle([
            ("BACKGROUND",    (0, 0), (-1, 0), NAVY),
            ("TEXTCOLOR",     (0, 0), (-1, 0), WHITE),
            ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE",      (0, 0), (-1, -1), 7),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 1), (-1, -1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
            ("LEFTPADDING",   (0, 0), (-1, -1), 4),
            ("RIGHTPADDING",  (0, 0), (-1, -1), 4),
        ]))
        story.append(t)
        story.append(Spacer(1, 0.15*inch))
        story.append(Paragraph(
            "Source: ICD-10-CM Official Guidelines for Coding and Reporting; American Diabetes "
            "Association Standards of Medical Care in Diabetes; HEDIS Technical Specifications.",
            ParagraphStyle("small_app3", parent=self.S["Body"], fontSize=7,
                           fontName="Helvetica-Oblique")
        ))

    # ── Appendix 4: Preventive Screening Eligibility Criteria ─────────────────

    def _appendix_4_screening_eligibility(self, story):
        hdr = self._section_header_table("Appendix 4: Preventive Screening Eligibility Criteria")
        hdr._toc_entry = "Appendix 4: Preventive Screening Eligibility Criteria"
        story.append(hdr)
        story.append(Spacer(1, 0.2*inch))
        story.append(Paragraph(
            "The following table defines the eligibility criteria used to identify members "
            "who are due for preventive cancer screenings in Section 14 (Preventive Screening "
            "Compliance) and Section 15 (Value of Preventive Screenings). Criteria are "
            "aligned with HEDIS technical specifications and USPSTF guidelines.",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.15*inch))

        # ── Main eligibility table ──────────────────────────────────────────
        elig_data = [
            ["Screening", "Eligible Population", "Age / Gender",
             "Interval", "HEDIS"],
            ["Breast Cancer",
             "Female members continuously enrolled for at least 12 months",
             "Women 50–74",
             "At least one mammogram in the past 27 months",
             "BCS"],
            ["Cervical Cancer",
             "Female members continuously enrolled for at least 12 months",
             "Women 21–64",
             "Pap 3 years; Pap + HPV 5 years",
             "CCS"],
            ["Colorectal Cancer",
             "Members continuously enrolled for at least 12 months",
             "Adults 50–75",
             "FOBT/FIT 1yr; Sig 5yr; Col 10yr; CT 5yr",
             "COL"],
        ]

        # Wrap data
        wrapped_elig = []
        for r_idx, row in enumerate(elig_data):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx == 0)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = WHITE if is_header else BLACK
                    al = TA_LEFT
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_elig.append(wrapped_row)

        col_w = [1.35*inch, 1.65*inch, 1.15*inch, 1.85*inch, 1.3*inch]
        t_elig = Table(wrapped_elig, colWidths=col_w, repeatRows=1)
        t_elig.setStyle(TableStyle([
            ("BACKGROUND",    (0, 0), (-1, 0), NAVY),
            ("TEXTCOLOR",     (0, 0), (-1, 0), WHITE),
            ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTNAME",      (0, 1), (-1, -1), "Helvetica"),
            ("FONTSIZE",      (0, 0), (-1, -1), 7),
            ("ALIGN",         (0, 0), (-1, -1), "LEFT"),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 1), (-1, -1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0, 0), (-1, -1), 5),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
            ("LEFTPADDING",   (0, 0), (-1, -1), 5),
        ]))
        story.append(t_elig)
        story.append(Spacer(1, 0.2*inch))

        # ── CPT / HCPCS codes used for screening identification ─────────────
        story.append(Paragraph("Screening Identification Codes (CPT / HCPCS)", self.S["SubHead"]))
        story.append(Spacer(1, 0.05*inch))
        story.append(Paragraph(
            "The following CPT and HCPCS codes are used to identify completed screenings "
            "in the claims data:",
            self.S["Body"]
        ))
        story.append(Spacer(1, 0.08*inch))

        cpt_data = [
            ["Screening Type", "CPT / HCPCS Codes"],
            ["Breast Cancer (Mammography)",
             "77065, 77066, 77067, G0202, G0204, G0206"],
            ["Cervical Cancer (Pap Smear)",
             "88141–88143, 88147, 88148, 88150, 88152–88154, 88164–88167, "
             "88174, 88175, G0101, G0123, G0124, G0141, G0143–G0145, "
             "G0147, G0148, P3000, P3001, Q0091"],
            ["Cervical Cancer (HPV Co-test)",
             "87620, 87621, 87622, 87623, 87624, 87625"],
            ["Colorectal — FOBT / FIT",
             "82270, 82274, G0107, G0328"],
            ["Colorectal — Flexible Sigmoidoscopy",
             "45330–45335, 45337–45342, 45345, G0104"],
            ["Colorectal — Colonoscopy",
             "44388–44394, 44397, 44401, 44404–44408, "
             "45355, 45378–45393, 45398, G0105, G0121"],
            ["Colorectal — CT Colonography",
             "74261, 74262, 74263"],
        ]

        # Wrap data
        wrapped_cpt = []
        for r_idx, row in enumerate(cpt_data):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx == 0)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = WHITE if is_header else BLACK
                    al = TA_LEFT
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_cpt.append(wrapped_row)

        col_w2 = [1.9*inch, 5.4*inch]
        t_cpt = Table(wrapped_cpt, colWidths=col_w2, repeatRows=1)
        t_cpt.setStyle(TableStyle([
            ("BACKGROUND",    (0, 0), (-1, 0), NAVY),
            ("TEXTCOLOR",     (0, 0), (-1, 0), WHITE),
            ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE",      (0, 0), (-1, -1), 7),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 1), (-1, -1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
            ("LEFTPADDING",   (0, 0), (-1, -1), 4),
            ("RIGHTPADDING",  (0, 0), (-1, -1), 4),
        ]))
        story.append(t_cpt)
        story.append(Spacer(1, 0.2*inch))

        # ── Exclusion criteria ──────────────────────────────────────────────
        story.append(Paragraph("Exclusion Criteria", self.S["SubHead"]))
        story.append(Spacer(1, 0.05*inch))

        excl_data = [
            ["Screening Type", "Exclusion Criteria"],
            ["Breast Cancer",
             "Bilateral mastectomy; history of breast cancer treatment; "
             "hospice enrollment during the measurement year."],
            ["Cervical Cancer",
             "Total hysterectomy with removal of cervix; history of cervical cancer; "
             "hospice enrollment during the measurement year."],
            ["Colorectal Cancer",
             "Total colectomy; colorectal cancer diagnosis; "
             "hospice enrollment during the measurement year."],
        ]

        # Wrap data
        wrapped_excl = []
        for r_idx, row in enumerate(excl_data):
            wrapped_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str) and cell:
                    is_header = (r_idx == 0)
                    fn = "Helvetica-Bold" if is_header else "Helvetica"
                    tc = WHITE if is_header else BLACK
                    al = TA_LEFT
                    wrapped_row.append(self._wrap_cell(cell, font_name=fn, font_size=7, alignment=al, text_color=tc))
                else:
                    wrapped_row.append(cell)
            wrapped_excl.append(wrapped_row)

        t_excl = Table(wrapped_excl, colWidths=[1.5*inch, 5.8*inch], repeatRows=1)
        t_excl.setStyle(TableStyle([
            ("BACKGROUND",    (0, 0), (-1, 0), NAVY),
            ("TEXTCOLOR",     (0, 0), (-1, 0), WHITE),
            ("FONTNAME",      (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE",      (0, 0), (-1, -1), 7),
            ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
            ("GRID",          (0, 0), (-1, -1), 0.4, rl_colors.grey),
            ("ROWBACKGROUNDS",(0, 1), (-1, -1), [WHITE, LTGREY]),
            ("TOPPADDING",    (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
            ("LEFTPADDING",   (0, 0), (-1, -1), 4),
            ("RIGHTPADDING",  (0, 0), (-1, -1), 4),
        ]))
        story.append(t_excl)
        story.append(Spacer(1, 0.15*inch))
        story.append(Paragraph(
            "Sources: NCQA HEDIS 2024 Technical Specifications; U.S. Preventive Services Task "
            "Force (USPSTF) Recommendations; American Cancer Society Screening Guidelines.",
            ParagraphStyle("small_app4", parent=self.S["Body"], fontSize=7,
                           fontName="Helvetica-Oblique")
        ))
