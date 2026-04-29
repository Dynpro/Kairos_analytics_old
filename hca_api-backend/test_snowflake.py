import requests
import urllib3
urllib3.disable_warnings()

# Using the manual env loading from worker.py
def load_env():
    values = {}
    with open(".env", "r") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            key, value = line.split("=", 1)
            values[key.strip()] = value.strip().strip('"').strip("'")
    return values

env = load_env()
host = env.get("SNOWFLAKE_HOST", "")
username = env.get("SNOWFLAKE_USERNAME", "") or env.get("SNOWFLAKE_USER", "")
password = env.get("SNOWFLAKE_PASSWORD", "")
account = env.get("SNOWFLAKE_ACCOUNT", "")
warehouse = env.get("SNOWFLAKE_WAREHOUSE", "")
role = env.get("SNOWFLAKE_ROLE", "")

login_url = f"https://{host}/oauth/token-request"

payload = {
    "grant_type": "password",
    "username": username,
    "password": password,
    "account": account,
    "warehouse": warehouse,
    "role": role,
}

headers = {
    "Content-Type": "application/x-www-form-urlencoded",
    "Accept": "application/json",
}

response = requests.post(login_url, data=payload, headers=headers, verify=False)
print("URL:", login_url)
print("Status:", response.status_code)
print("Text:", response.text)
