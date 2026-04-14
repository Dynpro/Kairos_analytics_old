import json
import requests
import time

def check_dashboard(title, dash_id):
    if dash_id.startswith('s/'):
        url = f"https://lookerstudio.google.com/embed/{dash_id}"
    else:
        url = f"https://lookerstudio.google.com/embed/reporting/{dash_id}"
    
    try:
        # We use a user agent to look like a real browser
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        }
        response = requests.get(url, headers=headers, timeout=10, allow_redirects=True)
        
        status = "WORKING" if response.status_code == 200 else "BROKEN"
        # Check if we were redirected to an error page
        if "/reporting/error" in response.url:
            status = "ERROR_PAGE"
        elif "Can't access report" in response.text:
            status = "ACCESS_DENIED"
            
        return {
            'title': title,
            'dash_id': dash_id,
            'url': url,
            'status_code': response.status_code,
            'final_url': response.url,
            'status': status
        }
    except Exception as e:
        return {
            'title': title,
            'dash_id': dash_id,
            'url': url,
            'status': "TIMEOUT/ERROR",
            'error': str(e)
        }

with open('dashboards_to_check.json', 'r') as f:
    dashboards = json.load(f)

results = []
print(f"Checking {len(dashboards)} dashboards...")
for i, dash in enumerate(dashboards):
    print(f"[{i+1}/{len(dashboards)}] Checking: {dash['title']}...")
    result = check_dashboard(dash['title'], dash['dash_id'])
    results.append(result)
    # Be nice to Google
    time.sleep(0.5)

with open('dashboard_check_results.json', 'w') as f:
    json.dump(results, f, indent=4)

# Create a summary report
summary = {
    'total': len(results),
    'working': len([r for r in results if r.get('status') == 'WORKING']),
    'error_page': len([r for r in results if r.get('status') == 'ERROR_PAGE']),
    'access_denied': len([r for r in results if r.get('status') == 'ACCESS_DENIED']),
    'broken': len([r for r in results if r.get('status') == 'BROKEN']),
    'timeout_error': len([r for r in results if r.get('status') == 'TIMEOUT/ERROR'])
}

print("\n--- SUMMARY ---")
for k, v in summary.items():
    print(f"{k.upper():<15}: {v}")

failed = [r for r in results if r.get('status') != 'WORKING']
if failed:
    print("\n--- FAILED DASHBOARDS ---")
    for r in failed:
        print(f"Title: {r['title']}")
        print(f"ID   : {r['dash_id']}")
        print(f"Msg  : {r.get('status')} ({r.get('status_code', 'N/A')})")
        print("-" * 20)
