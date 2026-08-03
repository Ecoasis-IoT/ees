# EES — Ecoasis Energy System

A multi-site solar energy monitoring and management platform for [Ecoasis Energy](https://ecoasisenergy.com). Aggregates real-time production data from LoRaWAN IoT devices across 7 sites, presents live dashboards, historical reports, and provides a full administrative backend.

---

## Table of Contents

- [Overview](#overview)
- [Project Structure](#project-structure)
- [Sites Monitored](#sites-monitored)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Features](#features)
- [Security](#security)
- [Cron Jobs](#cron-jobs)
- [Webhook Callbacks](#webhook-callbacks)
- [Deployment Checklist](#deployment-checklist)

---

## Overview

EES receives uplink data from ChirpStack LoRaWAN gateways via HTTP callbacks, decodes device payloads (DINRSM meters, UC300 controllers), stores readings in per-site MySQL databases, and surfaces the data through a PHP web application.

**Stack:** PHP 8+, MySQL, Apache, PDO, Bootstrap 5, Chart.js, DataTables, Leaflet

---

## Project Structure

```
EES/
├── .env                        # Environment config (never commit)
├── .env.example                # Template — copy to .env and fill in
├── .htaccess                   # Root Apache config (rewrites, security)
├── config.php                  # Central config loader + PDO factory
│
├── core/                       # Web application
│   ├── login.php               # Login page
│   ├── dashboard.php           # Main dashboard (all sites)
│   ├── site.php                # Site list / management
│   ├── site-dashboard.php      # Per-site live dashboard
│   ├── plant.php               # Plant production report
│   ├── query.php               # Custom data query
│   ├── archive.php             # Archived data viewer
│   ├── notifications.php       # Notification centre
│   ├── profile.php             # User profile + password + 2FA
│   ├── security_dashboard.php  # Admin security dashboard + system config
│   ├── admin-settings.php      # Admin application settings
│   ├── devices.php             # Device management
│   ├── user-management.php     # User list (admin)
│   ├── add-user.php            # Create user (admin)
│   ├── add-site.php            # Create site (admin)
│   ├── edit-site.php           # Edit site (admin)
│   ├── add-energy-meter.php    # Add meter (admin)
│   ├── edit-energy-meter.php   # Edit meter (admin)
│   │
│   ├── common/                 # Shared includes
│   │   ├── auth.php            # Session guard + expiry + regeneration
│   │   ├── authorization.php   # Role helpers (requireAdmin, isAdmin, etc.)
│   │   ├── csrf.php            # CSRF token generation + validation
│   │   ├── security_headers.php# HTTP security headers (CSP, HSTS, etc.)
│   │   ├── security_logging.php# logSecurityEvent() helper
│   │   ├── auth_security.php   # Rate limiting + account lockout
│   │   ├── validation.php      # Input validation helpers
│   │   ├── two_factor_auth.php # TOTP 2FA helpers
│   │   ├── sidebar.php         # Navigation sidebar
│   │   ├── header.php          # Top header + notifications bell
│   │   └── footer.php          # Page footer
│   │
│   └── scripts/                # AJAX / POST endpoint scripts
│       ├── admin/              # Admin-only CRUD endpoints
│       ├── archives/           # Archive data scripts
│       ├── site/               # Per-site card data
│       └── ...                 # All other data + action scripts
│
├── callback/                   # ChirpStack webhook receivers (IoT)
│   ├── shared/bootstrap.php    # Central PDO + webhook verifier
│   ├── bovalon/                # Bovalon site callbacks
│   ├── factory/                # Factory site callbacks
│   ├── gob/                    # GOB site callbacks
│   ├── rtm/                    # Riche Terre Mall callbacks
│   ├── the_pod/                # The Pod callbacks
│   ├── Home_and_Leisure/       # Home & Leisure callbacks
│   ├── phoenix/                # Phoenix Mall callbacks
│   ├── moka_city/              # Moka City callbacks (+ Fire Alarm Panel)
│   └── case_noyal/             # Case Noyal callbacks
│
└── cron/                       # Scheduled CLI scripts
    ├── gateway_status.php      # Polls gateway connectivity
    ├── phoenix_config.php      # Phoenix PDO bootstrap
    └── get_*_energy.php        # Per-site downlink energy triggers
```

---

## Sites Monitored

| Key | Site | Database |
|---|---|---|
| `admin` | Central / Admin | `u889201362_ees_pv` |
| `factory` | Factory | `u889201362_factory` |
| `gob` | GOB | `u889201362_gob` |
| `pod` | The Pod | `u889201362_the_pod` |
| `rtm` | Riche Terre Mall | `u889201362_r_terre_mall` |
| `bovalon` | Bovalon | `u889201362_bovalon_mall` |
| `phoenix` | Phoenix Mall | `u889201362_phoenix_mall` |
| `p_catering` | Phoenix Catering | `u889201362_p_catering` |
| `moka_city` | Moka City | `u889201362_moka_city` |
| `home_leisure` | Home & Leisure | `u889201362_home_leisure` |

---

## Requirements

- PHP 8.0+
- Apache 2.4+ with `mod_rewrite`, `mod_headers` enabled
- MySQL 5.7+ / MariaDB 10.3+
- PHP extensions: `pdo_mysql`, `openssl`, `mbstring`, `json`

---

## Installation

```bash
# 1. Clone / copy files to your Apache document root
#    e.g. C:/Apache24/htdocs/EES  or  /var/www/html/EES

# 2. Copy the environment template
cp .env.example .env

# 3. Fill in your database credentials and SMTP settings in .env

# 4. Point your browser to:
#    http://localhost/EES/core/login.php
#    (or http://localhost/EES/core/ if DirectoryIndex is set)
```

No Composer dependencies — all third-party libraries are bundled in `core/assets/`.

---

## Configuration

All configuration is in `.env` (never committed to version control). Copy `.env.example` to `.env` and fill in the values.

### Required — fill in before first run

```ini
# Admin database
ADMIN_DB_SERVER=localhost
ADMIN_DB_USERNAME=your_db_user
ADMIN_DB_PASSWORD=your_db_password
ADMIN_DB_NAME=your_admin_db

# Per-site databases (repeat for each site)
FACTORY_DB_USERNAME=...
FACTORY_DB_PASSWORD=...
# etc.

# SMTP (for user invitation emails)
SMTP_HOST=smtp.hostinger.com
SMTP_USERNAME=no-reply@yourdomain.com
SMTP_PASSWORD=your_smtp_password
SMTP_FROM_EMAIL=no-reply@yourdomain.com
```

### Key optional settings

| Key | Default | Description |
|---|---|---|
| `ENVIRONMENT` | `development` | Set to `production` to suppress errors and enable CSP |
| `HTTPS_ENABLED` | `false` | Enables secure cookies + HSTS |
| `CSP_ENABLED` | `false` | Enables Content-Security-Policy header |
| `CAPTCHA_ENABLED` | `false` | Enables reCAPTCHA on login |
| `TWO_FACTOR_ENABLED` | `false` | Enables TOTP 2FA for users |
| `WEBHOOK_REQUIRE_SIGNATURE` | `false` | Enables HMAC-SHA256 webhook verification |
| `WEBHOOK_SECRET` | _(empty)_ | ChirpStack signing secret |
| `WEBHOOK_IP_WHITELIST` | _(empty)_ | Comma-separated IPs allowed to POST to callbacks |
| `ACCOUNT_LOCKOUT_ATTEMPTS` | `5` | Failed logins before account lock |
| `SESSION_LIFETIME` | `14400` | Session timeout in seconds (4 hours) |

---

## Features

### Dashboard
- Live production summary table for all sites
- Interactive Leaflet map with site markers
- Per-site drilldown dashboard (power, energy, irradiance, meters)

### Reports
- **Plant** — daily production vs irradiance, performance ratio
- **Query** — per-meter energy query by day / month / year / custom range
- **Archive** — historical bulk data export

### Administration
- User management (create, edit, delete, invite by email)
- Site management (add, edit, delete sites and energy meters)
- Device management
- Admin settings panel

### Security Dashboard _(admin only)_
- Live security stats (failed logins 24h, locked accounts, trend charts)
- Full security log viewer with DataTables (filter by severity, event type, IP, date)
- System configuration UI — 10 tabs to manage all `.env` settings live without restarting
- Security event logging for all admin actions

### User Features
- Profile page — update name/email, change password
- Two-Factor Authentication (TOTP) — setup, verify, disable
- Notifications centre — paginated, filterable, mark-read / mark-all-read

---

## Security

### What is implemented

| Area | Implementation |
|---|---|
| SQL Injection | 100% PDO prepared statements — zero string interpolation |
| XSS | All output through `htmlspecialchars(ENT_QUOTES)` |
| CSRF | `bin2hex(random_bytes(32))` token, `hash_equals()` validation, auto-rotated |
| Session | `SameSite=Strict`, `HttpOnly`, `use_strict_mode`, ID regenerated every 30 min |
| Brute force | IP rate-limit + per-account lockout (configurable via `.env`) |
| Password storage | `password_hash()` bcrypt — no MD5 or SHA1 |
| HTTP headers | CSP, HSTS, `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy` |
| Sensitive files | `.env`, `config.php`, `.sql`, `.log`, `.sh` denied in `.htaccess` |
| AJAX endpoints | `X-Requested-With: XMLHttpRequest` enforced on all JSON endpoints |
| Error display | `display_errors=off` in production; all errors go to server log only |
| Audit trail | `logSecurityEvent()` records login failures, CSRF failures, rate limits, admin actions to `tbl_security_logs` |

### Enabling HTTPS (production)

```ini
# .env
HTTPS_ENABLED=true
HSTS_ENABLED=true
CSP_ENABLED=true
```

Then uncomment the HTTPS redirect block in `.htaccess`:
```apache
# RewriteCond %{HTTPS} !=on
# RewriteRule ^.*$ https://%{SERVER_NAME}%{REQUEST_URI} [R=301,L]
```

### Enabling webhook signature verification

Once you have your ChirpStack HTTP Integration signing key:
```ini
# .env
WEBHOOK_SECRET=your_chirpstack_signing_secret
WEBHOOK_IP_WHITELIST=192.168.1.100,10.0.0.5   # optional
WEBHOOK_REQUIRE_SIGNATURE=true
```

---

## Cron Jobs

The following scripts should be scheduled via cron (Linux) or Task Scheduler (Windows):

| Script | Schedule | Purpose |
|---|---|---|
| `cron/gateway_status.php` | Every 5 min | Polls gateway connectivity, writes `cron/network_status.txt` |
| `cron/get_*_energy.php` | Daily ~04:50 | Sends downlink command to UC300 devices to trigger energy reading |
| `cron/DINRSMSetInterval.php` | Daily ~03:59 | Sets DINRSM controller reporting interval |
| `cron/restart_DINRSM.php` | Daily ~01:00 | Restarts DINRSM controllers |

Example Linux crontab:
```cron
*/5 * * * *  php /var/www/html/EES/cron/gateway_status.php
50  4 * * *  php /var/www/html/EES/cron/get_bovalon_energy.php
59  3 * * *  php /var/www/html/EES/cron/DINRSMSetInterval.php
0   1 * * *  php /var/www/html/EES/cron/restart_DINRSM.php
```

---

## Webhook Callbacks

Each site has a callback endpoint that receives ChirpStack uplink POST payloads:

```
POST /EES/callback/bovalon/
POST /EES/callback/factory/
POST /EES/callback/gob/
POST /EES/callback/rtm/
POST /EES/callback/the_pod/
POST /EES/callback/Home_and_Leisure/
POST /EES/callback/phoenix/
POST /EES/callback/moka_city/
POST /EES/callback/case_noyal/
```

Each callback:
1. Optionally verifies the request IP and HMAC-SHA256 signature (controlled by `.env`)
2. Decodes the device payload based on `fPort`
3. Stores the raw uplink in `tbl_data`
4. Calls the appropriate decoder (`dinrsm_decoder.php`, `uc300_decoder.php`)
5. Stores processed readings in `tbl_sub_meters`, `tbl_hourly_prod`, `plant_irradiance`

---

## Deployment Checklist

- [ ] Copy `.env.example` → `.env` and fill in all credentials
- [ ] Set `ENVIRONMENT=production` and `DISPLAY_ERRORS=false`
- [ ] Set `CSP_ENABLED=true`
- [ ] Obtain and configure SSL certificate
- [ ] Set `HTTPS_ENABLED=true`, `HSTS_ENABLED=true`
- [ ] Uncomment HTTPS redirect in `.htaccess`
- [ ] Set `SMTP_*` values and test user invitation email
- [ ] Register cron jobs on the server
- [ ] Set `WEBHOOK_SECRET` from ChirpStack and flip `WEBHOOK_REQUIRE_SIGNATURE=true`
- [ ] Optionally enable `CAPTCHA_ENABLED=true` (requires reCAPTCHA keys)
- [ ] Optionally enable `TWO_FACTOR_ENABLED=true`
- [ ] Verify `.env` is not accessible via browser (`curl https://yourdomain.com/EES/.env` should return 403)
