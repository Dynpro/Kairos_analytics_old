from __future__ import annotations

import argparse
import logging
import os
import subprocess
import sys
import time
from dataclasses import dataclass
from pathlib import Path
from typing import Any, Dict, Iterable, List, Optional

try:
    import pymysql
    from pymysql.cursors import DictCursor
except ImportError as exc:  # pragma: no cover
    raise SystemExit(
        "Missing dependency 'PyMySQL'. Install requirements from "
        "'python_phm_worker/requirements.txt' before running the worker."
    ) from exc

# Import PHM modules
from snowflake_connector import SnowflakeConnector
from phm_data_analyzer import PHMDataAnalyzer, PHMReportConfig
from chart_generator import ChartGenerator, PDFReportGenerator


def load_env_file(env_path: Path) -> Dict[str, str]:
    values: Dict[str, str] = {}
    if not env_path.exists():
        return values

    for raw_line in env_path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        value = value.strip().strip('"').strip("'")
        values[key.strip()] = value
    return values


def env_value(key: str, default: str, file_values: Dict[str, str]) -> str:
    return os.getenv(key) or file_values.get(key, default)


def env_int(key: str, default: int, file_values: Dict[str, str]) -> int:
    try:
        return int(env_value(key, str(default), file_values))
    except ValueError:
        return default


@dataclass
class WorkerConfig:
    backend_root: Path
    env_path: Path
    driver: str
    db_host: str
    db_port: int
    db_name: str
    db_user: str
    db_password: str
    poll_seconds: int
    batch_size: int
    output_dir: Path
    log_file: Path
    # Snowflake configuration
    snowflake_host: str = ""
    snowflake_account: str = ""
    snowflake_username: str = ""
    snowflake_password: str = ""
    snowflake_warehouse: str = ""
    snowflake_database: str = ""
    snowflake_role: str = ""

    @classmethod
    def from_backend_root(cls, backend_root: Path) -> "WorkerConfig":
        env_path = backend_root / ".env"
        file_values = load_env_file(env_path)

        output_dir = backend_root / env_value(
            "PYTHON_PHM_OUTPUT_DIR", "storage/app/python-phm", file_values
        )
        log_file = backend_root / env_value(
            "PYTHON_PHM_LOG_FILE", "storage/logs/python-phm-worker.log", file_values
        )

        return cls(
            backend_root=backend_root,
            env_path=env_path,
            driver=env_value("PHM_GENERATOR_DRIVER", "php", file_values).lower(),
            db_host=env_value("DB_HOST", "127.0.0.1", file_values),
            db_port=env_int("DB_PORT", 3306, file_values),
            db_name=env_value("DB_DATABASE", "", file_values),
            db_user=env_value("DB_USERNAME", "", file_values),
            db_password=env_value("DB_PASSWORD", "", file_values),
            poll_seconds=max(1, env_int("PYTHON_PHM_WORKER_POLL_SECONDS", 15, file_values)),
            batch_size=max(1, env_int("PYTHON_PHM_WORKER_BATCH_SIZE", 1, file_values)),
            output_dir=output_dir,
            log_file=log_file,
            # Snowflake config
            snowflake_host=env_value("SNOWFLAKE_HOST", "", file_values),
            snowflake_account=env_value("SNOWFLAKE_ACCOUNT", "", file_values),
            snowflake_username=env_value("SNOWFLAKE_USERNAME", "", file_values),
            snowflake_password=env_value("SNOWFLAKE_PASSWORD", "", file_values),
            snowflake_warehouse=env_value("SNOWFLAKE_WAREHOUSE", "", file_values),
            snowflake_database=env_value("SNOWFLAKE_DATABASE", "DB_ANALYTICS", file_values),
            snowflake_role=env_value("SNOWFLAKE_ROLE", "", file_values),
        )


class PHMWorker:
    def __init__(self, config: WorkerConfig) -> None:
        self.config = config
        self.logger = self._build_logger()

    def _build_logger(self) -> logging.Logger:
        self.config.log_file.parent.mkdir(parents=True, exist_ok=True)
        logger = logging.getLogger("python_phm_worker")
        if logger.handlers:
            return logger

        logger.setLevel(logging.INFO)
        formatter = logging.Formatter("%(asctime)s [%(levelname)s] %(message)s")

        file_handler = logging.FileHandler(self.config.log_file, encoding="utf-8")
        file_handler.setFormatter(formatter)
        logger.addHandler(file_handler)

        stream_handler = logging.StreamHandler(sys.stdout)
        stream_handler.setFormatter(formatter)
        logger.addHandler(stream_handler)
        return logger

    def db_connection(self):
        return pymysql.connect(
            host=self.config.db_host,
            port=self.config.db_port,
            user=self.config.db_user,
            password=self.config.db_password,
            database=self.config.db_name,
            cursorclass=DictCursor,
            autocommit=False,
            charset="utf8mb4",
        )

    def doctor(self) -> int:
        self.logger.info("PHM generator driver: %s", self.config.driver)
        self.logger.info("Backend root: %s", self.config.backend_root)
        self.logger.info("Env file: %s", self.config.env_path)

        with self.db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute("SELECT COUNT(*) AS report_count FROM report")
                row = cur.fetchone() or {}
                self.logger.info("Database connection OK. report_count=%s", row.get("report_count"))

        if self.config.driver != "python":
            self.logger.warning(
                "PHM_GENERATOR_DRIVER is '%s'. PHP PHM jobs are still the active generator until you switch it to 'python'.",
                self.config.driver,
            )
        return 0

    def run_forever(self) -> int:
        self.logger.info("Starting Python PHM worker loop. poll_seconds=%s", self.config.poll_seconds)
        while True:
            self.run_once()
            time.sleep(self.config.poll_seconds)

    def run_once(self) -> bool:
        if self.config.driver != "python":
            self.logger.warning(
                "PHM_GENERATOR_DRIVER is '%s'. Python PHM worker is disabled until you switch it to 'python'.",
                self.config.driver,
            )
            return False

        # Use the new Python-based Snowflake PHM generation
        return self.run_python_phm_once()

    def run_python_phm_once(self) -> bool:
        """
        Run PHM report generation using Python with direct Snowflake queries.
        This replaces the Looker API calls with direct data analysis.
        """
        self.logger.info("Starting Python-based PHM report generation")
        
        # Get next pending report from database
        report = self.claim_next_report_python()
        if not report:
            self.logger.info("No pending reports found")
            return False
        
        report_id = report["report_id"]
        self.logger.info(f"Processing report {report_id}: {report.get('name', 'Unknown')}")
        
        try:
            # Step 1: Connect to Snowflake and analyze data
            charts = self._generate_phm_charts_from_snowflake(report)
            if not charts:
                self.logger.error(f"Failed to generate charts for report {report_id}")
                self._update_report_status(report_id, 7)  # Failed
                return False
            
            # Step 2: Generate chart images
            chart_files = self._generate_chart_images(report_id, charts)
            
            # Step 3: Generate PDF report
            pdf_path = self._generate_pdf_report(report_id, report, chart_files, charts)
            if not pdf_path:
                self.logger.error(f"Failed to generate PDF for report {report_id}")
                self._update_report_status(report_id, 7)  # Failed
                return False
            
            # Step 3.5: Upload to S3
            report_name = report.get("name", f"PHM_Report_{report_id}")
            s3_path = self._upload_to_s3(pdf_path, report_name)
            final_path = s3_path if s3_path else pdf_path
            
            # Step 4: Update database with file path
            self._update_report_file_path(report_id, final_path)
            self._update_report_status(report_id, 6)  # Completed
            
            self.logger.info(f"Successfully generated PHM report {report_id}: {pdf_path}")
            return True
            
        except Exception as e:
            self.logger.error(f"Error generating PHM report {report_id}: {e}")
            self._update_report_status(report_id, 7)  # Failed
            return False

    def _upload_to_s3(self, local_pdf_path: str, report_name: str) -> Optional[str]:
        """Uploads the local PDF to S3 and returns the S3 key."""
        try:
            import boto3
            import datetime
            
            s3_key = os.getenv("AWS_ACCESS_KEY_ID") or self.config.env_value("AWS_ACCESS_KEY_ID", "")
            s3_secret = os.getenv("AWS_SECRET_ACCESS_KEY") or self.config.env_value("AWS_SECRET_ACCESS_KEY", "")
            s3_region = os.getenv("AWS_DEFAULT_REGION") or self.config.env_value("AWS_DEFAULT_REGION", "us-east-1")
            s3_bucket = os.getenv("AWS_BUCKET") or self.config.env_value("AWS_BUCKET", "")
            
            if not all([s3_key, s3_secret, s3_bucket]):
                self.logger.warning("S3 credentials not fully configured. Skipping S3 upload.")
                return None
                
            s3_client = boto3.client(
                's3',
                aws_access_key_id=s3_key,
                aws_secret_access_key=s3_secret,
                region_name=s3_region
            )
            
            date_str = datetime.datetime.now().strftime("%md%y")
            # Mimic PHP format: $filePath = 'Generated_PHM/' . $name . '_' . date('mdy') . '.pdf';
            s3_file_key = f"Generated_PHM/{report_name}_{date_str}.pdf"
            
            self.logger.info(f"Uploading {local_pdf_path} to S3 bucket {s3_bucket} as {s3_file_key}...")
            s3_client.upload_file(local_pdf_path, s3_bucket, s3_file_key)
            self.logger.info("S3 upload complete.")
            return s3_file_key
            
        except Exception as e:
            self.logger.error(f"Failed to upload to S3: {e}")
            return None

    def claim_next_report_python(self) -> Optional[Dict[str, Any]]:
        """Claim the next pending report for Python-based processing"""
        with self.db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute("START TRANSACTION")
                cur.execute(
                    """
                    SELECT report_id, name, report_name, year, user_id, phm_folder_id,
                           reporting_year, frequency, schema_name, looks_generated,
                           medical_start_date, medical_end_date,
                           pharmacy_start_date, pharmacy_end_date, file_path
                    FROM report
                    WHERE is_active = 1
                      AND (frequency = 1 OR frequency = 2)
                      AND COALESCE(looks_generated, 0) IN (0, 1, 2)
                      AND (file_path IS NULL OR file_path = '')
                    ORDER BY report_id ASC
                    LIMIT 1
                    FOR UPDATE
                    """
                )
                report = cur.fetchone()
                if not report:
                    conn.commit()
                    return None

                # Mark as in progress
                cur.execute(
                    "UPDATE report SET looks_generated = 1 WHERE report_id = %s",
                    (report["report_id"],),
                )
                conn.commit()
                report["looks_generated"] = 1
                return report

    def _generate_phm_charts_from_snowflake(self, report: Dict[str, Any]) -> Dict[str, Any]:
        """Generate PHM charts by querying Snowflake directly"""
        
        # Get schema from report or client_folder_mapping
        schema = report.get("schema_name")
        if not schema:
            with self.db_connection() as conn:
                with conn.cursor() as cur:
                    cur.execute(
                        "SELECT schema_name FROM client_folder_mapping WHERE folder_id = %s",
                        (report["phm_folder_id"],)
                    )
                    row = cur.fetchone()
                    schema = row["schema_name"] if row else None
        
        if not schema:
            raise ValueError(f"No schema found for report {report['report_id']}")
        
        # Parse years from report
        years_str = report.get("year", "")
        years = [int(y.strip()) for y in years_str.split(",") if y.strip().isdigit()]
        if not years:
            years = [2025]  # Default to current year
        
        # Create Snowflake connector
        snowflake = SnowflakeConnector(self.config.env_path)
        
        # Test connection
        if not snowflake.test_connection():
            raise ConnectionError("Failed to connect to Snowflake")
        
        # Create PHM config
        config = PHMReportConfig(
            schema=schema,
            years=years,
            reporting_year=report.get("reporting_year", "Service"),
        )
        
        # Analyze data
        analyzer = PHMDataAnalyzer(snowflake, config)
        charts = analyzer.analyze_all()
        
        # Get summary stats
        stats = analyzer.get_summary_stats()
        
        snowflake.close()
        
        return {
            "charts": charts,
            "stats": stats,
            "schema": schema,
            "years": years,
        }

    def _generate_chart_images(self, report_id: int, chart_data: Dict[str, Any]) -> Dict[str, str]:
        """Generate chart images from chart data"""
        
        output_dir = self.config.output_dir / "charts"
        output_dir.mkdir(parents=True, exist_ok=True)
        
        generator = ChartGenerator(output_dir)
        chart_files = {}
        
        for key, chart in chart_data.get("charts", {}).items():
            try:
                file_path = generator.generate_chart(chart, report_id, key)
                if file_path:
                    chart_files[key] = file_path
                    self.logger.info(f"Generated chart: {key}")
            except Exception as e:
                self.logger.error(f"Failed to generate chart {key}: {e}")
        
        return chart_files

    def _generate_pdf_report(
        self,
        report_id: int,
        report: Dict[str, Any],
        chart_files: Dict[str, str],
        chart_data: Dict[str, Any],
    ) -> Optional[str]:
        """Generate the final PDF report"""
        
        output_dir = self.config.output_dir
        output_dir.mkdir(parents=True, exist_ok=True)
        
        generator = PDFReportGenerator(output_dir)
        
        # Get client name
        client_name = report.get("name", "Unknown Client")
        
        # Get reporting year
        years = chart_data.get("years", [2025])
        year = max(years) if years else 2025
        
        # Build metadata
        metadata = dict(chart_data.get("stats", {}))
        metadata["schema"] = chart_data.get("schema", "")
        metadata["total_charts"] = len(chart_files)
        # Inject report date ranges so the cover page can display them
        metadata["medical_start_date"] = str(report.get("medical_start_date") or "")
        metadata["medical_end_date"] = str(report.get("medical_end_date") or "")
        metadata["pharmacy_start_date"] = str(report.get("pharmacy_start_date") or "")
        metadata["pharmacy_end_date"] = str(report.get("pharmacy_end_date") or "")
        
        pdf_path = generator.generate_pdf(
            report_id=report_id,
            report_name=report.get("report_name", "PHM Report"),
            client_name=client_name,
            year=year,
            charts=chart_data.get("charts", {}), # Pass full ChartData objects
            chart_files=chart_files,             # Keep image paths
            metadata=metadata,
        )
        
        return pdf_path

    def _update_report_status(self, report_id: int, status: int) -> None:
        """Update the report status in the database"""
        with self.db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    "UPDATE report SET looks_generated = %s WHERE report_id = %s",
                    (status, report_id)
                )
                conn.commit()

    def _update_report_file_path(self, report_id: int, file_path: str) -> None:
        """Update the report file path in the database"""
        with self.db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    "UPDATE report SET file_path = %s WHERE report_id = %s",
                    (file_path, report_id)
                )
                conn.commit()

    def run_php_command(self, artisan_command: str) -> tuple[int, str, str]:
        self.logger.info("Running PHP artisan command: %s", artisan_command)
        process = subprocess.run(
            ["php", "artisan", artisan_command],
            cwd=str(self.config.backend_root),
            capture_output=True,
            text=True,
        )

        stdout = process.stdout or ""
        stderr = process.stderr or ""
        if stdout.strip():
            self.logger.info("%s stdout: %s", artisan_command, stdout.strip())
        if stderr.strip():
            self.logger.warning("%s stderr: %s", artisan_command, stderr.strip())

        return process.returncode, stdout, stderr

    def claim_next_report(self) -> Optional[Dict[str, Any]]:
        with self.db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute("START TRANSACTION")
                cur.execute(
                    """
                    SELECT report_id, name, report_name, year, user_id, phm_folder_id,
                           reporting_year, frequency, schema_name, looks_generated,
                           medical_start_date, medical_end_date,
                           pharmacy_start_date, pharmacy_end_date, file_path
                    FROM report
                    WHERE is_active = 1
                      AND frequency = 1
                      AND COALESCE(looks_generated, 0) = 0
                    ORDER BY report_id ASC
                    LIMIT 1
                    FOR UPDATE
                    """
                )
                report = cur.fetchone()
                if not report:
                    conn.commit()
                    return None

                cur.execute(
                    "UPDATE report SET looks_generated = 1 WHERE report_id = %s",
                    (report["report_id"],),
                )
                conn.commit()
                report["looks_generated"] = 1
                return report

    def prepare_report_shell(self, report: Dict[str, Any]) -> None:
        report_id = report["report_id"]
        with self.db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute("START TRANSACTION")
                cur.execute("DELETE FROM report_look WHERE report_id = %s", (report_id,))

                cur.execute(
                    """
                    SELECT sub_sections.id,
                           sub_sections.sub_section_no,
                           sub_sections.section_id,
                           sub_sections.look_id,
                           phm.client_id
                    FROM sub_sections
                    INNER JOIN sections ON sub_sections.section_id = sections.id
                    INNER JOIN phm ON sections.phm_id = phm.id
                    WHERE phm.client_id = %s
                      AND phm.is_active = 1
                    ORDER BY sub_sections.section_id ASC, sub_sections.sub_section_no ASC
                    """,
                    (report["phm_folder_id"],),
                )
                rows = cur.fetchall() or []

                for row in rows:
                    payload = {
                        "report_id": report_id,
                        "section_id": row["section_id"],
                        "sub_section_id": row["id"],
                        "sub_section_no": row["sub_section_no"],
                        "look_id": row["look_id"],
                        "chart_type": None,
                        "embed_url": "",
                        "look_url": "",
                    }

                    if row["look_id"]:
                        cur.execute(
                            "SELECT embed_url FROM studio_dashboards WHERE id = %s LIMIT 1",
                            (row["look_id"],),
                        )
                        dashboard = cur.fetchone()
                        if dashboard and dashboard.get("embed_url"):
                            payload["chart_type"] = "studio"
                            payload["embed_url"] = self.build_studio_url(
                                dashboard["embed_url"], report["year"]
                            )
                            payload["look_url"] = payload["embed_url"]

                    cur.execute(
                        """
                        INSERT INTO report_look (
                            report_id, section_id, sub_section_id, sub_section_no,
                            look_id, chart_type, embed_url, look_url
                        )
                        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                        """,
                        (
                            payload["report_id"],
                            payload["section_id"],
                            payload["sub_section_id"],
                            payload["sub_section_no"],
                            payload["look_id"],
                            payload["chart_type"],
                            payload["embed_url"],
                            payload["look_url"],
                        ),
                    )

                cur.execute(
                    "UPDATE report SET looks_generated = 2 WHERE report_id = %s",
                    (report_id,),
                )
                conn.commit()

        self.logger.info("Prepared report shell for report %s with report_look rows.", report_id)

    def generate_chart_data(self, report: Dict[str, Any]) -> None:
        raise NotImplementedError(
            "Port the Snowflake query + QuickChart logic from "
            "app/Console/Commands/PHMDataCron.php into python_phm_worker/worker.py."
        )

    def generate_pdf(self, report: Dict[str, Any]) -> None:
        raise NotImplementedError(
            "Port the final PDF assembly logic from "
            "app/Http/Controllers/GenerateReportController.php::generate_pdf into Python."
        )

    def mark_report_complete(self, report_id: int, file_path: Optional[str]) -> None:
        with self.db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    "UPDATE report SET looks_generated = 6, file_path = %s WHERE report_id = %s",
                    (file_path, report_id),
                )
                conn.commit()

    def mark_report_failed(self, report_id: int) -> None:
        with self.db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    "UPDATE report SET looks_generated = 7 WHERE report_id = %s",
                    (report_id,),
                )
                conn.commit()

    @staticmethod
    def build_studio_url(base_url: str, year_csv: str) -> str:
        base_url = base_url.rstrip("&?")
        year_param = '{"ds0.year": "%s"}' % str(year_csv)
        separator = "?" if "?" not in base_url else "&"
        from urllib.parse import quote

        return f"{base_url}{separator}params={quote(year_param)}"


def parse_args(argv: Iterable[str]) -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Python PHM background worker")
    parser.add_argument(
        "mode",
        choices=["doctor", "once", "daemon"],
        help="doctor: verify config/db, once: process one report, daemon: poll continuously",
    )
    parser.add_argument(
        "--backend-root",
        default=str(Path(__file__).resolve().parents[1]),
        help="Path to the hca_api-backend directory",
    )
    return parser.parse_args(list(argv))


def main(argv: Iterable[str]) -> int:
    args = parse_args(argv)
    config = WorkerConfig.from_backend_root(Path(args.backend_root).resolve())
    worker = PHMWorker(config)

    if args.mode == "doctor":
        return worker.doctor()
    if args.mode == "once":
        worker.run_once()
        return 0
    return worker.run_forever()


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
