# Detailed Summary of PHM Report Generation

The PHM (Population Health Management) report generation is a multi-step background process that extracts data from Snowflake, generates visual charts via a third-party API, and compiles a comprehensive PDF report using Python. 

The entire process runs as a daemonized Python worker (`worker.py`) that polls a central MySQL database for pending report jobs. It bypasses previous dependencies on Looker Studio APIs in favor of direct Snowflake queries and local PDF generation.

Below is a detailed, step-by-step breakdown of how the reports are generated, structured perfectly for an LLM to comprehend.

---

## 1. Orchestration and Job Polling (`worker.py`)
The main entry point is a persistent Python worker (`PHMWorker`) that runs in a continuous loop (`run_forever` daemon mode).

1. **Database Polling:** The worker queries the primary MySQL database (`report` table) to find pending report jobs. It looks for records where:
   - `is_active = 1`
   - `frequency` is 1 or 2 (indicating eligible report types)
   - `looks_generated` is in `(0, 1, 2)` (a legacy state column repurposed as status: pending/processing)
   - `file_path` is NULL or empty
2. **Claiming the Job:** When a pending report is found, the worker locks it using a database transaction and updates the `looks_generated` status to `1` (In Progress).
3. **Configuration Parsing:** The worker extracts parameters for the report such as the Snowflake `schema_name`, `phm_folder_id`, target `years`, and `report_id`.

---

## 2. Data Extraction and Analysis (`phm_data_analyzer.py`)
Once a report is claimed, the worker delegates data aggregation to the `PHMDataAnalyzer`.

1. **Snowflake Connection:** A direct connection to Snowflake is established using `SnowflakeConnector` using credentials loaded from the environment (`.env`).
2. **Schema & Year Detection:** The analyzer verifies the schema and automatically detects the available years in the medical data (e.g., `STG_TAB_MEDICAL_DATA`) if the report configuration doesn't enforce specific years.
3. **Execution of Analyses:** The analyzer runs a strict sequence of 30+ predefined SQL queries corresponding to various sections of the report. The sections include:
   - **Medical Expenditures:** Grouped by Year, Quarter, Relationship (Employee vs Dependent), Gender, and Diagnostic Categories.
   - **Risk & Chronic Diseases:** Analyzes `VW_RISK_GROUP_MIGRATION` to determine high-cost chronic diseases and risk stratifications.
   - **Diabetes Specifics:** Stratifies diabetes risk by co-morbidities, complications, and Evidence-Based Medicine (EBM) non-compliance (checking for specific medications like ACE/ARB and procedure CPT codes for eye/foot exams).
   - **Utilization:** Inpatient vs Outpatient vs ER utilization, avoidable ER visits, and PCP vs Specialty split.
   - **Demographics:** Age/Gender pyramids.
   - **Pharmacy:** Expenditures by Year, Quarter, Relationship, Brand vs. Generic, and Medication Possession Ratio (MPR compliance).
   - **Preventive Screenings:** Breast, Cervical, and Colon cancer screening compliance rates calculated from `VW_PREVENTIVE_SCREENING`.
4. **Data Encapsulation:** The result of each analysis is formatted into a custom dataclass called `ChartData`. This object contains the raw SQL rows, X/Y axes labels, title, values, and the intended `chart_type` (e.g., `bar`, `horizontalBar`, `pie`, `table`, `pyramid`).

---

## 3. Chart Image Generation (`chart_generator.py` -> `ChartGenerator`)
After the data is analyzed, the process converts the `ChartData` objects into actual image files.

1. **Filtering:** If a `ChartData` object defines its type as `table` or `pyramid`, it skips image generation (these are rendered as raw ReportLab elements later).
2. **QuickChart API Integration:** For standard charts (Bar, Line, Pie), the generator builds a Chart.js compatible JSON payload.
   - It applies a specific corporate color palette (e.g., `NAVY`, `TEAL`, `LTBLUE`).
   - It URL-encodes the JSON and sends it to `https://quickchart.io/chart` (a service that renders Chart.js configs into static images).
3. **Downloading Assets:** The generated PNG images are downloaded with a timeout/retry mechanism and saved to a local `charts/` subdirectory, named systematically (e.g., `chart_{report_id}_{section_key}.png`).

---

## 4. PDF Compilation (`chart_generator.py` -> `PDFReportGenerator`)
The final and most complex phase is compiling the PDF using the `reportlab` library. The `PDFReportGenerator` constructs a document that strictly mimics a specific corporate branding structure ("AllHealth CHOICE").

1. **Styling Initialization:** Custom paragraph styles, fonts (Helvetica), tables, and colors are defined.
2. **Document Flow (ReportLab Platyus):** A `SimpleDocTemplate` is used to build the PDF flow.
   - **Cover Page:** Generated with the client name, date ranges, and branding elements.
   - **Table of Contents:** Automatically generated using ReportLab's TOC mechanism tracking section headers.
   - **Executive Summary:** A dynamic section that includes hardcoded textual findings combined with dynamically generated data tables. It performs custom "pivoting" of the `ChartData` (especially for Disease Group Risk Stratification, Chronic Diseases, and Preventive Screenings) to build complex ReportLab `Table` objects with precise column widths, background colors, and formatting.
   - **Report Sections (1-25):** The generator iterates through predefined sections. For each section, it injects:
     - The section title.
     - Hardcoded "Key Findings" and "Recommended Solutions" text.
     - The downloaded QuickChart PNG image (if applicable) using ReportLab's `Image` component.
     - Fallback tables if an image wasn't generated.
   - **Appendices:** Static reference tables (e.g., definitions of Disease Groups and Diagnostic Categories) are appended to the end of the document.
3. **Finalization:** The `doc.multiBuild(story)` function is called to render the PDF file to the local disk.

---

## 5. Delivery and Cleanup (`worker.py`)
1. **S3 Upload:** The worker attempts to upload the compiled PDF to an AWS S3 bucket (using `boto3`). It mimics a specific folder structure (e.g., `Generated_PHM/{report_name}_{date}.pdf`).
2. **Database Update:** Finally, the worker updates the MySQL `report` table:
   - Sets `looks_generated = 6` (indicating success/completion).
   - Saves the S3 URL (or local file path) to the `file_path` column so the frontend application can access it.
3. **Failure Handling:** If any step fails (Snowflake connection error, QuickChart timeout, PDF compilation crash), the worker logs the exception and updates the database to `looks_generated = 7` (Failed).

## Summary Architecture
`MySQL (Job Queue)` -> `Python Worker` -> `Snowflake (Data Queries)` -> `QuickChart.io (Images)` -> `ReportLab (PDF)` -> `AWS S3 (Storage)` -> `MySQL (Status Update)`
