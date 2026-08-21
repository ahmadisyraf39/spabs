# SPABS — Sistem Pengurusan Akademi Bola Sepak

**SPABS** (Football Academy Management System) is a PHP web application for managing a football/soccer academy's day-to-day operations — players, coaches, training modules, attendance, skill progress, fees, tournaments, and media galleries. It supports three role-based portals: **Admin**, **Coach**, and **Parent**.

## Features

By role:

- **Admin** — manage user and player accounts, create/edit training activities and modules, set skill attributes, manage fees and view fee payments, publish announcements, manage photo/video galleries and albums, track tournaments, and view dashboard charts (user roles, player categories, etc.).
- **Coach** — view assigned activities and modules, take and record player attendance, review/approve absence requests, select players for activities, evaluate and record player skill progress, manage team photo/video galleries, and view team/player profiles.
- **Parent** — view their child's activity schedule, attendance record, and skill/module progress, submit leave (absence) requests, view and pay outstanding fees online via Stripe, view invoices, browse the gallery, and manage their profile.

Shared functionality includes secure login with role-based redirects, session handling, and an admin/coach/parent-specific sidebar navigation.

## Tech stack

- **Backend:** PHP (procedural, PDO for MySQL)
- **Database:** MySQL
- **Frontend:** HTML, CSS (Bootstrap), vanilla JavaScript, DataTables
- **Payments:** [Stripe PHP SDK](https://github.com/stripe/stripe-php) (Checkout Sessions)
- **Dependency management:** Composer

## Project structure

```
spabs/
├── admin/              Admin portal pages (users, players, activities, fees, gallery, announcements...)
├── coach/               Coach portal pages (attendance, evaluations, selections, gallery...)
├── parent/              Parent portal pages (attendance, progress, fee payment, leave requests...)
├── css/                 Stylesheets (Bootstrap, DataTables, custom)
├── js/                  Client-side scripts (jQuery, DataTables, custom)
├── pictures/             Static images, player photos, gallery media
├── vendor/               Composer dependencies (incl. Stripe SDK)
├── database.php         Database connection credentials (git-ignored, copy from database.example.php)
├── db.php                Database connection used by login (git-ignored, copy from db.example.php)
├── database.example.php  Template for database.php
├── db.example.php        Template for db.php
├── session.php           Session bootstrap + logged-in user lookup
├── login.php              Login page and auth logic
├── login_success.php      Post-login role-based redirect
├── logout.php             Session termination
├── index.html            Landing page
├── composer.json / composer.lock   PHP dependencies
└── rujukanModulKemahiran.pdf   Reference document for skill modules
```

## Getting started

### Prerequisites

- PHP 7.4+ with the `pdo_mysql` extension enabled (the bundled Stripe SDK requires PHP >= 5.6, but 7.4+ is recommended for compatibility with modern tooling)
- MySQL (or MariaDB)
- [Composer](https://getcomposer.org/)
- A local PHP server such as XAMPP/MAMP/WAMP, or the PHP built-in server

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/ahmadisyraf39/spabs.git
   cd spabs
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Create a MySQL database and import your schema (see **Database** below), then copy the example config files and fill in your own connection details:
   ```bash
   cp database.example.php database.php
   cp db.example.php db.php
   ```
   Both files are git-ignored, so your credentials stay out of source control.
4. Set your Stripe secret key in `parent/payment.php` (replace the `YOUR_STRIPE_TEST_KEY` placeholder with your own test/live key from the [Stripe Dashboard](https://dashboard.stripe.com/apikeys)).
5. Serve the app, e.g. with the PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
   or place the project in your local server's document root (e.g. `htdocs` for XAMPP) and browse to it.
6. Open `login.php` (or `index.html`) in your browser and log in with an account seeded in the `tbl_spabs_akaun` table.

### Database

The app expects a MySQL database with tables prefixed `tbl_spabs_`, referenced throughout the codebase, including:

| Table                                   | Purpose                                     |
| --------------------------------------- | ------------------------------------------- |
| `tbl_spabs_akaun`                       | User accounts / login credentials and roles |
| `tbl_spabs_user`, `tbl_spabs_pentadbir` | User and admin profile details              |
| `tbl_spabs_jurulatih`                   | Coach profiles                              |
| `tbl_spabs_ibubapa`                     | Parent profiles                             |
| `tbl_spabs_pemain`                      | Player profiles                             |
| `tbl_spabs_aktiviti`                    | Training activities                         |
| `tbl_spabs_modul`                       | Training modules                            |
| `tbl_spabs_kemahiran`                   | Skills / skill attributes                   |
| `tbl_spabs_penilaian`                   | Player skill progress evaluations           |
| `tbl_spabs_kehadiran`                   | Attendance records                          |
| `tbl_spabs_ketidakhadiran`              | Absence/leave requests                      |
| `tbl_spabs_pemilihan`                   | Player selection for activities             |
| `tbl_spabs_yuran`                       | Fees                                        |
| `tbl_spabs_bayaran`                     | Fee payments                                |
| `tbl_spabs_kejohanan`                   | Tournaments                                 |
| `tbl_spabs_pengumuman`                  | Announcements                               |
| `tbl_spabs_album`, `tbl_spabs_media`    | Gallery albums and media                    |

> This repository does not currently include a SQL schema/seed file. You'll need to create these tables manually (matching the columns referenced in the corresponding `*_crud.php` files) or export one from an existing working instance.

## Security notes

This project was archived as a completed academic/legacy build, so a few things are worth fixing before any real deployment:

- `database.php` and `db.php` are now git-ignored (see `database.example.php` / `db.example.php` for the expected structure). The credentials that were previously hardcoded and committed here have been scrubbed from git history — make sure the corresponding database password has also been rotated on the server, since it was exposed in the repo before this fix.
- `parent/payment.php` uses a placeholder Stripe key (`YOUR_STRIPE_TEST_KEY`) — replace with your own key, ideally loaded from an environment variable rather than hardcoded.
- Some queries interpolate variables directly into SQL strings rather than using prepared-statement bindings; review these for SQL injection risk before production use.
