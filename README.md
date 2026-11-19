# School & Madrasa ERP

Multi-tenant education management SaaS for Bangladesh (general schools, colleges, Alia / Qawmi / Hifz Madrasas).

**Phase 01 scope:** project initialization and premium design system only. No tenant engine, super-admin SaaS console, or student modules yet.

## Stack

| Layer | Choice |
|-------|--------|
| Runtime | PHP 8.3+ |
| Framework | Laravel 13 (modular monolith skeleton) |
| UI | Blade + Livewire 4 + Alpine.js + Tailwind CSS 4 |
| Icons | Lucide (`mallardduck/blade-lucide-icons`) |
| DB | **MySQL 8+** (primary) · **SQLite** OK for local / CI smoke |
| Tests | Pest + PHPUnit |
| Locale | `en` / `bn` / `ar` (RTL when `ar`) |
| Timezone | Asia/Dhaka |
| Currency | BDT |

## Requirements

- PHP 8.3+ with extensions: `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl`, `sqlite3` (local) or `pdo_mysql` (MySQL)
- Composer 2
- Node.js 20+ and npm
- MySQL 8+ **or** SQLite for local smoke

## Setup

```bash
git clone https://github.com/farhanaakterpinky1614-del/school-madrasa-erp.git
cd school-madrasa-erp
composer install
cp .env.example .env
php artisan key:generate
```

### Database — MySQL (recommended / production)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_madrasa_erp
DB_USERNAME=root
DB_PASSWORD=secret
```

```bash
php artisan migrate
```

### Database — SQLite (local / CI smoke)

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

```bash
touch database/database.sqlite
php artisan migrate
```

### Front-end assets

```bash
npm install
npm run build
# or during development:
npm run dev
```

### Run the app

```bash
php artisan serve
```

Open `http://127.0.0.1:8000` — redirects to the dashboard shell.

## Phase 01 routes

| Path | Purpose |
|------|---------|
| `/dashboard` | Premium layout shell (sidebar, topbar, dark/light, locale) |
| `/ui/kitchen-sink` | Reusable UI components demo (Livewire) |
| `/locale/{en\|bn\|ar}` | Switch locale (session) |

## Modular skeleton

Placeholder module directories under `modules/` (Tenancy, Identity, Academics, …, Platform) and `app/Core/` for shared kernel code. Domain logic arrives in later phases.

## Design system

- Primary Indigo `#4F46E5`, secondary Blue `#2563EB`, canvas Slate `#F8FAFC`
- Status: Emerald / Amber / Rose
- Fonts: Inter + Noto Sans Bengali + Noto Sans Arabic
- Dark mode via Alpine store + `class="dark"` on `<html>`
- Components: `resources/views/components/ui/*`

## Tests

```bash
php artisan test
# or
./vendor/bin/pest
```

CI-friendly default in `phpunit.xml` uses in-memory SQLite.

## What is intentionally not in Phase 01

- Tenant resolution / `tenant_id` scopes
- Auth, RBAC, Spatie Permission
- Super-admin SaaS panel
- Student / academic / fee modules
- Production deployment

## License

MIT (OSS license for public release may be revisited before launch — see project ADRs).
