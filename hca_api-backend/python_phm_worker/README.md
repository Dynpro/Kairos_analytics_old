# Python PHM Worker

This folder contains the Python-side PHM generation worker.

Current scope:
- Connects to the same MySQL database as Lumen using `hca_api-backend/.env`
- Delegates PHM generation to the existing PHP cron commands
- Uses Python as a scheduler/poller for the backend job pipeline
- Leaves request creation, auth, and report API behavior in PHP

The worker is intentionally scoped so PHP continues to own:
- request creation
- auth and permissions
- report listing / retry / delete / download APIs
- PDF rendering and storage

Python owns the background cron execution pipeline for PHM generation.

## Commands

Install dependencies:

```bash
pip install -r python_phm_worker/requirements.txt
```

Verify the database connection and active driver:

```bash
python python_phm_worker/worker.py doctor
```

Process a single pending PHM report:

```bash
python python_phm_worker/worker.py once
```

Run continuously:

```bash
python python_phm_worker/worker.py daemon
```

## Important

The worker scaffold is connected, but two parts still need to be ported from PHP before production use:

1. `app/Console/Commands/PHMDataCron.php`
2. `app/Http/Controllers/GenerateReportController.php::generate_pdf`

Until those are ported, the worker will claim a report, prepare the report shell, then mark the report as failed with `looks_generated = 7` and log the reason.
