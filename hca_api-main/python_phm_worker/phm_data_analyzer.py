"""PHM Data Analyzer - Generates all chart data matching the Long County PHM Report structure."""
from __future__ import annotations
import logging
from dataclasses import dataclass, field
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List, Optional
from snowflake_connector import SnowflakeConnector


@dataclass
class PHMReportConfig:
    schema: str
    years: List[int]
    reporting_year: str = "Service"
    medical_date_col: str = "DIAGNOSIS_DATE"
    pharmacy_date_col: str = "DATE_FILLED"
    medical_amount_col: str = "TOTAL_EMPLOYER_PAID_AMT"
    pharmacy_amount_col: str = "TOTAL_EMPLOYER_PAID_AMT"
    member_col: str = "MEMBER_ID"
    medical_gender_col: str = "PATIENT_GENDER"
    pharmacy_gender_col: str = "EMPLOYEE_GENDER"
    medical_rel_col: str = "RELATIONSHIP_TO_EMPLOYEE"
    pharmacy_rel_col: str = "RELATIONSHIP_TO_EMPLOYEE"
    brand_generic_col: str = "DRUG_INDICATOR_GENERIC_OR_BRAND"
    diag_category_col: str = "DISEASE_GROUP"
    pos_col: str = "PLACE_OF_SERVICE"


@dataclass
class ChartData:
    title: str
    chart_type: str
    data: List[Dict[str, Any]]
    x_axis: str
    y_axis: str
    labels: List[str] = field(default_factory=list)
    values: List[float] = field(default_factory=list)


class PHMDataAnalyzer:
    def __init__(self, snowflake: SnowflakeConnector, config: PHMReportConfig):
        self.snowflake = snowflake
        self.config = config
        self.logger = logging.getLogger("phm_analyzer")
        self._chart_cache: Dict[str, ChartData] = {}

    def _detect_available_years(self) -> List[int]:
        sql = f"SELECT DISTINCT YEAR({self.config.medical_date_col}) AS yr FROM {self.config.schema}.STG_TAB_MEDICAL_DATA WHERE {self.config.medical_date_col} IS NOT NULL ORDER BY yr DESC"
        rows = self.snowflake.query(sql)
        return [int(r["YR"]) for r in rows if r.get("YR")] if rows else []

    def _get_years_clause(self) -> str:
        return "(" + ",".join(str(y) for y in self.config.years) + ")"

    def _get_date_col(self, table: str) -> str:
        if "PHARMACY" in table.upper():
            return self.config.pharmacy_date_col
        return self.config.medical_date_col

    def analyze_all(self) -> Dict[str, ChartData]:
        self.logger.info(f"Starting PHM analysis for schema: {self.config.schema}")
        try:
            if not self.snowflake.test_connection():
                self.logger.error("Snowflake connection failed.")
                return {}
        except Exception as e:
            self.logger.error(f"Snowflake error: {e}")
            return {}

        # Auto-detect years
        yc = self._get_years_clause()
        dc = self.config.medical_date_col
        probe = self.snowflake.query(f"SELECT COUNT(*) AS cnt FROM {self.config.schema}.STG_TAB_MEDICAL_DATA WHERE YEAR({dc}) IN {yc}")
        if not probe or int(probe[0].get("CNT", 0)) == 0:
            available = self._detect_available_years()
            if not available:
                self.logger.error("No medical data found.")
                return {}
            self.config.years = available
            self.logger.info(f"Using auto-detected years: {available}")

        # Ordered sections matching Long County report
        analyses = [
            ("demographics_rel_age", self.analyze_demographics_rel_age),
            ("demographics_age_group", self.analyze_demographics_age_group),
            ("demographics_gender_pct", self.analyze_demographics_gender_pct),
            ("med_by_year",        self.analyze_medical_by_year),
            ("med_by_quarter",     self.analyze_medical_by_quarter),
            ("emp_spouse_dep",     self.analyze_employee_breakdown),
            ("gender_exp",         self.analyze_gender_expenditure),
            ("diag_category",      self.analyze_diagnostic_categories),
            ("chronic_diseases",   self.analyze_chronic_diseases),
            ("diabetes_strat",     self.analyze_diabetes_stratification),
            ("diabetes_complications", self.analyze_diabetes_complications),
            ("diabetes_comorbids", self.analyze_diabetes_comorbids),
            ("diabetes_ebm",       self.analyze_diabetes_ebm_noncompliance),
            ("risk_groups",        self.analyze_risk_groups),
            ("lifestyle",          self.analyze_lifestyle_modifiable),
            ("health_disparities", self.analyze_health_disparities),
            ("breast_screening",   self.analyze_breast_cancer_screening),
            ("cervical_screening", self.analyze_cervical_cancer_screening),
            ("colon_screening",    self.analyze_colon_cancer_screening),
            ("screening_value",    self.analyze_preventive_screening_value),
            ("msk_work",           self.analyze_musculoskeletal_work),
            ("catastrophic",       self.analyze_catastrophic_claims),
            ("inpatient_er",       self.analyze_inpatient_outpatient_er),
            ("avoidable_er",       self.analyze_avoidable_er),
            ("pcp_specialty",      self.analyze_pcp_vs_specialty),
            ("pharm_by_year",      self.analyze_pharmacy_by_year),
            ("pharm_by_quarter",   self.analyze_pharmacy_by_quarter),
            ("pharm_relationship", self.analyze_pharmacy_by_relationship),
            ("brand_generic",      self.analyze_brand_generic),
            ("medication_mpr",     self.analyze_medication_compliance),
            ("demographics",       self.analyze_demographics_pyramid),
            ("hospital_util",      self.analyze_hospital_utilization),
            ("provider_type",      self.analyze_provider_type),
        ]

        for key, fn in analyses:
            try:
                cd = fn()
                if cd:
                    self._chart_cache[key] = cd
                    self.logger.info(f"Generated: {key}")
            except Exception as e:
                self.logger.error(f"Failed {key}: {e}")

        return dict(self._chart_cache)

    # ── Medical sections ──────────────────────────────────────────────────────

    def analyze_medical_by_year(self) -> Optional[ChartData]:
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
            GROUP BY 1 ORDER BY 1
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(
            title="Overall Medical Expenditure by Year",
            chart_type="horizontalBar",
            data=rows, x_axis="Year", y_axis="Total Amount ($)",
            labels=[str(r["YR"]) for r in rows],
            values=[float(r.get("TOTAL_AMT") or 0) for r in rows],
        )


    def analyze_demographics_rel_age(self) -> Optional[ChartData]:
        dc = "DIAGNOSIS_DATE"
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   RELATIONSHIP_TO_EMPLOYEE AS relationship,
                   AVG(PATIENT_AGE) AS mean_age,
                   COUNT(DISTINCT UNIQUE_ID) AS n
            FROM {self.config.schema}.LKR_TAB_MEDICAL
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND RELATIONSHIP_TO_EMPLOYEE IN ('EMPLOYEE', 'SPOUSE', 'DEPENDENT')
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(title="Demographics Rel Age", chart_type="bar", data=rows, x_axis="Year", y_axis="Mean Age", labels=[], values=[])

    def analyze_demographics_age_group(self) -> Optional[ChartData]:
        dc = "DIAGNOSIS_DATE"
        sql = f"""
            WITH bins AS (
                SELECT UNIQUE_ID, PATIENT_AGE,
                       CASE 
                           WHEN PATIENT_AGE < 20 THEN 'Below 20'
                           WHEN PATIENT_AGE BETWEEN 20 AND 29 THEN '20 to 29'
                           WHEN PATIENT_AGE BETWEEN 30 AND 39 THEN '30 to 39'
                           WHEN PATIENT_AGE BETWEEN 40 AND 49 THEN '40 to 49'
                           WHEN PATIENT_AGE BETWEEN 50 AND 59 THEN '50 to 59'
                           ELSE '60 or Above'
                       END AS age_group
                FROM {self.config.schema}.LKR_TAB_MEDICAL
                WHERE YEAR({dc}) IN {self._get_years_clause()}
            )
            SELECT age_group, AVG(PATIENT_AGE) AS mean_age, COUNT(DISTINCT UNIQUE_ID) AS n
            FROM bins GROUP BY 1 ORDER BY 
                CASE age_group WHEN 'Below 20' THEN 1 WHEN '20 to 29' THEN 2 WHEN '30 to 39' THEN 3
                WHEN '40 to 49' THEN 4 WHEN '50 to 59' THEN 5 ELSE 6 END DESC
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(title="Demographics Age Group", chart_type="horizontalBar", data=rows, x_axis="Age Group", y_axis="N", labels=[], values=[])

    def analyze_demographics_gender_pct(self) -> Optional[ChartData]:
        dc = "DIAGNOSIS_DATE"
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   {self.config.medical_gender_col} AS gender,
                   COUNT(DISTINCT UNIQUE_ID) AS n
            FROM {self.config.schema}.LKR_TAB_MEDICAL
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND {self.config.medical_gender_col} IN ('M', 'F')
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(title="Demographics Gender", chart_type="horizontalBar", data=rows, x_axis="Year", y_axis="N", labels=[], values=[])

    def analyze_medical_by_quarter(self) -> Optional[ChartData]:
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr, QUARTER({dc}) AS qtr,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(
            title="Overall Medical Expenditure by Quarter",
            chart_type="bar",
            data=rows, x_axis="Quarter", y_axis="Total Amount ($)",
            labels=[f"{r['YR']}-Q{r['QTR']}" for r in rows],
            values=[float(r.get("TOTAL_AMT") or 0) for r in rows],
        )

    def analyze_employee_breakdown(self) -> Optional[ChartData]:
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   {self.config.medical_rel_col} AS relationship,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND {self.config.medical_rel_col} IS NOT NULL
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, float] = {}
        for r in rows:
            rel = r.get("RELATIONSHIP", "Unknown")
            totals[rel] = totals.get(rel, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Medical Expenditure by Relationship (Employee/Spouse/Dependent)",
            chart_type="bar",
            data=rows, x_axis="Relationship", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    def analyze_gender_expenditure(self) -> Optional[ChartData]:
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   {self.config.medical_gender_col} AS gender,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND {self.config.medical_gender_col} IS NOT NULL
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, float] = {}
        for r in rows:
            g = r.get("GENDER", "Unknown")
            totals[g] = totals.get(g, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Medical Expenditure by Gender",
            chart_type="bar",
            data=rows, x_axis="Gender", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    # ── Diagnostic Categories (Section 7) ───────────────────────────────────

    def analyze_diagnostic_categories(self) -> Optional[ChartData]:
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   CASE
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'A'
                       OR LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'B' THEN 'INFECTIOUS DISEASE'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'C'
                       OR (LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'D'
                           AND LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),3) <= 'D49') THEN 'CANCER/NEOPLASM'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'E' THEN 'ENDOCRINE/METABOLIC'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'F' THEN 'MENTAL HEALTH'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'G' THEN 'NERVOUS SYSTEM'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'H' THEN 'EYE/EAR'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'I' THEN 'CARDIOVASCULAR'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'J' THEN 'RESPIRATORY'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'K' THEN 'DIGESTIVE'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'L' THEN 'SKIN'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'M' THEN 'MUSCULOSKELETAL'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'N' THEN 'GENITOURINARY'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'O' THEN 'PREGNANCY'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) IN ('S','T') THEN 'INJURY/TRAUMA'
                     ELSE 'OTHER'
                   END AS category,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND RECONCILED_DIAGNOSIS_CODE_ICD10 IS NOT NULL
            GROUP BY 1,2 ORDER BY 1, 3 DESC
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, float] = {}
        for r in rows:
            c = str(r.get("CATEGORY", ""))
            totals[c] = totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
        top_cats = sorted(totals.items(), key=lambda x: x[1], reverse=True)[:10]
        return ChartData(
            title="Diagnostic Category Expenditures",
            chart_type="horizontalBar",
            data=rows, x_axis="Diagnostic Category", y_axis="Total Amount ($)",
            labels=[x[0] for x in top_cats],
            values=[x[1] for x in top_cats],
        )

    # ── Risk / Chronic / Diabetes ────────────────────────────────────────────

    def analyze_risk_groups(self) -> Optional[ChartData]:
        sql = f"""
            SELECT RISK_GROUP, FILE_YEAR,
                   SUM(TOTAL_PAID_AMT) AS total_amt,
                   AVG(TOTAL_PAID_AMT) AS mean_amt,
                   COUNT(DISTINCT UNIQUE_ID) AS n
            FROM {self.config.schema}.VW_RISK_GROUP_MIGRATION
            WHERE FILE_YEAR IN {self._get_years_clause()}
            GROUP BY 1,2 ORDER BY 2,1
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, float] = {}
        for r in rows:
            g = str(r.get("RISK_GROUP", ""))
            totals[g] = totals.get(g, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Disease Group Risk Stratification",
            chart_type="table",
            data=rows, x_axis="Risk Group", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    def analyze_chronic_diseases(self) -> Optional[ChartData]:
        sql = f"""
            SELECT CHRONIC_CAT, FILE_YEAR,
                   SUM(CHRONIC_PAID_AMT) AS total_amt,
                   COUNT(DISTINCT UNIQUE_ID) AS n
            FROM {self.config.schema}.VW_RISK_GROUP_MIGRATION
            WHERE FILE_YEAR IN {self._get_years_clause()}
              AND CHRONIC_CAT IS NOT NULL
            GROUP BY 1,2 ORDER BY 3 DESC
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, float] = {}
        for r in rows:
            c = str(r.get("CHRONIC_CAT", ""))
            totals[c] = totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Chronic Disease Expenditures",
            chart_type="horizontalBar",
            data=rows, x_axis="Chronic Category", y_axis="Total Amount ($)",
            labels=list(totals.keys())[:10], values=list(totals.values())[:10],
        )

    def analyze_diabetes_stratification(self) -> Optional[ChartData]:
        sql = f"""
            SELECT FILE_YEAR,
                   CASE
                     WHEN COMORBID_COUNT = 0 THEN '0'
                     WHEN COMORBID_COUNT = 1 THEN '1'
                     WHEN COMORBID_COUNT = 2 THEN '2'
                     WHEN COMORBID_COUNT = 3 THEN '3'
                     ELSE '4+'
                   END AS comorbid_range,
                   COUNT(DISTINCT UNIQUE_ID) AS n
            FROM {self.config.schema}.VW_RISK_GROUP_MIGRATION
            WHERE FILE_YEAR IN {self._get_years_clause()}
              AND CHRONIC_CAT LIKE '%DIABETES%'
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, int] = {}
        for r in rows:
            c = str(r.get("COMORBID_RANGE", ""))
            totals[c] = totals.get(c, 0) + int(r.get("N") or 0)
        return ChartData(
            title="Diabetes Risk Stratification by Co-morbidities",
            chart_type="bar",
            data=rows, x_axis="Co-morbidities", y_axis="Member Count",
            labels=list(totals.keys()), values=[float(v) for v in totals.values()],
        )

    def analyze_diabetes_complications(self) -> Optional[ChartData]:
        """Section 9 top table: Diabetes-specific complication categories by year (total $, N).
        Complication type is derived from CHRONIC_CAT by matching known diabetes complication keywords.
        """
        sql = f"""
            SELECT FILE_YEAR,
                   CHRONIC_CAT,
                   SUM(CHRONIC_PAID_AMT) AS total_amt,
                   COUNT(DISTINCT UNIQUE_ID) AS n
            FROM {self.config.schema}.VW_RISK_GROUP_MIGRATION
            WHERE FILE_YEAR IN {self._get_years_clause()}
              AND CHRONIC_CAT LIKE '%DIABETES%'
            GROUP BY 1,2 ORDER BY 1, 3 DESC
        """
        rows = self.snowflake.query(sql)
        if not rows: return None

        # Map CHRONIC_CAT to complication category based on disease keywords
        COMPLICATION_KEYWORDS = [
            ("Cardiovascular",      ["HEART DISEASE", "HEART FAILURE", "CORONARY", "CARDIAC"]),
            ("Peripheral Vascular", ["PERIPHERAL VASCULAR", "PERIPHERAL ARTERY", "PAD"]),
            ("Neuropathy",          ["NEUROPATHY", "NERVE"]),
            ("Nephropathy",         ["KIDNEY", "NEPHROPATHY", "RENAL", "CKD"]),
            ("Retinopathy",         ["RETINOPATHY", "RETINAL", "EYE"]),
            ("Hyperlipidemia",      ["HYPERLIPIDEMIA", "CHOLESTEROL"]),
            ("Hypertension",        ["HYPERTENSION", "BLOOD PRESSURE"]),
            ("Obesity",             ["OBESITY", "OBESE"]),
        ]

        def _classify(chronic_cat: str) -> str:
            upper = chronic_cat.upper()
            for label, keywords in COMPLICATION_KEYWORDS:
                if any(kw in upper for kw in keywords):
                    return label
            return "Other"

        # Re-aggregate by derived complication category
        agg: Dict[tuple, dict] = {}
        for r in rows:
            comp = _classify(str(r.get("CHRONIC_CAT", "")))
            yr = str(r.get("FILE_YEAR", ""))
            key = (yr, comp)
            if key not in agg:
                agg[key] = {"FILE_YEAR": yr, "COMPLICATION": comp, "TOTAL_AMT": 0.0, "N": 0}
            agg[key]["TOTAL_AMT"] += float(r.get("TOTAL_AMT") or 0)
            agg[key]["N"] += int(r.get("N") or 0)

        agg_rows = sorted(agg.values(), key=lambda x: (-float(x["TOTAL_AMT"]), x["FILE_YEAR"]))
        totals: Dict[str, float] = {}
        for r in agg_rows:
            c = str(r.get("COMPLICATION", ""))
            totals[c] = totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Diabetes Complications Expenditures",
            chart_type="horizontalBar",
            data=agg_rows, x_axis="Complication Type", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    def analyze_diabetes_comorbids(self) -> Optional[ChartData]:
        """Section 9 co-morbidities chart: number of diabetic members with each co-morbidity."""
        sql = f"""
            SELECT FILE_YEAR,
                   CHRONIC_CAT AS comorbid,
                   COUNT(DISTINCT UNIQUE_ID) AS n
            FROM {self.config.schema}.VW_RISK_GROUP_MIGRATION
            WHERE FILE_YEAR IN {self._get_years_clause()}
              AND UNIQUE_ID IN (
                  SELECT DISTINCT UNIQUE_ID
                  FROM {self.config.schema}.VW_RISK_GROUP_MIGRATION
                  WHERE FILE_YEAR IN {self._get_years_clause()}
                    AND CHRONIC_CAT LIKE '%DIABETES%'
              )
              AND CHRONIC_CAT IS NOT NULL
              AND CHRONIC_CAT NOT LIKE '%DIABETES%'
            GROUP BY 1,2 ORDER BY 1, 3 DESC
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, int] = {}
        for r in rows:
            c = str(r.get("COMORBID", ""))
            totals[c] = totals.get(c, 0) + int(r.get("N") or 0)
        top = sorted(totals.items(), key=lambda x: x[1], reverse=True)[:10]
        return ChartData(
            title="Diabetes Co-morbidities",
            chart_type="horizontalBar",
            data=rows, x_axis="Co-morbidity", y_axis="Member Count",
            labels=[x[0] for x in top], values=[float(x[1]) for x in top],
        )

    def analyze_diabetes_ebm_noncompliance(self) -> Optional[ChartData]:
        """Section 10: diabetic members non-compliant per EBM measure per year.

        Eligible = members in VW_RISK_GROUP_MIGRATION with CHRONIC_CAT LIKE '%DIABETES%'.
        Medication non-compliance: member has no dispensed ACE_INHIBITOR / ARB / DRI / STATIN
            drug in VW_MEDICATION_POSSESSION_RATIO for that year.
        Exam non-compliance (Foot Exam, Eye Exam, HbA1c): member has no matching CPT claim
            across PRIMARY_PROCEDURE_CODE or PROCEDURE_CODE_2..10 in STG_TAB_MEDICAL_DATA.
        """
        dc = self.config.medical_date_col

        # HEDIS CPT code sets — checked across all procedure code columns
        FOOT_EXAM_CPTS = ("'G0245','G0246','97597','97598','11055','11056','11057',"
                          "'11720','11721','Q4051'")
        EYE_EXAM_CPTS  = ("'92002','92004','92012','92014','92134','92250',"
                          "'99203','99204','99205','99213','99214','99215','2022F','2024F'")
        HBA1C_CPTS     = ("'83036','83037','3044F','3045F','3046F','2018F'")

        # Helper: one CASE per procedure code column, OR-ed together
        def _cpt_match(cpts: str) -> str:
            cols = [
                "PRIMARY_PROCEDURE_CODE",
                "PROCEDURE_CODE_2", "PROCEDURE_CODE_3", "PROCEDURE_CODE_4",
                "PROCEDURE_CODE_5", "PROCEDURE_CODE_6", "PROCEDURE_CODE_7",
                "PROCEDURE_CODE_8", "PROCEDURE_CODE_9", "PROCEDURE_CODE_10",
            ]
            conditions = " OR ".join(f"UPPER(TRIM({c})) IN ({cpts})" for c in cols)
            return f"CASE WHEN {conditions} THEN 1 ELSE 0 END"

        sql = f"""
            WITH DIABETIC_MEMBERS AS (
                SELECT DISTINCT UNIQUE_ID, FILE_YEAR AS yr
                FROM {self.config.schema}.VW_RISK_GROUP_MIGRATION
                WHERE FILE_YEAR IN {self._get_years_clause()}
                  AND CHRONIC_CAT LIKE '%DIABETES%'
            ),
            RX_FLAGS AS (
                SELECT
                    UNIQUE_ID,
                    YEAR AS yr,
                    MAX(CASE WHEN UPPER(TRIM(ACE_INHIBITOR)) = 'TRUE' THEN 1 ELSE 0 END) AS has_ace,
                    MAX(CASE WHEN UPPER(TRIM(ARB))           = 'TRUE' THEN 1 ELSE 0 END) AS has_arb,
                    MAX(CASE WHEN UPPER(TRIM(DRI))           = 'TRUE' THEN 1 ELSE 0 END) AS has_dri,
                    MAX(CASE WHEN STATIN_FLAG                = 1      THEN 1 ELSE 0 END) AS has_statin
                FROM {self.config.schema}.VW_MEDICATION_POSSESSION_RATIO
                WHERE YEAR IN {self._get_years_clause()}
                GROUP BY 1, 2
            ),
            EXAM_FLAGS AS (
                SELECT
                    {self.config.member_col} AS UNIQUE_ID,
                    YEAR({dc}) AS yr,
                    MAX({_cpt_match(FOOT_EXAM_CPTS)}) AS has_foot,
                    MAX({_cpt_match(EYE_EXAM_CPTS)})  AS has_eye,
                    MAX({_cpt_match(HBA1C_CPTS)})      AS has_hba1c
                FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
                WHERE YEAR({dc}) IN {self._get_years_clause()}
                GROUP BY 1, 2
            )
            SELECT
                d.yr                                                          AS year,
                COUNT(DISTINCT d.UNIQUE_ID)                                   AS total_eligible,
                COUNT(DISTINCT CASE WHEN COALESCE(r.has_ace,    0) = 0
                                    THEN d.UNIQUE_ID END)                     AS no_ace,
                COUNT(DISTINCT CASE WHEN COALESCE(r.has_arb,    0) = 0
                                    THEN d.UNIQUE_ID END)                     AS no_arb,
                COUNT(DISTINCT CASE WHEN COALESCE(r.has_dri,    0) = 0
                                    THEN d.UNIQUE_ID END)                     AS no_dri,
                COUNT(DISTINCT CASE WHEN COALESCE(r.has_statin, 0) = 0
                                    THEN d.UNIQUE_ID END)                     AS no_statin,
                COUNT(DISTINCT CASE WHEN COALESCE(e.has_foot,   0) = 0
                                    THEN d.UNIQUE_ID END)                     AS no_foot_exam,
                COUNT(DISTINCT CASE WHEN COALESCE(e.has_eye,    0) = 0
                                    THEN d.UNIQUE_ID END)                     AS no_eye_exam,
                COUNT(DISTINCT CASE WHEN COALESCE(e.has_hba1c,  0) = 0
                                    THEN d.UNIQUE_ID END)                     AS no_hba1c
            FROM DIABETIC_MEMBERS d
            LEFT JOIN RX_FLAGS   r ON r.UNIQUE_ID = d.UNIQUE_ID AND r.yr = d.yr
            LEFT JOIN EXAM_FLAGS e ON e.UNIQUE_ID = d.UNIQUE_ID AND e.yr = d.yr
            GROUP BY 1 ORDER BY 1
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(
            title="Diabetes Non-Compliance to Evidence-Based Medicine",
            chart_type="table",
            data=rows, x_axis="Year", y_axis="Non-Compliant Members",
            labels=[str(r["YEAR"]) for r in rows],
            values=[float(r.get("NO_HBA1C") or 0) for r in rows],
        )

    # ── Utilization ──────────────────────────────────────────────────────────

    def analyze_hospital_utilization(self) -> Optional[ChartData]:
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   CASE
                     WHEN UPPER(HOSPITALIZED_OR_NOT) = 'YES' THEN 'Inpatient'
                     WHEN UPPER(PLACE_OF_SERVICE_NAME) LIKE '%EMERGENCY%' THEN 'Emergency Room'
                     ELSE 'Outpatient'
                   END AS service_type,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, float] = {}
        for r in rows:
            s = str(r.get("SERVICE_TYPE", ""))
            totals[s] = totals.get(s, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Inpatient / Outpatient / Emergency Room Utilization",
            chart_type="bar",
            data=rows, x_axis="Service Type", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    def analyze_provider_type(self) -> Optional[ChartData]:
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   COALESCE(SERVICE_PROVIDER_TYPE_DESCRIPTION, 'OTHER') AS provider_type,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
            GROUP BY 1,2 ORDER BY 3 DESC
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, float] = {}
        for r in rows:
            p = str(r.get("PROVIDER_TYPE", ""))
            totals[p] = totals.get(p, 0) + float(r.get("TOTAL_AMT") or 0)
        top = sorted(totals.items(), key=lambda x: x[1], reverse=True)[:10]
        return ChartData(
            title="Expenditure by Provider / Service Type",
            chart_type="horizontalBar",
            data=rows, x_axis="Provider Type", y_axis="Total Amount ($)",
            labels=[x[0] for x in top], values=[x[1] for x in top],
        )

    # ── Demographics ─────────────────────────────────────────────────────────

    def analyze_demographics_pyramid(self) -> Optional[ChartData]:
        sql = f"""
            SELECT
                CASE 
                    WHEN PATIENT_AGE < 20 THEN 'Below 20'
                    WHEN PATIENT_AGE BETWEEN 20 AND 29 THEN '20 to 29'
                    WHEN PATIENT_AGE BETWEEN 30 AND 39 THEN '30 to 39'
                    WHEN PATIENT_AGE BETWEEN 40 AND 49 THEN '40 to 49'
                    WHEN PATIENT_AGE BETWEEN 50 AND 59 THEN '50 to 59'
                    ELSE '60 or Above'
                END AS age_group,
                {self.config.medical_gender_col} AS gender,
                COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE {self.config.medical_gender_col} IS NOT NULL
              AND PATIENT_AGE IS NOT NULL
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(
            title="Age / Gender Demographics",
            chart_type="pyramid",
            data=rows, x_axis="Age Group", y_axis="Member Count",
            labels=[], values=[],
        )

    # ── Preventive Screenings ────────────────────────────────────────────────

    def _analyze_screening(self, cancer_type: str, title: str) -> Optional[ChartData]:
        sql = f"""
            SELECT YEAR,
                   COUNT(DISTINCT UNIQUE_ID) AS eligible_n,
                   SUM(CASE WHEN SCREENING_DATE_MIN IS NOT NULL THEN 1 ELSE 0 END) AS screened_n
            FROM {self.config.schema}.VW_PREVENTIVE_SCREENING
            WHERE UPPER(CANCER_SCREENING) = UPPER('{cancer_type}')
              AND YEAR IN {self._get_years_clause()}
            GROUP BY 1 ORDER BY 1
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        for r in rows:
            eligible = int(r.get("ELIGIBLE_N") or 0)
            screened = int(r.get("SCREENED_N") or 0)
            r["SCREENING_RATE_PCT"] = round(screened / eligible * 100, 1) if eligible else 0
        return ChartData(
            title=title,
            chart_type="table",
            data=rows, x_axis="Year", y_axis="Screening Rate %",
            labels=[str(r["YEAR"]) for r in rows],
            values=[float(r.get("SCREENING_RATE_PCT") or 0) for r in rows],
        )

    def analyze_breast_cancer_screening(self) -> Optional[ChartData]:
        return self._analyze_screening("BREAST CANCER", "Breast Cancer Screening Compliance")

    def analyze_cervical_cancer_screening(self) -> Optional[ChartData]:
        return self._analyze_screening("CERVICAL CANCER", "Cervical Cancer Screening Compliance")

    def analyze_colon_cancer_screening(self) -> Optional[ChartData]:
        return self._analyze_screening("COLON CANCER", "Colon Cancer Screening Compliance")

    # ── Catastrophic ─────────────────────────────────────────────────────────

    def analyze_catastrophic_claims(self) -> Optional[ChartData]:
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   COUNT(*) AS claim_count,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE {self.config.medical_amount_col} >= 100000
              AND YEAR({dc}) IN {self._get_years_clause()}
            GROUP BY 1 ORDER BY 1
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(
            title="Catastrophic Claims (>= $100,000)",
            chart_type="bar",
            data=rows, x_axis="Year", y_axis="Total Amount ($)",
            labels=[str(r["YR"]) for r in rows],
            values=[float(r.get("TOTAL_AMT") or 0) for r in rows],
        )

    # ── Pharmacy sections ────────────────────────────────────────────────────

    def analyze_pharmacy_by_year(self) -> Optional[ChartData]:
        dc = self.config.pharmacy_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   SUM({self.config.pharmacy_amount_col}) AS total_amt,
                   AVG({self.config.pharmacy_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_PHARMACY_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
            GROUP BY 1 ORDER BY 1
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(
            title="Overall Pharmacy Expenditure by Year",
            chart_type="horizontalBar",
            data=rows, x_axis="Year", y_axis="Total Amount ($)",
            labels=[str(r["YR"]) for r in rows],
            values=[float(r.get("TOTAL_AMT") or 0) for r in rows],
        )

    def analyze_pharmacy_by_quarter(self) -> Optional[ChartData]:
        dc = self.config.pharmacy_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr, QUARTER({dc}) AS qtr,
                   SUM({self.config.pharmacy_amount_col}) AS total_amt,
                   AVG({self.config.pharmacy_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_PHARMACY_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(
            title="Overall Pharmacy Expenditure by Quarter",
            chart_type="bar",
            data=rows, x_axis="Quarter", y_axis="Total Amount ($)",
            labels=[f"{r['YR']}-Q{r['QTR']}" for r in rows],
            values=[float(r.get("TOTAL_AMT") or 0) for r in rows],
        )

    def analyze_pharmacy_by_relationship(self) -> Optional[ChartData]:
        dc = self.config.pharmacy_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   {self.config.pharmacy_rel_col} AS relationship,
                   SUM({self.config.pharmacy_amount_col}) AS total_amt,
                   AVG({self.config.pharmacy_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_PHARMACY_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND {self.config.pharmacy_rel_col} IS NOT NULL
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, float] = {}
        for r in rows:
            rel = r.get("RELATIONSHIP", "Unknown")
            totals[rel] = totals.get(rel, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Pharmacy Expenditure by Relationship",
            chart_type="bar",
            data=rows, x_axis="Relationship", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    def analyze_brand_generic(self) -> Optional[ChartData]:
        dc = self.config.pharmacy_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   CASE WHEN {self.config.brand_generic_col} = 'YES' THEN 'GENERIC'
                        WHEN {self.config.brand_generic_col} = 'NO'  THEN 'BRAND'
                        ELSE 'OTHER' END AS drug_type,
                   SUM({self.config.pharmacy_amount_col}) AS total_amt,
                   AVG({self.config.pharmacy_amount_col}) AS mean_amt,
                   COUNT(*) AS n
            FROM {self.config.schema}.STG_TAB_PHARMACY_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND {self.config.brand_generic_col} IS NOT NULL
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        totals: Dict[str, float] = {}
        for r in rows:
            t = str(r.get("DRUG_TYPE", ""))
            totals[t] = totals.get(t, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Brand vs. Generic Pharmaceutical Expenditure",
            chart_type="pie",
            data=rows, x_axis="Drug Type", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    def analyze_medication_compliance(self) -> Optional[ChartData]:
        sql = f"""
            SELECT YEAR,
                   ROUND(AVG(MPR_FINAL)*100, 1) AS all_mpr,
                   ROUND(AVG(CASE WHEN STATIN_FLAG = 1 THEN MPR_FINAL END)*100, 1) AS statin_mpr,
                   ROUND(AVG(CASE WHEN HYPERTENSION_FLAG = 1 THEN MPR_FINAL END)*100, 1) AS htn_mpr,
                   ROUND(AVG(CASE WHEN DIABETES_FLAG = 1 THEN MPR_FINAL END)*100, 1) AS diabetes_mpr,
                   COUNT(DISTINCT UNIQUE_ID) AS n
            FROM {self.config.schema}.VW_MEDICATION_POSSESSION_RATIO
            WHERE YEAR IN {self._get_years_clause()}
            GROUP BY 1 ORDER BY 1
        """
        rows = self.snowflake.query(sql)
        if not rows: return None
        return ChartData(
            title="Medication Possession Ratio (MPR) Compliance",
            chart_type="table",
            data=rows, x_axis="Year", y_axis="MPR %",
            labels=[str(r["YEAR"]) for r in rows],
            values=[float(r.get("ALL_MPR") or 0) for r in rows],
        )

    # ── Section 12: Lifestyle Modifiable & Preventive Utilization ────────────

    def analyze_lifestyle_modifiable(self) -> Optional[ChartData]:
        """Lifestyle-modifiable diagnoses (obesity, tobacco, alcohol, mental health) by year via ICD-10."""
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   CASE
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),3) IN ('E66','Z68') THEN 'OBESITY'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),3) IN ('F17','Z87') THEN 'TOBACCO USE'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),3) IN ('F10','Z71') THEN 'ALCOHOL USE'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),3) IN ('F11','F12','F13','F14','F15','F16','F19') THEN 'SUBSTANCE ABUSE'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),3) IN ('F32','F33','F41','F43','F44') THEN 'STRESS/MENTAL HEALTH'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),3) IN ('Z72') THEN 'SEDENTARY LIFESTYLE'
                   END AS category,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND RECONCILED_DIAGNOSIS_CODE_ICD10 IS NOT NULL
              AND LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),3) IN (
                  'E66','Z68','F17','Z87','F10','Z71',
                  'F11','F12','F13','F14','F15','F16','F19',
                  'F32','F33','F41','F43','F44','Z72'
              )
            GROUP BY 1,2 ORDER BY 1, 3 DESC
        """
        rows = self.snowflake.query(sql)
        if not rows:
            return None
        totals: Dict[str, float] = {}
        for r in rows:
            c = str(r.get("CATEGORY", ""))
            totals[c] = totals.get(c, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Lifestyle Modifiable & Preventive Utilization",
            chart_type="horizontalBar",
            data=rows, x_axis="Category", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    # ── Section 13: Estimated Lost Time & Cost due to Health Disparities ──────

    def analyze_health_disparities(self) -> Optional[ChartData]:
        """Diagnostic categories by year derived from ICD-10 chapter prefixes."""
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   CASE
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) IN ('A','B') THEN 'INFECTIOUS DISEASE'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'C' THEN 'CANCER/NEOPLASM'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'E' THEN 'ENDOCRINE/METABOLIC'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'F' THEN 'MENTAL HEALTH'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'I' THEN 'CARDIOVASCULAR'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'J' THEN 'RESPIRATORY'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'K' THEN 'DIGESTIVE'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'M' THEN 'MUSCULOSKELETAL'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) = 'N' THEN 'GENITOURINARY'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)),1) IN ('S','T') THEN 'INJURY/TRAUMA'
                     ELSE 'OTHER'
                   END AS category,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND RECONCILED_DIAGNOSIS_CODE_ICD10 IS NOT NULL
            GROUP BY 1,2 ORDER BY 1, 5 DESC
        """
        rows = self.snowflake.query(sql)
        if not rows:
            return None
        return ChartData(
            title="Estimated Lost Time & Cost due to Health Disparities",
            chart_type="table",
            data=rows, x_axis="Category", y_axis="N",
            labels=[], values=[],
        )

    # ── Section 15: Value of Preventive Screenings ────────────────────────────

    def analyze_preventive_screening_value(self) -> Optional[ChartData]:
        """Preventive screening counts (screened vs eligible) by cancer type and year."""
        sql = f"""
            SELECT YEAR,
                   CANCER_SCREENING,
                   COUNT(DISTINCT UNIQUE_ID) AS eligible_n,
                   SUM(CASE WHEN SCREENING_DATE_MIN IS NOT NULL THEN 1 ELSE 0 END) AS screened_n
            FROM {self.config.schema}.VW_PREVENTIVE_SCREENING
            WHERE YEAR IN {self._get_years_clause()}
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows:
            return None
        for r in rows:
            eligible = int(r.get("ELIGIBLE_N") or 0)
            screened = int(r.get("SCREENED_N") or 0)
            r["SCREENING_RATE_PCT"] = round(screened / eligible * 100, 1) if eligible else 0
        return ChartData(
            title="Value of Preventive Screenings",
            chart_type="table",
            data=rows, x_axis="Cancer Type", y_axis="Screened N",
            labels=[], values=[],
        )

    # ── Section 16: Work-Related Musculoskeletal Expenditures ─────────────────

    def analyze_musculoskeletal_work(self) -> Optional[ChartData]:
        """Potentially work-related MSK claims bucketed by body part via ICD-10 M-code prefix."""
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   CASE
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)), 3) IN ('M54','M47','M48','M51') THEN 'BACK'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)), 3) IN ('M75','M77') THEN 'SHOULDER'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)), 3) IN ('M65','M66','M67','M70') THEN 'HAND & WRIST'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)), 3) IN ('M17','M23','M25') THEN 'KNEE'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)), 3) IN ('M16','M24') THEN 'HIP'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)), 3) IN ('M19','M79','M60','M62') THEN 'UPPER EXTREMITY'
                     WHEN LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)), 3) IN ('M20','M21','M72') THEN 'LOWER EXTREMITY'
                     ELSE 'OTHER MSK'
                   END AS body_part,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND RECONCILED_DIAGNOSIS_CODE_ICD10 IS NOT NULL
              AND LEFT(UPPER(TRIM(RECONCILED_DIAGNOSIS_CODE_ICD10)), 1) = 'M'
            GROUP BY 1,2 ORDER BY 1, 3 DESC
        """
        rows = self.snowflake.query(sql)
        if not rows:
            return None
        totals: Dict[str, float] = {}
        for r in rows:
            bp = str(r.get("BODY_PART", ""))
            totals[bp] = totals.get(bp, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Potentially Work-Related Musculoskeletal Expenditures",
            chart_type="horizontalBar",
            data=rows, x_axis="Body Part", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    # ── Section 18: Inpatient / Outpatient / ER ───────────────────────────────

    def analyze_inpatient_outpatient_er(self) -> Optional[ChartData]:
        """ER, inpatient, outpatient expenditures by year with separate breakout."""
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   CASE
                     WHEN ADMIT_DATE IS NOT NULL THEN 'Inpatient'
                     ELSE 'Outpatient'
                   END AS service_type,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows:
            return None
        totals: Dict[str, float] = {}
        for r in rows:
            s = str(r.get("SERVICE_TYPE", ""))
            totals[s] = totals.get(s, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Inpatient / Outpatient / Emergency Room Expenditures",
            chart_type="bar",
            data=rows, x_axis="Service Type", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    # ── Section 19: Avoidable ER Visits ──────────────────────────────────────

    def analyze_avoidable_er(self) -> Optional[ChartData]:
        """ER visits for conditions treatable in primary care (AHRQ avoidable ER ICD codes)."""
        dc = self.config.medical_date_col
        # AHRQ-defined ambulatory-care-sensitive ICD-10 diagnosis prefixes
        AVOIDABLE_ICD_PREFIXES = (
            "'J06','J20','J45','J22','N39','K59','K57','K21','H66','J32',"
            "'N390','L03','R51','F41','G43'"
        )
        pos_col = self.config.pos_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   PRIMARY_DIAGNOSIS_CODE AS diagnosis,
                   PRIMARY_DIAGNOSIS_CODE AS icd_code,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
              AND (
                    LEFT(UPPER(TRIM(PRIMARY_DIAGNOSIS_CODE)), 3) IN ({AVOIDABLE_ICD_PREFIXES})
                 OR LEFT(UPPER(TRIM(PRIMARY_DIAGNOSIS_CODE)), 4) IN ({AVOIDABLE_ICD_PREFIXES})
              )
            GROUP BY 1,2,3 ORDER BY 1, 4 DESC
        """
        rows = self.snowflake.query(sql)
        if not rows:
            return None
        totals: Dict[str, float] = {}
        for r in rows:
            d = str(r.get("DIAGNOSIS", ""))
            totals[d] = totals.get(d, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Avoidable Emergency Room Visits",
            chart_type="table",
            data=rows, x_axis="Diagnosis", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    # ── Section 20: Primary Care Physician & Specialty Expenditures ───────────

    def analyze_pcp_vs_specialty(self) -> Optional[ChartData]:
        """PCP vs specialist expenditure split by year."""
        dc = self.config.medical_date_col
        sql = f"""
            SELECT YEAR({dc}) AS yr,
                   CASE
                     WHEN UPPER(COALESCE(SERVICE_PROVIDER_TYPE_DESCRIPTION,'')) LIKE '%PRIMARY%'
                          OR UPPER(COALESCE(SERVICE_PROVIDER_TYPE_DESCRIPTION,'')) LIKE '%FAMILY%'
                          OR UPPER(COALESCE(SERVICE_PROVIDER_TYPE_DESCRIPTION,'')) LIKE '%INTERNAL MED%'
                          OR UPPER(COALESCE(SERVICE_PROVIDER_TYPE_DESCRIPTION,'')) LIKE '%GENERAL PRACT%'
                       THEN 'Primary Care Physician Services'
                     ELSE 'Other/Specialty Services'
                   END AS provider_class,
                   SUM({self.config.medical_amount_col}) AS total_amt,
                   AVG({self.config.medical_amount_col}) AS mean_amt,
                   COUNT(DISTINCT {self.config.member_col}) AS n
            FROM {self.config.schema}.STG_TAB_MEDICAL_DATA
            WHERE YEAR({dc}) IN {self._get_years_clause()}
            GROUP BY 1,2 ORDER BY 1,2
        """
        rows = self.snowflake.query(sql)
        if not rows:
            return None
        totals: Dict[str, float] = {}
        for r in rows:
            p = str(r.get("PROVIDER_CLASS", ""))
            totals[p] = totals.get(p, 0) + float(r.get("TOTAL_AMT") or 0)
        return ChartData(
            title="Primary Care Physician & Specialty Expenditures",
            chart_type="bar",
            data=rows, x_axis="Provider Class", y_axis="Total Amount ($)",
            labels=list(totals.keys()), values=list(totals.values()),
        )

    # ── Section 23: Pharmacy by Relationship ─────────────────────────────────
    # (already have analyze_pharmacy_by_relationship — aliased below for clarity)

    def get_summary_stats(self) -> Dict[str, Any]:
        return {
            "schema": self.config.schema,
            "years": self.config.years,
            "charts_generated": len(self._chart_cache),
            "timestamp": datetime.now().isoformat(),
        }