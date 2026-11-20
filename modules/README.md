# Modules

Bounded-context layout for the modular monolith (see `SYSTEM_ARCHITECTURE.md`).

| Module | Status | Responsibility |
|--------|--------|----------------|
| Tenancy | Implemented | Tenant model, host resolution, registrar, domains, BelongsToTenant |
| Platform | Implemented | Super-admin console, plans, module registry, feature access, usage |
| Identity | Implemented (Phase 03) | Spatie teams RBAC, memberships, audit, login history, sessions |
| Settings | Implemented (Phase 04) | Institution settings (locale/currency/timezone/branding hooks) |
| Academics | Implemented (Phase 04) | Branches, departments, years/terms, shifts, classes/sections, subjects, curricula, grading, calendar |
| Admissions…Reports | Placeholder | Later phases |

Core shared kernel: `app/Core/Tenancy` (`CurrentTenant`) and `app/Core/Concerns/BelongsToTenant`.
