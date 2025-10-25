# LIFEFI Backend

This repository hosts the official backend service for the LIFEFI platform. It exposes the core APIs that power the LIFEFI frontend experience and the ecosystem of wearable plugins. The application is built with ThinkPHP 6 and follows a light, modular structure that suits small devices and rapid iteration.

## Ecosystem
- `Backend`: this repository (ThinkPHP 6 service that powers the platform APIs)
- `Frontend`: https://github.com/LIFEFI-X/Frontend
- `Plugin`: https://github.com/LIFEFI-X/Plugin
- `Production`: https://www.lifefi.io/

## Features
- Unified REST-style API responses with consistent envelopes and error handling
- Multi-application support through ThinkPHP native multi-app architecture
- Database migrations and seed SQL provided for rapid environment provisioning
- Centralized configuration with environment overrides for deployment flexibility
- Extendable service layer that encourages reusable business logic in `extend/`

## Requirements
- PHP 7.3 or newer (tested on PHP 8.1)
- Composer
- MySQL 5.7+ (or compatible) for the default SQL schema in `sql.sql`
- Web server (Apache, Nginx, or PHP built-in server) configured for ThinkPHP public entry point

## Quick Start
```bash
# install dependencies
composer install

# copy default environment file if needed
cp .env.example .env

# adjust database credentials in .env

# import initial schema
mysql -u <user> -p <database> < sql.sql

# serve locally (built-in PHP server example)
php -S 0.0.0.0:8000 -t public
```
Visit `http://localhost:8000` to verify the application is running.

## Configuration Highlights
- `config/app.php`: global application settings
- `config/database.php`: database drivers, connections, and SQL debug switches
- `.env`: per-environment overrides; keep sensitive values out of version control

When running in production, disable debug mode and rely on log files for error traces. Avoid manual `die`/`exit` calls because they can prevent log writers from flushing.

## Project Layout
- `app/`: controllers, services, middleware, and validators for each module
- `extend/`: reusable helpers and service abstractions that are shared across modules
- `public/`: ThinkPHP front controller (`index.php`) and web-accessible assets
- `route/`: HTTP route definitions
- `sql.sql`: reference schema and seed data
- `vendor/`: Composer-managed dependencies

## Development Workflow
1. Create feature branches from the main line of development.
2. Run automated tests or linting scripts (add them under `vendor/bin/` or custom Composer scripts).
3. Keep API responses aligned with the unified formatter shipped in this codebase.
4. Update API documentation and notify frontend/plugin teams when contracts change.

## Deployment Notes
- Generate optimized autoload files with `composer install --optimize-autoloader --no-dev` for production.
- Point the web server document root to `public/` and enable URL rewriting to route all requests through `public/index.php`.
- Ensure file and cache permissions are writable by the web server user where required (e.g., `runtime/`).

## License

This project is released under the terms of the [Apache License 2.0](LICENSE).
