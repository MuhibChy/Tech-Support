# PerfectITSecurity

> Enterprise-grade international IT support and cybersecurity platform built for modern business operations, client management, and mission-critical workflows.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Key Features](#key-features)
  - [Client Portal](#client-portal)
  - [Staff & Administration Workspace](#staff--administration-workspace)
  - [Service Management & Quoting](#service-management--quoting)
  - [IT Ticketing & SLA Escalations](#it-ticketing--sla-escalations)
  - [Billing, Invoicing & Stripe Integration](#billing-invoicing--stripe-integration)
  - [Backup & Disaster Recovery](#backup--disaster-recovery)
  - [Enterprise Security & MFA](#enterprise-security--mfa)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Quick Start / Local Setup](#quick-start--local-setup)
  - [1. Clone the Repository](#1-clone-the-repository)
  - [2. Install Backend Dependencies](#2-install-backend-dependencies)
  - [3. Configure Environment Variables](#3-configure-environment-variables)
  - [4. Generate Application Key](#4-generate-application-key)
  - [5. Setup the Database](#5-setup-the-database)
  - [6. Install & Build Frontend Assets](#6-install--build-frontend-assets)
  - [7. Link Storage](#7-link-storage)
  - [8. Run the Development Server](#8-run-the-development-server)
- [Windows One-Click Launcher](#windows-one-click-launcher)
- [Testing](#testing)
- [Environment Variables Guide](#environment-variables-guide)
- [Production Deployment](#production-deployment)
- [Security Notes](#security-notes)
- [Project Structure](#project-structure)
- [License](#license)

---

## Project Overview

**PerfectITSecurity** is a full-featured, secure web application designed to serve as an end-to-end digital operations platform for cybersecurity consultancies, managed IT service providers (MSPs), and IT support firms.

The platform provides a public-facing website with interactive 3D elements, a self-service customer portal with role-based access, and an extensive administrative back-office system covering ticketing, SLA tracking, sales pipeline management, recurring billing, automated backup orchestration, and operational health monitoring.

---

## Key Features

### Client Portal
- **Dashboard & Overview**: Real-time visibility into open tickets, ongoing IT projects, active service contracts, and unpaid invoices.
- **Service Request & Quotations**: Interactive quotation review, digital acceptance/rejection, and price negotiation workflows.
- **Document Management**: Secure document upload, download, and storage with strict per-tenant isolation.
- **Phone & Email Verification**: Multi-factor identity validation with OTP generation, rate limiting, and resend cooldowns.

### Staff & Administration Workspace
- **Role-Based Access Control (RBAC)**: Fine-grained permissions separating Customers, Support Agents, Technical Staff, and Super Admins.
- **Service Request Pipeline**: Visual stage transitions from lead generation to technical quotation and confirmed work order.
- **Manual Work Orders**: Ability for technical staff to generate custom service orders and customer profiles on the fly.
- **Operational Health Center**: Real-time system health checks, database status, queue monitoring, and error tracking.

### Service Management & Quoting
- **Dynamic Service Catalog**: Categorized cybersecurity offerings, incident response packages, and managed IT services with SEO-friendly unique slugs.
- **PDF Generation**: Automatic quotation and invoice PDF rendering with itemized tax, discount breakdowns, and terms of service.

### IT Ticketing & SLA Escalations
- **Ticketing Lifecycle**: Threaded support conversations, internal staff notes, priority levels, and file attachments.
- **Automated SLA Monitoring**: Background scheduler checking response deadlines with escalation notifications for critical incidents.
- **Ticket Merging & Time Tracking**: Staff utilities for combining related issues and logging billable technical hours.

### Billing, Invoicing & Stripe Integration
- **Financial Ledger**: Accurate revenue tracking, debit/credit entries, profit and loss calculations, and expense categorization.
- **Payment Processing**: Seamless Stripe Checkout integration with webhook processing for verified transactions and receipts.
- **Recurring Subscriptions**: Recurring billing management for continuous IT retainers and monitoring contracts.

### Backup & Disaster Recovery
- **Multi-Level Backups**: Automated database and full-file backup orchestration with configurable cron schedules.
- **AES-256 Encryption**: Client-side encryption for backup archives using isolated cryptographic keys before disk write.
- **Integrity & Verification**: Automatic round-trip tamper detection and isolated test restoration.

### Enterprise Security & MFA
- **Time-based OTP (TOTP)**: Staff MFA enrollment with QR code provisioning and secure challenge verification.
- **Content Security Policy (CSP)**: Robust security headers restricting script injection and unauthorized resource loading.
- **Rate Limiting & Brute-Force Defense**: Route-level throttle limits on authentication, password reset, and verification endpoints.

---

## Technology Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | Laravel 9.52.x (PHP 8.0+) |
| **Frontend Runtime** | Alpine.js 3.16.x |
| **Styling & Design** | Tailwind CSS 3.4.x, PostCSS, Autoprefixer |
| **Interactive Visuals**| Three.js 0.162.x (WebGL hero background & role themes) |
| **Build Tooling** | Vite 4.5.x & `laravel-vite-plugin` |
| **Database** | SQLite (development/testing) / MySQL 8.0+ (production) |
| **PDF Engine** | DomPDF (`barryvdh/laravel-dompdf`) |
| **Payment Gateway** | Stripe PHP SDK |
| **Communication** | Twilio SDK (Phone OTP / SMS), Laravel Mail (SMTP/Log) |
| **Testing** | PHPUnit 9.5.x |

---

## System Requirements

- **PHP**: `^8.0.2` (Extensions required: `pdo_sqlite`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `tokenizer`, `bcmath`, `curl`)
- **Composer**: `2.x`
- **Node.js**: `18.x` or `20.x` LTS
- **npm**: `9.x`+
- **Database**: SQLite 3 or MySQL 8.0+

---

## Quick Start / Local Setup

Follow these steps to set up the project locally in your development environment:

### 1. Clone the Repository
```bash
git clone <GITHUB_REPOSITORY_URL>
cd perfectitsecurity
```

### 2. Install Backend Dependencies
```bash
composer install
```

### 3. Configure Environment Variables
Create your local environment file from the provided example template:
```bash
# On Linux / macOS:
cp .env.example .env

# On Windows (Command Prompt):
copy .env.example .env
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Setup the Database
For a quick local setup using SQLite:
```bash
# Ensure the SQLite database file exists (if using SQLite)
touch database/database.sqlite

# Run all migrations
php artisan migrate
```

To populate the database with complete demo data and test accounts:
```bash
php artisan db:seed
```

> **Demo Credentials**:
> - **Administrator**: `admin@techsupport.com` / `password`
> - **Customer**: `alice@example.com` / `password`
> *(For development use only)*

### 6. Install & Build Frontend Assets
```bash
npm install
npm run build
```

For hot-module replacement during active frontend development:
```bash
npm run dev
```

### 7. Link Storage
Ensure uploaded documents and attachments are correctly linked:
```bash
php artisan storage:link
```

### 8. Run the Development Server
```bash
php artisan serve
```

The application will be accessible at: **`http://127.0.0.1:8000`**

---

## Windows One-Click Launcher

On Windows environments, you can also launch the full development environment automatically by double-clicking `run-local.bat` or running:

```bat
run-local.bat
```

This script automatically verifies prerequisites, installs locked dependencies, initializes `.env` if missing, runs outstanding migrations, compiles assets, and launches the server.

---

## Testing

The project includes an extensive suite of automated feature and unit tests:

```bash
# Run the entire test suite
php artisan test

# Run tests in parallel
php artisan test --parallel

# Run a specific test class
php artisan test tests/Feature/AuthenticationTest.php
```

All 199 tests validate critical business paths, payment workflows, security authorization boundaries, and PDF generation.

---

## Environment Variables Guide

Key configuration keys defined in `.env.example`:

| Key | Purpose | Default / Example |
|---|---|---|
| `APP_NAME` | Application title | `PerfectITSecurity` |
| `APP_ENV` | Environment stage | `local` / `production` |
| `APP_KEY` | 32-character AES encryption key | Generated via `php artisan key:generate` |
| `APP_DEBUG` | Debug stack traces (set `false` in prod) | `true` |
| `APP_URL` | Canonical application base URL | `http://127.0.0.1:8000` |
| `DB_CONNECTION` | Database driver | `sqlite` or `mysql` |
| `DB_DATABASE` | Database path or name | `database/database.sqlite` |
| `MAIL_MAILER` | Mail driver | `log` (local) or `smtp` (production) |
| `STRIPE_KEY` | Stripe publishable key | `pk_test_...` |
| `STRIPE_SECRET` | Stripe secret API key | `sk_test_...` |
| `STRIPE_WEBHOOK_SECRET` | Stripe webhook verification secret | `whsec_...` |
| `BACKUP_ENCRYPTION_KEY` | 32-byte key for backup archive encryption | Base64 encoded key |
| `AI_PROVIDER` | AI assistant provider | `openai` |
| `AI_API_KEY` | OpenAI API secret key | `sk-proj-...` |

---

## Production Deployment

Before deploying to a public/production server:

1. **Set Production Environment**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   ```
2. **Switch Database**:
   Configure `DB_CONNECTION=mysql` with dedicated database credentials.
3. **Optimize Caching**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. **Compile Production Assets**:
   ```bash
   npm ci
   npm run build
   ```
5. **Configure Process Monitors**:
   Run queue workers and the scheduler via Supervisor and system crontab:
   ```cron
   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
   ```

---

## Security Notes

- **Zero Credentials in Version Control**: `.env` and sensitive local files are strictly excluded from source control.
- **CSRF & Security Headers**: All forms enforce CSRF verification; production responses include HTTP security headers (CSP, X-Frame-Options, X-Content-Type-Options).
- **Tenant Isolation**: Customer data access is strictly gated through authorization policies preventing IDOR vulnerabilities.
- **Encrypted Backups**: Backups stored on disk or transferred to cloud storage (S3) are encrypted using AES-256-CBC.

---

## Project Structure

```text
├── app/
│   ├── Http/Controllers/    # Public, Customer Portal, and Staff/Admin Controllers
│   ├── Http/Middleware/     # Security, RBAC, and Localization middleware
│   ├── Models/              # Eloquent models (Users, Tickets, Invoices, Orders, etc.)
│   ├── Policies/            # Authorization policies
│   └── Services/            # Core business services (Payments, Backups, SLA, Health)
├── config/                  # Configuration files
├── database/
│   ├── factories/           # Model factories for testing
│   ├── migrations/          # Database schema migrations
│   └── seeders/             # Database seeders (Production and Demo datasets)
├── docs/                    # Architectural and disaster recovery documentation
├── public/                  # Web server document root (Vite assets, index.php)
├── resources/
│   ├── css/                 # Tailwind stylesheet sources
│   ├── js/                  # Alpine.js and Three.js visual scripts
│   └── views/               # Blade templates (layouts, customer, admin, public)
├── routes/
│   ├── web.php              # Web application routes
│   └── api.php              # API endpoints
├── storage/                 # Logs, framework cache, encrypted backups
├── tests/                   # Automated PHPUnit feature and unit tests
├── .env.example             # Safe environment variable blueprint
└── vite.config.js           # Vite asset bundler configuration
```

---

## License

This software is proprietary. All rights reserved.
