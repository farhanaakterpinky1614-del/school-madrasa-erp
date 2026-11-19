<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Identity\Services\AuditLogger;
use Modules\Identity\Services\RoleProvisioner;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(CurrentTenant $currentTenant): View
    {
        $this->authorize('viewAny', Role::class);

        setPermissionsTeamId($currentTenant->id());

        $roles = Role::query()
            ->where('tenant_id', $currentTenant->id())
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('tenant.roles.index', compact('roles'));
    }

    public function create(CurrentTenant $currentTenant): View
    {
        $this->authorize('create', Role::class);

        $permissions = $this->tenantPermissionCatalog();

        return view('tenant.roles.create', compact('permissions'));
    }

    public function store(
        Request $request,
        CurrentTenant $currentTenant,
        RoleProvisioner $provisioner,
        AuditLogger $audit,
    ): RedirectResponse {
        $this->authorize('create', Role::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:125'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        setPermissionsTeamId($currentTenant->id());

        if (Role::query()->where('tenant_id', $currentTenant->id())->where('name', $data['name'])->exists()) {
            return back()->withErrors(['name' => __('ui.role_name_taken')])->withInput();
        }

        $role = $provisioner->createCustomRole(
            $currentTenant->get(),
            $data['name'],
            $data['permissions'] ?? [],
        );

        $audit->log('roles.created', $role, null, [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->all(),
        ]);

        return redirect()
            ->route('tenant.roles.index')
            ->with('status', __('ui.role_created'));
    }

    public function edit(Role $role, CurrentTenant $currentTenant): View
    {
        abort_unless((int) $role->tenant_id === (int) $currentTenant->id(), 404);
        $this->authorize('update', $role);

        setPermissionsTeamId($currentTenant->id());

        $permissions = $this->tenantPermissionCatalog();
        $assigned = $role->permissions->pluck('name')->all();

        return view('tenant.roles.edit', compact('role', 'permissions', 'assigned'));
    }

    public function update(
        Request $request,
        Role $role,
        CurrentTenant $currentTenant,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless((int) $role->tenant_id === (int) $currentTenant->id(), 404);
        $this->authorize('update', $role);

        setPermissionsTeamId($currentTenant->id());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:125'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $allowed = collect(config('identity.permissions'))
            ->reject(fn (string $p) => str_starts_with($p, 'platform.'))
            ->all();

        $filtered = array_values(array_intersect($data['permissions'] ?? [], $allowed));
        $old = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->all(),
        ];

        if ($role->name !== 'Institution Admin') {
            $role->name = $data['name'];
            $role->save();
        }

        $role->syncPermissions($filtered);

        $audit->log('roles.updated', $role, $old, [
            'name' => $role->name,
            'permissions' => $filtered,
        ]);

        return redirect()
            ->route('tenant.roles.index')
            ->with('status', __('ui.role_updated'));
    }

    public function destroy(Role $role, CurrentTenant $currentTenant, AuditLogger $audit): RedirectResponse
    {
        abort_unless((int) $role->tenant_id === (int) $currentTenant->id(), 404);
        $this->authorize('delete', $role);

        $audit->log('roles.deleted', $role, ['name' => $role->name], null);
        $role->delete();

        return redirect()
            ->route('tenant.roles.index')
            ->with('status', __('ui.role_deleted'));
    }

    /**
     * @return array<string, list<string>>
     */
    protected function tenantPermissionCatalog(): array
    {
        return collect(config('identity.permissions'))
            ->reject(fn (string $p) => str_starts_with($p, 'platform.'))
            ->groupBy(fn (string $p) => explode('.', $p)[0])
            ->map(fn ($group) => $group->values()->all())
            ->all();
    }
}
