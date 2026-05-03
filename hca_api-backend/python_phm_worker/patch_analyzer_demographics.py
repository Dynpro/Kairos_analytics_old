with open('phm_data_analyzer.py', 'r', encoding='utf-8') as f:
    content = f.read()

demographics_methods = '''
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
        return ChartData(title="Demographics - Relationship & Age", chart_type="bar", data=rows)

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
        return ChartData(title="Demographics - Age Group", chart_type="horizontalBar", data=rows)

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
        return ChartData(title="Demographics - Gender", chart_type="horizontalBar", data=rows)
'''

content = content.replace('    def analyze_medical_by_quarter(self)', demographics_methods + '\n    def analyze_medical_by_quarter(self)')

all_old = '''        analyses = [
            ("med_by_year",        self.analyze_medical_by_year),
            ("med_by_quarter",     self.analyze_medical_by_quarter),'''

all_new = '''        analyses = [
            ("demographics_rel_age", self.analyze_demographics_rel_age),
            ("demographics_age_group", self.analyze_demographics_age_group),
            ("demographics_gender_pct", self.analyze_demographics_gender_pct),
            ("med_by_year",        self.analyze_medical_by_year),
            ("med_by_quarter",     self.analyze_medical_by_quarter),'''

content = content.replace(all_old, all_new)

with open('phm_data_analyzer.py', 'w', encoding='utf-8') as f:
    f.write(content)
print("Added demographics successfully")
