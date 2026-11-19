# School & Madrasa ERP

Multi-tenant education management SaaS for Bangladesh (general schools, colleges, Alia / Qawmi / Hifz Madrasas).

**Phase 02 scope:** SaaS Super Admin & Tenant Engine — registration, host resolution, isolation, institution profiles, domains, plans/features, module registry, usage stubs. Full RBAC builder and academic modules are later phases.

## Stack

| Layer | Choice |
|-------|--------|
| Runtime | PHP 8.3+ |
| Framework | Laravel 13 (modular monolith) |
| UI | Blade + Livewire 4 + Alpine.js + Tailwind CSS 4 |
| Icons | Lucide (`mallardduck/blade-lucide-icons`) |
| Authz | Spatie Permission (teams/`tenant_id` enabled; minimal roles in Phase 02) |
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
php artisan migrate --seed
```

### Database — SQLite (local / CI smoke)

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

```bash
touch database/database.sqlite
php artisan migrate --seed
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

Open `http://127.0.0.1:8000` — redirects to login.

**Seeded super admin:** `superadmin@sme.local` / `password`

## Phase 02 routes

| Path | Purpose |
|------|---------|
| `/login` · `/register` | Auth + tenant self-registration |
| `/platform/*` | Super Admin SaaS console |
| `/app/*` | Tenant institution admin |
| `/ui/shell-preview` · `/ui/kitchen-sink` | Design-system demos |
| `/locale/{en\|bn\|ar}` | Switch locale (session) |

### Tenancy hosts

- Subdomain: `{slug}.{TENANCY_BASE_DOMAIN}` (default `localhost`)
- Custom domain: stored on `tenant_domains` (Enterprise feature; DNS verify stubbed)
- Platform: `/platform` path (and optional `admin.{base}`)
- Local fallback: on central hosts, authenticated tenant users bind via `tenant_id` when `TENANCY_USER_FALLBACK=true`

## Design system

Premium Indigo / Blue / Slate gradients from Phase 01 are preserved (sidebar, topbar, KPIs, surfaces).

## Tests

```bash
php artisan test
```

Isolation tests create two tenants and assert scoped queries + host IDOR denial.

## License

MIT (exact OSS packaging TBD before public release).
