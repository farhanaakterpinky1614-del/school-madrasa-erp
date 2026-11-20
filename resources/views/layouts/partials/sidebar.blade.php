@php
    $isPlatform = request()->routeIs('platform.*');
    $isTenant = request()->routeIs('tenant.*');

    if ($isPlatform) {
        $nav = [
            ['route' => 'platform.dashboard', 'label' => __('ui.nav.dashboard'), 'icon' => 'layout-dashboard', 'match' => 'platform.dashboard'],
            ['route' => 'platform.tenants.index', 'label' => __('ui.nav.tenants'), 'icon' => 'building-2', 'match' => 'platform.tenants.*'],
            ['route' => 'platform.institutions.index', 'label' => __('ui.nav.institutions'), 'icon' => 'school', 'match' => 'platform.institutions.*'],
            ['route' => 'platform.plans.index', 'label' => __('ui.nav.plans'), 'icon' => 'badge-dollar-sign', 'match' => 'platform.plans.*'],
            ['route' => 'platform.modules.index', 'label' => __('ui.nav.modules'), 'icon' => 'blocks', 'match' => 'platform.modules.*'],
            ['route' => 'platform.usage.index', 'label' => __('ui.nav.usage'), 'icon' => 'gauge', 'match' => 'platform.usage.*'],
            ['route' => 'platform.users.index', 'label' => __('ui.nav.users'), 'icon' => 'users', 'match' => 'platform.users.*'],
            ['route' => 'platform.audit.index', 'label' => __('ui.nav.audit'), 'icon' => 'scroll-text', 'match' => 'platform.audit.*'],
        ];
        $navTitle = __('ui.platform_nav');
        $brandSub = __('ui.super_admin');
    } elseif ($isTenant) {
        $nav = [
            ['route' => 'tenant.dashboard', 'label' => __('ui.nav.dashboard'), 'icon' => 'layout-dashboard', 'match' => 'tenant.dashboard'],
            ['route' => 'tenant.institution.edit', 'label' => __('ui.nav.institution'), 'icon' => 'school', 'match' => 'tenant.institution.*'],
            ['route' => 'tenant.users.index', 'label' => __('ui.nav.users'), 'icon' => 'users', 'match' => 'tenant.users.*'],
            ['route' => 'tenant.roles.index', 'label' => __('ui.nav.roles'), 'icon' => 'shield', 'match' => 'tenant.roles.*'],
            ['route' => 'tenant.permissions.index', 'label' => __('ui.nav.permissions'), 'icon' => 'key-round', 'match' => 'tenant.permissions.*'],
            ['route' => 'tenant.memberships.index', 'label' => __('ui.nav.memberships'), 'icon' => 'user-cog', 'match' => 'tenant.memberships.*'],
            ['route' => 'tenant.audit.index', 'label' => __('ui.nav.audit'), 'icon' => 'scroll-text', 'match' => 'tenant.audit.*'],
            ['route' => 'tenant.login-history.index', 'label' => __('ui.nav.login_history'), 'icon' => 'history', 'match' => 'tenant.login-history.*'],
            ['route' => 'tenant.sessions.index', 'label' => __('ui.nav.sessions'), 'icon' => 'monitor-smartphone', 'match' => 'tenant.sessions.*'],
            ['route' => 'tenant.domains.index', 'label' => __('ui.nav.domains'), 'icon' => 'globe', 'match' => 'tenant.domains.*'],
            ['route' => 'tenant.subscription.show', 'label' => __('ui.nav.subscription'), 'icon' => 'credit-card', 'match' => 'tenant.subscription.*'],
            ['route' => 'tenant.modules.index', 'label' => __('ui.nav.modules'), 'icon' => 'blocks', 'match' => 'tenant.modules.*'],
            ['route' => 'tenant.usage.index', 'label' => __('ui.nav.usage'), 'icon' => 'gauge', 'match' => 'tenant.usage.*'],
        ];
        $navTitle = __('ui.primary_nav');
        $brandSub = app(\App\Core\Tenancy\CurrentTenant::class)->get()?->name ?? __('ui.tenant_admin');
    } else {
        $nav = [
            ['route' => 'ui.shell-preview', 'label' => __('ui.nav.dashboard'), 'icon' => 'layout-dashboard', 'match' => 'ui.shell-preview'],
            ['route' => 'ui.kitchen-sink', 'label' => __('ui.nav.components'), 'icon' => 'component', 'match' => 'ui.kitchen-sink'],
        ];
        $navTitle = __('ui.primary_nav');
        $brandSub = __('ui.tagline');
    }
@endphp

<aside
    class="sme-sidebar-shell fixed inset-y-0 start-0 z-40 flex h-full flex-col text-sidebar transition-sidebar"
    :class="{
        'w-64': ! $store.sidebar.collapsed || window.innerWidth < 1024,
        'w-20': $store.sidebar.collapsed && window.innerWidth >= 1024,
        '-translate-x-full rtl:translate-x-full': ! $store.sidebar.open && window.innerWidth < 1024,
        'translate-x-0': $store.sidebar.open || window.innerWidth >= 1024
    }"
    @keydown.escape.window="$store.sidebar.closeMobile()"
>
    <div class="relative flex h-16 items-center gap-3 border-b border-white/10 px-4">
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-primary-400/40 to-transparent"></div>
        <div class="sme-brand-mark flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-white">
            <x-lucide-graduation-cap class="h-5 w-5" />
        </div>
        <div class="min-w-0" x-show="! $store.sidebar.collapsed || window.innerWidth < 1024">
            <p class="truncate text-sm font-semibold tracking-tight text-white">{{ __('ui.app_name') }}</p>
            <p class="truncate text-xs text-sidebar-muted">{{ $brandSub }}</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="{{ $navTitle }}">
        <p
            class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-sidebar-muted/80"
            x-show="! $store.sidebar.collapsed || window.innerWidth < 1024"
        >
            {{ $navTitle }}
        </p>
        @foreach ($nav as $item)
            <a
                href="{{ route($item['route']) }}"
                @click="$store.sidebar.closeMobile()"
                @class([
                    'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200',
                    'sme-nav-active text-white' => request()->routeIs($item['match']),
                    'text-sidebar-muted hover:bg-white/5 hover:text-white' => ! request()->routeIs($item['match']),
                ])
            >
                @svg('lucide-'.$item['icon'], 'h-5 w-5 shrink-0 opacity-90')
                <span x-show="! $store.sidebar.collapsed || window.innerWidth < 1024">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="hidden border-t border-white/10 p-3 lg:block">
        <button
            type="button"
            class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm text-sidebar-muted transition-colors hover:bg-white/5 hover:text-white"
            @click="$store.sidebar.toggleCollapse()"
            :aria-expanded="! $store.sidebar.collapsed"
            aria-label="{{ __('ui.toggle_sidebar') }}"
        >
            <x-lucide-panel-left-close class="h-5 w-5" x-show="! $store.sidebar.collapsed" />
            <x-lucide-panel-left-open class="h-5 w-5" x-show="$store.sidebar.collapsed" style="display: none;" />
            <span x-show="! $store.sidebar.collapsed">{{ __('ui.collapse') }}</span>
        </button>
    </div>
</aside>
