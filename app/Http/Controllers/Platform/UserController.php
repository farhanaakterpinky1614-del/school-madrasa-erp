<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Identity\Services\AuditLogger;
use Modules\Identity\Services\PasswordPolicy;
use Modules\Identity\Services\RoleProvisioner;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        setPermissionsTeamId(RoleProvisioner::platformTeamId());

        $users = User::query()
            ->where('is_platform_admin', true)
            ->with('roles')
            ->orderBy('name')
            ->paginate(20);

        return view('platform.users.index', compact('users'));
    }

    public function create(): View
    {
        setPermissionsTeamId(RoleProvisioner::platformTeamId());

        $roles = Role::query()
            ->whereNull('tenant_id')
            ->orderBy('name')
            ->get();

        return view('platform.users.create', compact('roles'));
    }

    public function store(Request $request, PasswordPolicy $passwordPolicy, AuditLogger $audit): RedirectResponse
    {
        setPermissionsTeamId(RoleProvisioner::platformTeamId());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', $passwordPolicy->rule()],
            'role' => ['required', 'string'],
        ]);

        $user = User::query()->create([
            'tenant_id' => null,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_platform_admin' => true,
            'user_type' => User::TYPE_PLATFORM_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
            'password_changed_at' => now(),
        ]);

        $role = Role::query()->whereNull('tenant_id')->where('name', $data['role'])->firstOrFail();
        $user->assignRole($role);

        $audit->log('platform.users.created', $user, null, $user->only(['name', 'email']), null, null);

        return redirect()->route('platform.users.index')->with('status', __('ui.user_created'));
    }

    public function edit(User $user): View
    {
        abort_unless($user->is_platform_admin, 404);
        setPermissionsTeamId(RoleProvisioner::platformTeamId());

        $roles = Role::query()->whereNull('tenant_id')->orderBy('name')->get();

        return view('platform.users.edit', [
            'user' => $user->load('roles'),
            'roles' => $roles,
        ]);
    }

    public function update(
        Request $request,
        User $user,
        PasswordPolicy $passwordPolicy,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless($user->is_platform_admin, 404);
        setPermissionsTeamId(RoleProvisioner::platformTeamId());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', $passwordPolicy->rule()],
            'role' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $old = $user->only(['name', 'email', 'is_active']);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $request->boolean('is_active', $user->is_active),
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->password_changed_at = now();
        }

        $user->save();

        $role = Role::query()->whereNull('tenant_id')->where('name', $data['role'])->firstOrFail();
        $user->syncRoles([$role]);

        $audit->log('platform.users.updated', $user, $old, $user->only(['name', 'email', 'is_active']), null, null);

        return redirect()->route('platform.users.index')->with('status', __('ui.user_updated'));
    }
}
