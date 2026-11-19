<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Identity\Models\TenantMembership;
use Modules\Identity\Services\AuditLogger;
use Modules\Identity\Services\PasswordPolicy;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(CurrentTenant $currentTenant): View
    {
        $this->authorize('viewAny', User::class);

        setPermissionsTeamId($currentTenant->id());

        $users = User::query()
            ->where('tenant_id', $currentTenant->id())
            ->with('roles')
            ->orderBy('name')
            ->paginate(20);

        return view('tenant.users.index', compact('users'));
    }

    public function create(CurrentTenant $currentTenant): View
    {
        $this->authorize('create', User::class);

        setPermissionsTeamId($currentTenant->id());

        $roles = Role::query()
            ->where('tenant_id', $currentTenant->id())
            ->orderBy('name')
            ->get();

        return view('tenant.users.create', compact('roles'));
    }

    public function store(
        Request $request,
        CurrentTenant $currentTenant,
        PasswordPolicy $passwordPolicy,
        AuditLogger $audit,
    ): RedirectResponse {
        $this->authorize('create', User::class);

        setPermissionsTeamId($currentTenant->id());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', $passwordPolicy->rule()],
            'user_type' => ['required', Rule::in([User::TYPE_TENANT_ADMIN, User::TYPE_STAFF, User::TYPE_OWNER])],
            'role' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = User::query()->create([
            'tenant_id' => $currentTenant->id(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_platform_admin' => false,
            'user_type' => $data['user_type'],
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
            'password_changed_at' => now(),
        ]);

        TenantMembership::query()->create([
            'tenant_id' => $currentTenant->id(),
            'user_id' => $user->id,
            'status' => TenantMembership::STATUS_ACTIVE,
            'invited_by' => $request->user()->id,
            'joined_at' => now(),
        ]);

        if (! empty($data['role'])) {
            $role = Role::query()
                ->where('tenant_id', $currentTenant->id())
                ->where('name', $data['role'])
                ->firstOrFail();
            $user->assignRole($role);
        }

        $audit->log('users.created', $user, null, $user->only(['name', 'email', 'user_type']));

        return redirect()
            ->route('tenant.users.index')
            ->with('status', __('ui.user_created'));
    }

    public function edit(User $user, CurrentTenant $currentTenant): View
    {
        abort_unless((int) $user->tenant_id === (int) $currentTenant->id(), 404);
        $this->authorize('update', $user);

        setPermissionsTeamId($currentTenant->id());

        $roles = Role::query()
            ->where('tenant_id', $currentTenant->id())
            ->orderBy('name')
            ->get();

        return view('tenant.users.edit', [
            'user' => $user->load('roles'),
            'roles' => $roles,
        ]);
    }

    public function update(
        Request $request,
        User $user,
        CurrentTenant $currentTenant,
        PasswordPolicy $passwordPolicy,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless((int) $user->tenant_id === (int) $currentTenant->id(), 404);
        $this->authorize('update', $user);

        setPermissionsTeamId($currentTenant->id());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', $passwordPolicy->rule()],
            'user_type' => ['required', Rule::in([User::TYPE_TENANT_ADMIN, User::TYPE_STAFF, User::TYPE_OWNER])],
            'role' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $old = $user->only(['name', 'email', 'user_type', 'is_active']);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'user_type' => $data['user_type'],
            'is_active' => $request->boolean('is_active', $user->is_active),
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->password_changed_at = now();
        }

        $user->save();

        if (array_key_exists('role', $data)) {
            if ($data['role']) {
                $role = Role::query()
                    ->where('tenant_id', $currentTenant->id())
                    ->where('name', $data['role'])
                    ->firstOrFail();
                $user->syncRoles([$role]);
            } else {
                $user->syncRoles([]);
            }
        }

        $audit->log('users.updated', $user, $old, $user->only(['name', 'email', 'user_type', 'is_active']));

        return redirect()
            ->route('tenant.users.index')
            ->with('status', __('ui.user_updated'));
    }

    public function destroy(User $user, CurrentTenant $currentTenant, AuditLogger $audit): RedirectResponse
    {
        abort_unless((int) $user->tenant_id === (int) $currentTenant->id(), 404);
        $this->authorize('delete', $user);

        $audit->log('users.deleted', $user, $user->only(['name', 'email']), null);
        $user->delete();

        return redirect()
            ->route('tenant.users.index')
            ->with('status', __('ui.user_deleted'));
    }
}
