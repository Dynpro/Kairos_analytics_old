"""
Snowflake Connector for Python PHM Worker

Direct connection to Snowflake using snowflake-connector-python.
"""

from __future__ import annotations

import logging
import os
from pathlib import Path
from typing import Any, Dict, List, Optional

try:
    import snowflake.connector
    from snowflake.connector.errors import DatabaseError
    from snowflake.connector import DictCursor
except ImportError:
    raise ImportError("snowflake-connector-python is required. Please install it.")


class SnowflakeConnector:
    """Direct Snowflake API connector"""
    
    def __init__(self, env_file: Optional[Path] = None):
        self.logger = logging.getLogger("snowflake_connector")
        self._load_config(env_file)
        self.conn = None
    
    def _load_config(self, env_file: Optional[Path] = None) -> None:
        """Load configuration from environment or .env file"""
        if env_file and env_file.exists():
            for line in env_file.read_text(encoding="utf-8").splitlines():
                line = line.strip()
                if line and not line.startswith("#") and "=" in line:
                    key, value = line.split("=", 1)
                    key = key.strip()
                    value = value.strip().strip('"').strip("'")
                    os.environ[key] = value
        
        self.host = os.getenv("SNOWFLAKE_HOST", "")
        self.account = os.getenv("SNOWFLAKE_ACCOUNT", "")
        self.username = os.getenv("SNOWFLAKE_USERNAME", "") or os.getenv("SNOWFLAKE_USER", "")
        self.password = os.getenv("SNOWFLAKE_PASSWORD", "")
        self.warehouse = os.getenv("SNOWFLAKE_WAREHOUSE", "")
        self.database = os.getenv("SNOWFLAKE_DATABASE", "DB_ANALYTICS")
        self.role = os.getenv("SNOWFLAKE_ROLE", "SYSADMIN")
        
        if not self.account or not self.username:
            self.logger.warning("Snowflake credentials not fully configured")
    
    def _ensure_connection(self) -> bool:
        """Authenticate and ensure we have a valid connection"""
        if self.conn and not self.conn.is_closed():
            return True
        
        if not self.account or not self.username or not self.password:
            self.logger.error("Missing Snowflake credentials")
            return False
        
        try:
            self.conn = snowflake.connector.connect(
                user=self.username,
                password=self.password,
                account=self.account,
                warehouse=self.warehouse,
                database=self.database,
                role=self.role
            )
            self.logger.info("Successfully authenticated with Snowflake")
            return True
        except Exception as e:
            self.logger.error(f"Snowflake authentication error: {e}")
            return False
    
    def query(self, sql: str, schema: Optional[str] = None) -> Optional[List[Dict[str, Any]]]:
        """
        Execute a single query against Snowflake
        
        Args:
            sql: SQL query to execute
            schema: Optional schema name (will be prefixed to database)
        
        Returns:
            List of dictionaries representing rows, or None on error
        """
        if not self._ensure_connection():
            return None
            
        try:
            with self.conn.cursor(DictCursor) as cur:
                if schema:
                    cur.execute(f"USE SCHEMA {self.database}.{schema}")
                cur.execute(sql)
                return cur.fetchall()
        except DatabaseError as e:
            self.logger.error(f"Query error: {e}")
            return None
    
    def query_parallel(self, queries: Dict[str, str], schema: Optional[str] = None) -> Dict[str, List[Dict[str, Any]]]:
        """
        Execute multiple queries sequentially (simulating parallel for API compatibility)
        """
        results = {}
        for key, sql in queries.items():
            results[key] = self.query(sql, schema) or []
        return results
    
    def get_tables(self, schema: str) -> List[Dict[str, Any]]:
        """Get list of tables in a schema"""
        sql = f"""
            SELECT TABLE_NAME, TABLE_TYPE, TABLE_SCHEMA
            FROM {self.database}.INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '{schema.upper()}'
            ORDER BY TABLE_NAME
        """
        return self.query(sql) or []
    
    def get_columns(self, schema: str, table: str) -> List[Dict[str, Any]]:
        """Get column information for a table"""
        sql = f"""
            SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
            FROM {self.database}.INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{schema.upper()}'
              AND TABLE_NAME = '{table.upper()}'
            ORDER BY ORDINAL_POSITION
        """
        return self.query(sql) or []
    
    def test_connection(self) -> bool:
        """Test the Snowflake connection"""
        # If credentials are missing, return False to trigger mock data mode
        if not self.account or not self.username or not self.password:
            self.logger.warning("Snowflake credentials not configured - will use mock data")
            return False
        
        result = self.query("SELECT 1 as test")
        if result:
            self.logger.info("Snowflake connection test successful")
            return True
        self.logger.error("Snowflake connection test failed")
        return False
    
    def close(self) -> None:
        """Close the connection"""
        if self.conn:
            self.conn.close()
            self.conn = None
        self.logger.info("Snowflake connection closed")


# For testing when run directly
if __name__ == "__main__":
    import sys
    
    logging.basicConfig(level=logging.INFO)
    
    env_path = Path(__file__).parent.parent / ".env"
    connector = SnowflakeConnector(env_path if env_path.exists() else None)
    
    if connector.test_connection():
        print("✓ Snowflake connection successful")
        
        tables = connector.get_tables("STG_TAB")
        print(f"Found {len(tables)} tables")
    else:
        print("✗ Snowflake connection failed")
        sys.exit(1)