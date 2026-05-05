import sys
from pathlib import Path
from datetime import datetime

from reportlab.lib.pagesizes import letter
from reportlab.lib.units import inch
from reportlab.lib import colors
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, Image as RLImage
from reportlab.platypus.tableofcontents import TableOfContents
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_LEFT

def build_pdf():
    pdf_path = "test_scratch.pdf"
    
    styles = getSampleStyleSheet()
    styles.add(ParagraphStyle(name='CenterTitle', parent=styles['Heading1'], alignment=TA_CENTER, spaceAfter=20, fontSize=18))
    styles.add(ParagraphStyle(name='CenterSubtitle', parent=styles['Heading2'], alignment=TA_CENTER, spaceAfter=10, fontSize=12))
    styles.add(ParagraphStyle(name='BoldBody', parent=styles['Normal'], spaceAfter=10, fontSize=10, fontName='Helvetica-Bold'))
    styles.add(ParagraphStyle(name='TOCHeading', parent=styles['Heading2'], spaceAfter=10))

    Story = []
    
    # 1. Cover Page
    Story.append(Spacer(1, 2*inch))
    Story.append(Paragraph("AllHealth CHOICE", styles['CenterTitle'])) # Placeholder for Logo
    Story.append(Spacer(1, 0.5*inch))
    Story.append(Paragraph("Population Health Management Report", styles['CenterTitle']))
    Story.append(Spacer(1, 1*inch))
    Story.append(Paragraph("Time Period:", styles['CenterSubtitle']))
    Story.append(Paragraph("Medical Data: 2022-01-01 To 2024-04-01", styles['CenterSubtitle']))
    Story.append(Paragraph("Pharmacy Data: 2022-07-01 To 2024-03-09", styles['CenterSubtitle']))
    Story.append(Spacer(1, 0.5*inch))
    Story.append(Paragraph("Prepared for", styles['CenterSubtitle']))
    Story.append(Paragraph("Long County", styles['CenterSubtitle']))
    Story.append(PageBreak())
    
    # 2. Table of Contents
    Story.append(Paragraph("Table of contents", styles['Heading1']))
    toc = TableOfContents()
    toc.levelStyles = [
        ParagraphStyle(fontName='Helvetica', fontSize=10, name='TOCLevel1', leftIndent=20, firstLineIndent=-20, spaceBefore=0, leading=16),
    ]
    Story.append(toc)
    Story.append(PageBreak())
    
    # 3. Executive Summary
    Story.append(Paragraph("1. Executive Summary", styles['Heading1']))
    # For TOC to pick up heading1
    Story[-1].tocLevel = 0
    
    Story.append(Paragraph("Introduction:", styles['BoldBody']))
    
    intro_text = (
        "The following report is the result of an analysis of archival medical and pharmacy utilization data. "
        "The intent of this analysis is to yield a better understanding of the epidemiology currently influencing this population "
        "and to suggest population health management opportunities that can address the specific risk impacting this population. "
        "In order to accomplish this task, archival data was processed through proprietary algorithms in order to properly risk-stratify the population. "
        "The risk of a population has a direct relationship to current and future spending patterns. Variables that are the building blocks of risk "
        "and/or disease include, but are not limited to:"
    )
    Story.append(Paragraph(intro_text, styles['Normal']))
    
    variables_text = (
        "Age, Gender, Lifestyle, Genetics, Chronic Illness, Co-Morbidities, Multi-Morbidities, Medication Compliance/Non-Compliance, "
        "Compliance/Non-Compliance to Evidence-Based Guidelines, Gaps in Care, etc."
    )
    Story.append(Paragraph(f"• {variables_text}", styles['Normal']))
    Story.append(Spacer(1, 10))
    
    Story.append(Paragraph(
        "The majority of the aforementioned variables were utilized to investigate risk stratifications within the population. "
        "The overall health of a population is determined by multiple factors; however, an individual's lifestyle is a powerful predictor of "
        "leading causes of morbidity and disability.", styles['Normal']))
    Story.append(Spacer(1, 10))
    
    Story.append(Paragraph("This analysis explored multiple areas of interest within the data, including the following research questions:", styles['Normal']))
    
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
        Story.append(Paragraph(q, styles['Normal']))
        Story.append(Spacer(1, 2))
    
    Story.append(Spacer(1, 15))
    Story.append(Paragraph("Key Findings and Solutions for Consideration:", styles['BoldBody']))
    Story.append(Paragraph("The following key findings resulted from the analysis of archival health care data.", styles['Normal']))
    Story.append(Spacer(1, 10))
    
    Story.append(Paragraph("Key Finding 1: Chronic Disease", styles['BoldBody']))
    Story.append(Paragraph("Key Finding: Patterns of risk generally occur within any given population. In order to better understand these patterns, the population was risk stratified into five distinct groups:", styles['Normal']))
    Story.append(Spacer(1, 10))
    
    # 4. Table 1: Disease Group Risk Stratification
    table_data = [
        ["Group", "Description"],
        ["Disease Group-1", "No chronic disease and less than $1,500 medical expenditures per 12 months"],
        ["Disease Group-2", "No chronic disease and $1,500 or more medical expenditures per 12 months"],
        ["Disease Group-3", "One Chronic Disease"],
        ["Disease Group-4", "Two Chronic Disease"],
        ["Disease Group-5", "Three Chronic Disease"],
        ["Disease Group-6", "Four Chronic Disease"],
        ["Disease Group-7", "Five or More Chronic Disease"]
    ]
    
    t = Table(table_data, colWidths=[1.5*inch, 5*inch])
    t.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#B4C6E7")),
        ('TEXTCOLOR', (0,0), (-1,0), colors.black),
        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
        ('FONTNAME', (0,0), (-1,0), 'Helvetica-Bold'),
        ('FONTSIZE', (0,0), (-1,-1), 9),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('GRID', (0,0), (-1,-1), 0.5, colors.black),
    ]))
    Story.append(t)
    Story.append(Spacer(1, 20))
    
    # 5. Table 2: Risk Group Summary
    Story.append(Paragraph("the number of individuals incorporated into each analysis.", styles['Normal']))
    Story.append(Spacer(1, 10))
    table2_data = [
        ["Risk Group\nSummary\nRisk Group", "2022", "", "", "2023", "", "", "2024", "", ""],
        ["", "Risk Group\nSummary\nTotal $", "Risk Group\nSummary\nMean $", "Risk Group\nSummary\nN", "Risk Group\nSummary\nTotal $", "Risk Group\nSummary\nMean $", "Risk Group\nSummary\nN", "Risk Group\nSummary\nTotal $", "Risk Group\nSummary\nMean $", "Risk Group\nSummary\nN"],
        ["GROUP-1", "$19,959", "$407", "49", "$30,245", "$432", "70", "$15,686", "$285", "55"],
        ["GROUP-2", "$87,538", "$5,471", "16", "$180,545", "$7,523", "24", "$19,126", "$2,732", "7"],
    ]
    t2 = Table(table2_data)
    t2.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,1), colors.HexColor("#D9E1F2")),
        ('SPAN', (1,0), (3,0)),
        ('SPAN', (4,0), (6,0)),
        ('SPAN', (7,0), (9,0)),
        ('SPAN', (0,0), (0,1)),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('FONTNAME', (0,0), (-1,1), 'Helvetica-Bold'),
        ('FONTSIZE', (0,0), (-1,-1), 8),
        ('GRID', (0,0), (-1,-1), 0.5, colors.black),
    ]))
    Story.append(t2)
    
    # Add multi-pass logic for TOC
    class MyDocTemplate(SimpleDocTemplate):
        def afterFlowable(self, flowable):
            if flowable.__class__.__name__ == 'Paragraph':
                text = flowable.getPlainText()
                style = flowable.style.name
                if style == 'Heading1':
                    self.notify('TOCEntry', (0, text, self.page))
    
    doc = MyDocTemplate(pdf_path, pagesize=letter, rightMargin=72, leftMargin=72, topMargin=72, bottomMargin=18)
    doc.multiBuild(Story)
    print("Generated PDF!")

if __name__ == "__main__":
    build_pdf()
