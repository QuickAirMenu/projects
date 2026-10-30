# iProjects Architecture

## Overview
iProjects is a Single-Tenant multi-instance ERP system built on Laravel 10.
Each client gets a separate instance with its own database, storage, and subdomain.

## Stack
- Backend: Laravel 10 (PHP 8.2)
- Frontend: Blade + Tailwind CSS + Alpine.js + Vite
- Database: MySQL 8
- Auth: Laravel Sanctum + Spatie Permissions
- Hosting: Hostinger Shared (current) → VPS (future)

## Instance Map
| Instance | Subdomain | Database | Path |
|----------|-----------|----------|------|
| AirMenu | airmenu.i-projects.net | ip_am | /domains/i-projects.net/iprojects_app |
| TopAccounts | topaccounts.i-projects.net | ip_TopAcc | /domains/i-projects.net/topaccounts_app |
| Demo | app.i-projects.net | ip_demo_app | /domains/i-projects.net/demo_app |
| Staging | stg-airmenu.i-projects.net | ip_am_stg | /domains/i-projects.net/iprojects_stg_app |

## Symlink Structure
public_html/[subdomain] → ../[app_dir]/public

## Deploy Strategy
- GitHub repo: QuickAirMenu/projects
- GitHub Actions: tests (Pint + PHPStan) → deploy (matrix to 4 instances)
- Manual: deploy-all.sh script
- Each instance: git pull → composer install → migrate → optimize

## Future: Multi-Tenancy (VPS)
- stancl/tenancy package
- Database per tenant
- Wildcard DNS *.i-projects.net
- Custom domains per tenant
