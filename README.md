# Franchise Management System - Enterprise Multi-Outlet Management Platform

**Franchise Management System** is a production-grade, enterprise web application engineered to oversee and orchestrate multiple franchise outlets and company-owned branches under a unified brand. Built on the modern architecture of **Laravel 13** and architected for **PostgreSQL 18**, this system delivers centralized brand governance, store-level POS transaction handling, automated franchise royalty billing, supply chain warehouse fulfillment, quality audit scorecards, and a robust **Admin Bulk Data Import Engine**.

The frontend is powered by **Bootstrap 5.3**, **Bootstrap Icons**, and **Chart.js**—all **100% locally downloaded and bundled** into the project folder (`public/vendor/`). This ensures full operational capability in **internal enterprise intranets, air-gapped environments, and POS store networks without requiring internet access or third-party CDNs**.

---

## 🌟 Key Highlights

- **Framework & Backend:** Laravel 13.x running on PHP 8.3+ with strict type declarations.
- **Database Engine:** PostgreSQL 18 compatible schema utilizing `jsonb`, numeric monetary precision, foreign key cascade rules, and high-performance indexing.
- **Offline / Local Intranet Guarantee:** Bootstrap 5.3 CSS/JS, Bootstrap Icons (including offline WOFF/WOFF2 font binaries), and Chart.js are stored directly within `public/vendor/`. No CDN or external Google Fonts are referenced.
- **Multi-Outlet Hierarchy:** Brand Headquarters &rarr; Regional Districts &rarr; Outlets (Franchise, Company-Owned, Kiosk, Express).
- **Role-Based Access Control (RBAC):**
  - `super_admin`: Corporate Brand HQ executive with global visibility, bulk import hub, royalty run issuance, and store provisioning.
  - `franchise_owner`: Licensee operator managing one or multiple franchise stores, reviewing sales, supply requisitions, and royalty invoices.
  - `outlet_manager`: Day-to-day store general manager overseeing local shifts, inventory levels, stock adjustments, and staff.
  - `staff`: Frontline cashier and barista operating the POS terminal and recording orders.
- **Admin Bulk Data Import Engine:** Multi-entity CSV batch importer for Outlets, Products, Inventory Stocks, Staff Credentials, and Sales Transactions with downloadable templates, delimiter auto-detection, and row-level diagnostic error logs.
- **Automated Royalty & Billing Engine:** Monthly automated assessment of franchise royalties (5% gross sales), national marketing fund contributions (2%), and portal fees with ACH/wire reconciliation.
- **Central Supply Chain Pipeline:** Store-to-HQ warehouse requisition workflow (Requested &rarr; Approved &rarr; Dispatched &rarr; Received with automated stock increments).
- **Standards & Inspection Audits:** Digital audit scorecards for food safety, hygiene, brand compliance, and mystery shopper evaluations.

---

## 🏗️ Architecture & Module Overview

```
Franchise Management System
├── Corporate Brand HQ (Global Settings, Royalty Rates, Central Menu)
├── Regional Territories (Metro Core, Coastal Pacific, Airport Concessions)
├── Store Outlets
│   ├── Outlet Profile & Tax Configuration
│   ├── Staff & POS Terminals
│   ├── Local Inventory Stock & Reorder Thresholds
│   ├── Supply Requisitions from HQ Warehouse
│   └── Brand Standards Audits
├── Commercial Engine
│   ├── Point-of-Sale (POS) Register & Thermal Receipts
│   ├── Automated Stock Depletion
│   └── Monthly Royalty Invoicing & ACH Settlement
└── Admin Tools
    ├── Bulk CSV Importer (5 Entity Models)
    └── Audit Trail & Discrepancy Logs
```

---

## 🚀 Getting Started with Laragon & PostgreSQL 18

The application is structured to run directly in Windows with Laragon and PostgreSQL 18.

### 1. Prerequisites

- **PHP 8.3 or 8.4** (available in `C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64` or your Laragon PHP selector)
- **Composer 2.x**
- **PostgreSQL 18** (or PostgreSQL 15/16/17/18 via Laragon Services or standalone installer)
- `pdo_pgsql` and `pgsql` PHP extensions enabled in `php.ini`

### 2. Configure Environment (`.env`)

Verify your `.env` configuration for PostgreSQL 18:

```env
APP_NAME="Franchise Management System"
APP_ENV=local
APP_KEY=base64:VQLbB3HgRU9G0cDLdwm0fjU3pjJA2v9a+eGO6gT0+gw=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=franchise_db
DB_USERNAME=postgres
DB_PASSWORD=postgres
DB_SCHEMA=public
DB_SSLMODE=prefer
```

### 3. Create the Database in PostgreSQL

Using `psql` or HeidiSQL / pgAdmin:

```sql
CREATE DATABASE franchise_db;
```

### 4. Execute Migrations & Enterprise Demo Seeder

Run the following Artisan command to generate the database schema and populate comprehensive demo data:

```bash
php artisan migrate:fresh --seed
```

### 5. Launch the Web Application

Start the local web server:

```bash
php artisan serve
```

The application will be accessible at:
```
http://localhost:8000
```

---

## 👥 Pre-Configured Demo Credentials

The database seeder provisions 4 role-based user accounts for immediate testing:

| Role Level | Email | Password | Permissions & Scope |
| :--- | :--- | :--- | :--- |
| **Corporate Super Admin** | `admin@franchise.local` | `Password123!` | Full HQ access, all stores, bulk import hub, royalty run issuance |
| **Franchise Licensee** | `owner@franchise.local` | `Password123!` | Multi-store owner view, royalty invoices, supply orders |
| **Store General Manager** | `manager@franchise.local` | `Password123!` | Downtown Flagship store, stock adjustments, cashier roster |
| **Store Cashier / Barista** | `staff@franchise.local` | `Password123!` | POS register, sales entry, receipt generation |

*Note: The login page at `/login` provides 1-click credential filler buttons for rapid switching between roles.*

---

## 📥 Admin Bulk Data Import Engine

Accessible at `/admin/bulk-imports` for Super Admins. The system features dedicated CSV import parsers with downloadable templates:

### 1. Outlets Import (`outlets`)
- **Route:** `POST /admin/bulk-imports` with `type=outlets`
- **Template Route:** `GET /admin/bulk-imports/template/outlets`
- **Header Structure:**
  ```csv
  name,outlet_code,region_code,franchisee_name,type,status,phone,email,address,city,tax_rate_percentage
  "PrimeBrew Metro Downtown","FRAN-MET-001","METRO","Corporate / HQ","company_owned","active","+1-555-0101","downtown@franchise.local","100 Market St","Metro City",10.00
  ```

### 2. Products / Menu Catalog Import (`products`)
- **Header Structure:**
  ```csv
  sku,barcode,name,category_slug,unit_of_measure,cost_price,suggested_retail_price,minimum_selling_price,is_track_inventory
  "PRD-BEV-001","899100101","Caramel Cloud Latte","handcrafted-coffee","cup",1.45,5.50,4.50,1
  ```

### 3. Inventory Stock Levels Import (`inventory`)
- **Header Structure:**
  ```csv
  outlet_code,sku,quantity_on_hand,reorder_threshold,target_stock_level
  "FRAN-MET-001","PRD-BEV-001",120.00,25.00,150.00
  ```

### 4. Staff & Credentials Import (`staff`)
- **Header Structure:**
  ```csv
  name,email,role,outlet_code,phone,password
  "Elena Rostova","elena.rostova@franchise.local","staff","FRAN-MET-001","+1-555-0202","Password123!"
  ```

### 5. Sales Transactions Import (`sales`)
- **Header Structure:**
  ```csv
  outlet_code,transaction_code,cashier_email,customer_name,customer_phone,subtotal,discount_amount,tax_amount,total_amount,payment_method,payment_status,transaction_date
  "FRAN-MET-001","TRX-MET-202610-001","marcus.vance@franchise.local","Jane Doe","+1-555-9001",14.20,0.00,1.42,15.62,"credit_card","paid","2026-10-12 10:15:00"
  ```

### Bulk Import Engine Features:
- **Encoding & BOM Handling:** Automatically detects and strips UTF-8 BOM headers.
- **Delimiter Detection:** Handles standard comma `,` as well as semicolon `;` delimited CSVs.
- **Transactional Rollback:** Database transactions isolate rows; failing rows are captured in a structured `jsonb` error audit log displaying the exact row number, error description, and offending payload.
- **Downloadable Sample CSVs:** Every module has a downloadable starter template pre-populated with compliant header columns and sample rows.

---

## 📦 Offline Asset Architecture

The project requires **zero internet access** during operation. Assets are stored in `public/vendor/`:

```
public/vendor/
├── bootstrap/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── bootstrap.min.css.map
│   └── js/
│       ├── bootstrap.bundle.min.js
│       └── bootstrap.bundle.min.js.map
├── bootstrap-icons/
│   ├── bootstrap-icons.min.css
│   └── fonts/
│       ├── bootstrap-icons.woff
│       └── bootstrap-icons.woff2
└── chartjs/
    └── chart.umd.min.js
```

All blade views reference these assets locally via `{{ asset('vendor/...') }}`.

---

## 🧪 Testing Suite & Quality Assurance

The Franchise Management System includes automated unit and feature tests covering authentication, dashboard analytics, POS inventory depletion, bulk imports, and royalty calculations.

To execute the test suite (configured with SQLite in-memory for zero external database dependency):

```bash
vendor/bin/phpunit
```

To format code using Laravel Pint:

```bash
vendor/bin/pint
```

Current test coverage output:
```
Tests: 17 passed (45 assertions)
Duration: 1.05s
```

---

## 🔒 Security & Production Checklist

1. **Production Environment:** Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
2. **PostgreSQL Configuration:** Enable SSL mode (`DB_SSLMODE=require`) when connecting over external networks.
3. **Queue Processing:** For high-volume CSV uploads (>100,000 rows), configure Laravel queue worker:
   ```bash
   php artisan queue:work --timeout=300
   ```
4. **Task Scheduling:** Add Laravel's scheduler to Windows Task Scheduler or cron:
   ```bash
   php artisan schedule:run
   ```
