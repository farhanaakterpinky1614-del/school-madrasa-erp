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
            ['route' => 'tenant.settings.edit', 'label' => __('ui.nav.settings'), 'icon' => 'settings', 'match' => 'tenant.settings.*'],
            ['route' => 'tenant.branches.index', 'label' => __('ui.nav.branches'), 'icon' => 'building', 'match' => 'tenant.branches.*'],
            ['route' => 'tenant.departments.index', 'label' => __('ui.nav.departments'), 'icon' => 'network', 'match' => 'tenant.departments.*'],
            ['route' => 'tenant.years.index', 'label' => __('ui.nav.years'), 'icon' => 'calendar-range', 'match' => 'tenant.years.*'],
            ['route' => 'tenant.terms.index', 'label' => __('ui.nav.terms'), 'icon' => 'calendar-days', 'match' => 'tenant.terms.*'],
            ['route' => 'tenant.shifts.index', 'label' => __('ui.nav.shifts'), 'icon' => 'clock-3', 'match' => 'tenant.shifts.*'],
            ['route' => 'tenant.classes.index', 'label' => __('ui.nav.classes'), 'icon' => 'graduation-cap', 'match' => 'tenant.classes.*'],
            ['route' => 'tenant.sections.index', 'label' => __('ui.nav.sections'), 'icon' => 'layers', 'match' => 'tenant.sections.*'],
            ['route' => 'tenant.subjects.index', 'label' => __('ui.nav.subjects'), 'icon' => 'book-open', 'match' => 'tenant.subjects.*'],
            ['route' => 'tenant.curricula.index', 'label' => __('ui.nav.curricula'), 'icon' => 'library', 'match' => 'tenant.curricula.*'],
            ['route' => 'tenant.grading.index', 'label' => __('ui.nav.grading'), 'icon' => 'award', 'match' => 'tenant.grading.*'],
            ['route' => 'tenant.calendar.index', 'label' => __('ui.nav.calendar'), 'icon' => 'calendar', 'match' => 'tenant.calendar.*'],
            ['route' => 'tenant.students.index', 'label' => __('ui.nav.students'), 'icon' => 'user-round', 'match' => 'tenant.students.*'],
            ['route' => 'tenant.guardians.index', 'label' => __('ui.nav.guardians'), 'icon' => 'users-round', 'match' => 'tenant.guardians.*'],
            ['route' => 'tenant.applications.index', 'label' => __('ui.nav.applications'), 'icon' => 'clipboard-list', 'match' => 'tenant.applications.*'],
            ['route' => 'tenant.admission-sessions.index', 'label' => __('ui.nav.admission_sessions'), 'icon' => 'door-open', 'match' => 'tenant.admission-sessions.*'],
            ['route' => 'tenant.admission-forms.index', 'label' => __('ui.nav.admission_forms'), 'icon' => 'form-input', 'match' => 'tenant.admission-forms.*'],
            ['route' => 'tenant.transfers.index', 'label' => __('ui.nav.transfers'), 'icon' => 'arrow-left-right', 'match' => 'tenant.transfers.*'],
            ['route' => 'tenant.promotions.index', 'label' => __('ui.nav.promotions'), 'icon' => 'trending-up', 'match' => 'tenant.promotions.*'],
            ['route' => 'tenant.staff.index', 'label' => __('ui.nav.staff'), 'icon' => 'briefcase', 'match' => 'tenant.staff.*'],
            ['route' => 'tenant.teachers.index', 'label' => __('ui.nav.teachers'), 'icon' => 'presentation', 'match' => 'tenant.teachers.*'],
            ['route' => 'tenant.assignments.index', 'label' => __('ui.nav.assignments'), 'icon' => 'link', 'match' => 'tenant.assignments.*'],
            ['route' => 'tenant.designations.index', 'label' => __('ui.nav.designations'), 'icon' => 'badge-check', 'match' => 'tenant.designations.*'],
            ['route' => 'tenant.staff-attendance.index', 'label' => __('ui.nav.staff_attendance'), 'icon' => 'clipboard-check', 'match' => 'tenant.staff-attendance.*'],
            ['route' => 'tenant.leave-requests.index', 'label' => __('ui.nav.leave_requests'), 'icon' => 'calendar-off', 'match' => 'tenant.leave-requests.*'],
            ['route' => 'tenant.leave-types.index', 'label' => __('ui.nav.leave_types'), 'icon' => 'list-checks', 'match' => 'tenant.leave-types.*'],
            ['route' => 'tenant.salary.index', 'label' => __('ui.nav.salary'), 'icon' => 'wallet', 'match' => 'tenant.salary.*'],
            ['route' => 'tenant.payroll.index', 'label' => __('ui.nav.payroll'), 'icon' => 'banknote', 'match' => 'tenant.payroll.*'],
            ['route' => 'tenant.rooms.index', 'label' => __('ui.nav.rooms'), 'icon' => 'door-open', 'match' => 'tenant.rooms.*'],
            ['route' => 'tenant.periods.index', 'label' => __('ui.nav.periods'), 'icon' => 'timer', 'match' => 'tenant.periods.*'],
            ['route' => 'tenant.routines.index', 'label' => __('ui.nav.routines'), 'icon' => 'calendar-clock', 'match' => 'tenant.routines.*'],
            ['route' => 'tenant.teacher-routines.index', 'label' => __('ui.nav.teacher_routines'), 'icon' => 'user-round-cog', 'match' => 'tenant.teacher-routines.*'],
            ['route' => 'tenant.subject-offerings.index', 'label' => __('ui.nav.subject_offerings'), 'icon' => 'book-marked', 'match' => 'tenant.subject-offerings.*'],
            ['route' => 'tenant.syllabi.index', 'label' => __('ui.nav.syllabi'), 'icon' => 'notebook-text', 'match' => 'tenant.syllabi.*'],
            ['route' => 'tenant.lesson-plans.index', 'label' => __('ui.nav.lesson_plans'), 'icon' => 'clipboard-pen', 'match' => 'tenant.lesson-plans.*'],
            ['route' => 'tenant.homework.index', 'label' => __('ui.nav.homework'), 'icon' => 'pencil-line', 'match' => 'tenant.homework.*'],
            ['route' => 'tenant.classwork.index', 'label' => __('ui.nav.classwork'), 'icon' => 'file-pen', 'match' => 'tenant.classwork.*'],
            ['route' => 'tenant.materials.index', 'label' => __('ui.nav.materials'), 'icon' => 'folder-open', 'match' => 'tenant.materials.*'],
            ['route' => 'tenant.academic-reports.index', 'label' => __('ui.nav.academic_reports'), 'icon' => 'chart-column', 'match' => 'tenant.academic-reports.*'],
            ['route' => 'tenant.attendance.daily.index', 'label' => __('ui.nav.daily_attendance'), 'icon' => 'clipboard-list', 'match' => 'tenant.attendance.daily.*'],
            ['route' => 'tenant.attendance.period.index', 'label' => __('ui.nav.period_attendance'), 'icon' => 'calendar-check', 'match' => 'tenant.attendance.period.*'],
            ['route' => 'tenant.attendance.reports.index', 'label' => __('ui.nav.attendance_reports'), 'icon' => 'pie-chart', 'match' => 'tenant.attendance.reports.*'],
            ['route' => 'tenant.attendance.qr.index', 'label' => __('ui.nav.attendance_qr'), 'icon' => 'qr-code', 'match' => 'tenant.attendance.qr.*'],
            ['route' => 'tenant.exams.index', 'label' => __('ui.nav.exams'), 'icon' => 'file-badge', 'match' => 'tenant.exams.*'],
            ['route' => 'tenant.marks.index', 'label' => __('ui.nav.marks_entry'), 'icon' => 'pen-line', 'match' => 'tenant.marks.*'],
            ['route' => 'tenant.grading-schemes.index', 'label' => __('ui.nav.grading_schemes'), 'icon' => 'sigma', 'match' => 'tenant.grading-schemes.*'],
            ['route' => 'tenant.results.publish.index', 'label' => __('ui.nav.publish_results'), 'icon' => 'send', 'match' => 'tenant.results.publish.*'],
            ['route' => 'tenant.results.pdf.index', 'label' => __('ui.nav.result_pdfs'), 'icon' => 'file-down', 'match' => 'tenant.results.pdf.*'],
            ['route' => 'tenant.hifz.profiles.index', 'label' => __('ui.nav.hifz_profiles'), 'icon' => 'book-open', 'match' => 'tenant.hifz.profiles.*'],
            ['route' => 'tenant.hifz.progress.index', 'label' => __('ui.nav.hifz_progress'), 'icon' => 'list-checks', 'match' => 'tenant.hifz.progress.*'],
            ['route' => 'tenant.hifz.revisions.index', 'label' => __('ui.nav.hifz_revisions'), 'icon' => 'refresh-cw', 'match' => 'tenant.hifz.revisions.*'],
            ['route' => 'tenant.hifz.meta.index', 'label' => __('ui.nav.islamic_meta'), 'icon' => 'library', 'match' => 'tenant.hifz.meta.*'],
            ['route' => 'tenant.hifz.reports.index', 'label' => __('ui.nav.hifz_reports'), 'icon' => 'scroll', 'match' => 'tenant.hifz.reports.*'],
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
