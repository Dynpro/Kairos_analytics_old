# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Contributing

Thank you for considering contributing to Lumen! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## PHM Python Worker

This codebase now supports a split PHM flow:

- PHP/Lumen keeps ownership of PHM request creation, auth, report listing, retry, delete, and download APIs.
- Python can own only the PHM background generation pipeline.

### Driver switch

Set `PHM_GENERATOR_DRIVER` in `.env`:

- `php`: existing PHP PHM jobs stay active
- `python`: PHP PHM schedulers/manual PHM generation endpoints are disabled so the Python worker can own the job

Example environment entries:

```env
PHM_GENERATOR_DRIVER=python
PYTHON_PHM_WORKER_POLL_SECONDS=15
PYTHON_PHM_WORKER_BATCH_SIZE=1
PYTHON_PHM_OUTPUT_DIR=storage/app/python-phm
PYTHON_PHM_LOG_FILE=storage/logs/python-phm-worker.log
```

### Python worker

Worker files live in [python_phm_worker](./python_phm_worker).

Quick start:

```bash
pip install -r python_phm_worker/requirements.txt
python python_phm_worker/worker.py doctor
python python_phm_worker/worker.py daemon
```

The current scaffold is wired to the shared database and claims pending PHM reports, but the Snowflake/chart and final PDF parity logic still need to be ported from the existing PHP generator classes before production cutover.
