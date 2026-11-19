# Modules

Bounded-context layout for the modular monolith (see `SYSTEM_ARCHITECTURE.md`).

| Module | Phase 02 status | Responsibility |
|--------|-----------------|----------------|
| Tenancy | **Implemented** | Tenant model, host resolution, registrar, domains, BelongsToTenant |
| Platform | **Implemented** | Super-admin console, plans, module registry, feature access, usage |
| Identity | Minimal | Users live in `App\Models\User`; Spatie teams foundation; full RBAC in Phase 03 |
| Academics…Reports | Placeholder | Later phases |

Core shared kernel: `app/Core/Tenancy` (`CurrentTenant`) and `app/Core/Concerns/BelongsToTenant`.
