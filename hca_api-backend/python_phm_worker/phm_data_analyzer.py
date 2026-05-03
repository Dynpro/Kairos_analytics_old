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
    pos_col: str = "PLACE_OF_SERVICE_NAME"


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
            ("med_by_year",        self.analyze_medical_by_year),
            ("med_by_quarter",     self.analyze_medical_by_quarter),
            ("emp_spouse_dep",     self.analyze_employee_breakdown),
            ("gender_exp",         self.analyze_gender_expenditure),
            ("risk_groups",        self.analyze_risk_groups),
            ("chronic_diseases",   self.analyze_chronic_diseases),
            ("diabetes_strat",     self.analyze_diabetes_stratification),
            ("hospital_util",      self.analyze_hospital_utilization),
            ("provider_type",      self.analyze_provider_type),
            ("demographics",       self.analyze_demographics_pyramid),
            ("breast_screening",   self.analyze_breast_cancer_screening),
            ("cervical_screening", self.analyze_cervical_cancer_screening),
            ("colon_screening",    self.analyze_colon_cancer_screening),
            ("catastrophic",       self.analyze_catastrophic_claims),
            ("pharm_by_year",      self.analyze_pharmacy_by_year),
            ("pharm_by_quarter",   self.analyze_pharmacy_by_quarter),
            ("pharm_relationship", self.analyze_pharmacy_by_relationship),
            ("brand_generic",      self.analyze_brand_generic),
            ("medication_mpr",     self.analyze_medication_compliance),
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
                   CASE WHEN {self.config.brand_generic_col} = 'YES' THEN 'Generic'
                        WHEN {self.config.brand_generic_col} = 'NO'  THEN 'Brand'
                        ELSE 'Other' END AS drug_type,
                   SUM({self.config.pharmacy_amount_col}) AS total_amt,
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

    def get_summary_stats(self) -> Dict[str, Any]:
        return {
            "schema": self.config.schema,
            "years": self.config.years,
            "charts_generated": len(self._chart_cache),
            "timestamp": datetime.now().isoformat(),
        }