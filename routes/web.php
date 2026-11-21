<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterTenantController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Platform\AuditLogController as PlatformAuditLogController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\InstitutionController as PlatformInstitutionController;
use App\Http\Controllers\Platform\ModuleController as PlatformModuleController;
use App\Http\Controllers\Platform\PlanController as PlatformPlanController;
use App\Http\Controllers\Platform\TenantController as PlatformTenantController;
use App\Http\Controllers\Platform\UsageController as PlatformUsageController;
use App\Http\Controllers\Platform\UserController as PlatformUserController;
use App\Http\Controllers\Tenant\AuditLogController as TenantAuditLogController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\DomainController as TenantDomainController;
use App\Http\Controllers\Tenant\InstitutionController as TenantInstitutionController;
use App\Http\Controllers\Tenant\LoginHistoryController as TenantLoginHistoryController;
use App\Http\Controllers\Tenant\MembershipController as TenantMembershipController;
use App\Http\Controllers\Tenant\ModuleController as TenantModuleController;
use App\Http\Controllers\Tenant\PermissionController as TenantPermissionController;
use App\Http\Controllers\Tenant\RoleController as TenantRoleController;
use App\Http\Controllers\Tenant\SessionController as TenantSessionController;
use App\Http\Controllers\Tenant\SubscriptionController as TenantSubscriptionController;
use App\Http\Controllers\Tenant\UsageController as TenantUsageController;
use App\Http\Controllers\Tenant\Setup\AcademicTermController as TenantAcademicTermController;
use App\Http\Controllers\Tenant\Setup\AcademicYearController as TenantAcademicYearController;
use App\Http\Controllers\Tenant\Setup\BranchController as TenantBranchController;
use App\Http\Controllers\Tenant\Setup\CalendarEventController as TenantCalendarEventController;
use App\Http\Controllers\Tenant\Setup\CurriculumController as TenantCurriculumController;
use App\Http\Controllers\Tenant\Setup\DepartmentController as TenantDepartmentController;
use App\Http\Controllers\Tenant\Setup\GradingScaleController as TenantGradingScaleController;
use App\Http\Controllers\Tenant\Setup\InstitutionSettingsController as TenantInstitutionSettingsController;
use App\Http\Controllers\Tenant\Setup\SchoolClassController as TenantSchoolClassController;
use App\Http\Controllers\Tenant\Setup\SectionController as TenantSectionController;
use App\Http\Controllers\Tenant\Setup\ShiftController as TenantShiftController;
use App\Http\Controllers\Tenant\Setup\SubjectController as TenantSubjectController;
use App\Http\Controllers\Tenant\UserController as TenantUserController;
use App\Http\Controllers\Tenant\Students\StudentController as TenantStudentController;
use App\Http\Controllers\Tenant\Students\GuardianController as TenantGuardianController;
use App\Http\Controllers\Tenant\Students\StudentDocumentController as TenantStudentDocumentController;
use App\Http\Controllers\Tenant\Students\EnrollmentController as TenantEnrollmentController;
use App\Http\Controllers\Tenant\Students\TransferController as TenantTransferController;
use App\Http\Controllers\Tenant\Students\PromotionController as TenantPromotionController;
use App\Http\Controllers\Tenant\Students\IdCardController as TenantIdCardController;
use App\Http\Controllers\Tenant\Students\StudentPhotoController as TenantStudentPhotoController;
use App\Http\Controllers\Tenant\Students\StudentImportController as TenantStudentImportController;
use App\Http\Controllers\Tenant\Admissions\FormTemplateController as TenantAdmissionFormController;
use App\Http\Controllers\Tenant\Admissions\AdmissionSessionController as TenantAdmissionSessionController;
use App\Http\Controllers\Tenant\Admissions\ApplicationController as TenantApplicationController;
use App\Http\Controllers\Tenant\Hr\StaffController as TenantStaffController;
use App\Http\Controllers\Tenant\Hr\TeacherController as TenantTeacherController;
use App\Http\Controllers\Tenant\Hr\DesignationController as TenantDesignationController;
use App\Http\Controllers\Tenant\Hr\TeacherAssignmentController as TenantTeacherAssignmentController;
use App\Http\Controllers\Tenant\Hr\StaffDocumentController as TenantStaffDocumentController;
use App\Http\Controllers\Tenant\Hr\StaffAttendanceController as TenantStaffAttendanceController;
use App\Http\Controllers\Tenant\Hr\LeaveTypeController as TenantLeaveTypeController;
use App\Http\Controllers\Tenant\Hr\LeaveRequestController as TenantLeaveRequestController;
use App\Http\Controllers\Tenant\Hr\SalaryStructureController as TenantSalaryStructureController;
use App\Http\Controllers\Tenant\Hr\PayrollController as TenantPayrollController;
use App\Http\Controllers\Tenant\Academics\RoomController as TenantRoomController;
use App\Http\Controllers\Tenant\Academics\PeriodController as TenantPeriodController;
use App\Http\Controllers\Tenant\Academics\RoutineController as TenantRoutineController;
use App\Http\Controllers\Tenant\Academics\TeacherRoutineController as TenantTeacherRoutineController;
use App\Http\Controllers\Tenant\Academics\SubjectOfferingController as TenantSubjectOfferingController;
use App\Http\Controllers\Tenant\Academics\SyllabusController as TenantSyllabusController;
use App\Http\Controllers\Tenant\Academics\LessonPlanController as TenantLessonPlanController;
use App\Http\Controllers\Tenant\Academics\HomeworkController as TenantHomeworkController;
use App\Http\Controllers\Tenant\Academics\ClassworkController as TenantClassworkController;
use App\Http\Controllers\Tenant\Academics\LearningMaterialController as TenantLearningMaterialController;
use App\Http\Controllers\Tenant\Academics\AcademicReportController as TenantAcademicReportController;
use App\Http\Controllers\Tenant\Attendance\DailyAttendanceController as TenantDailyAttendanceController;
use App\Http\Controllers\Tenant\Attendance\PeriodAttendanceController as TenantPeriodAttendanceController;
use App\Http\Controllers\Tenant\Attendance\AttendanceReportController as TenantAttendanceReportController;
use App\Http\Controllers\Tenant\Attendance\AttendanceQrController as TenantAttendanceQrController;
use App\Http\Controllers\Tenant\Examinations\ExamController as TenantExamController;
use App\Http\Controllers\Tenant\Examinations\ExamSubjectController as TenantExamSubjectController;
use App\Http\Controllers\Tenant\Examinations\MarksEntryController as TenantMarksEntryController;
use App\Http\Controllers\Tenant\Examinations\ResultPublishController as TenantResultPublishController;
use App\Http\Controllers\Tenant\Examinations\GradingSchemeController as TenantGradingSchemeController;
use App\Http\Controllers\Tenant\Examinations\MarksheetPdfController as TenantMarksheetPdfController;
use App\Http\Controllers\PublicAdmissionController;
use App\Livewire\Demo\UiKitchenSink;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->is_platform_admin
            ? redirect()->route('platform.dashboard')
            : redirect()->route('tenant.dashboard');
    }

    return redirect()->route('login');
});

/*
| Soft landing used by Phase 01 bookmarks / Try Live — never dump platform admins into /app.
*/
Route::get('/home', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->is_platform_admin
        ? redirect()->route('platform.dashboard')
        : redirect()->route('tenant.dashboard');
})->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

/*
|--------------------------------------------------------------------------
| Public online admission (rate limited)
|--------------------------------------------------------------------------
*/
Route::prefix('apply/{tenantSlug}')
    ->name('public.admissions.')
    ->group(function () {
        Route::get('/{sessionSlug}', [PublicAdmissionController::class, 'show'])->name('show');
        Route::post('/{sessionSlug}', [PublicAdmissionController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('store');
        Route::get('/{sessionSlug}/thanks/{applicationNumber}', [PublicAdmissionController::class, 'thanks'])->name('thanks');
    });

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/register', [RegisterTenantController::class, 'create'])->name('register');
    Route::post('/register', [RegisterTenantController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Design-system demos (Phase 01) — auth optional for kitchen sink
|--------------------------------------------------------------------------
*/
Route::view('/ui/shell-preview', 'dashboard.index')->name('ui.shell-preview');
Route::get('/ui/kitchen-sink', UiKitchenSink::class)->name('ui.kitchen-sink');

/*
|--------------------------------------------------------------------------
| Super Admin (platform) — no tenant scope required
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'platform.admin'])
    ->prefix('platform')
    ->name('platform.')
    ->group(function () {
        Route::get('/', PlatformDashboardController::class)->name('dashboard');
        Route::get('/tenants', [PlatformTenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [PlatformTenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [PlatformTenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [PlatformTenantController::class, 'show'])->name('tenants.show');
        Route::patch('/tenants/{tenant}/status', [PlatformTenantController::class, 'updateStatus'])->name('tenants.status');

        Route::get('/institutions', [PlatformInstitutionController::class, 'index'])->name('institutions.index');
        Route::get('/institutions/{institution}', [PlatformInstitutionController::class, 'show'])->name('institutions.show');

        Route::get('/plans', [PlatformPlanController::class, 'index'])->name('plans.index');
        Route::get('/modules', [PlatformModuleController::class, 'index'])->name('modules.index');
        Route::get('/usage', [PlatformUsageController::class, 'index'])->name('usage.index');

        Route::get('/users', [PlatformUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [PlatformUserController::class, 'create'])->name('users.create');
        Route::post('/users', [PlatformUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [PlatformUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [PlatformUserController::class, 'update'])->name('users.update');

        Route::get('/audit', [PlatformAuditLogController::class, 'index'])->name('audit.index');
    });

/*
|--------------------------------------------------------------------------
| Tenant back-office — members + permission gates (server-side)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant.member', 'tenant.context'])
    ->prefix('app')
    ->name('tenant.')
    ->group(function () {
        Route::get('/', TenantDashboardController::class)->name('dashboard');

        Route::middleware('permission:settings.institution.view')->group(function () {
            Route::get('/institution', [TenantInstitutionController::class, 'edit'])->name('institution.edit');
        });
        Route::middleware('permission:settings.institution.update')->group(function () {
            Route::put('/institution', [TenantInstitutionController::class, 'update'])->name('institution.update');
        });

        Route::middleware('permission:settings.branding.view')->group(function () {
            Route::get('/settings', [TenantInstitutionSettingsController::class, 'edit'])->name('settings.edit');
        });
        Route::middleware('permission:settings.branding.update')->group(function () {
            Route::put('/settings', [TenantInstitutionSettingsController::class, 'update'])->name('settings.update');
        });

        // Phase 04 — Institution setup & academic structure
        Route::middleware('permission:branches.branch.view')->group(function () {
            Route::get('/branches', [TenantBranchController::class, 'index'])->name('branches.index');
        });
        Route::middleware('permission:branches.branch.manage')->group(function () {
            Route::get('/branches/create', [TenantBranchController::class, 'create'])->name('branches.create');
            Route::post('/branches', [TenantBranchController::class, 'store'])->name('branches.store');
            Route::get('/branches/{branch}/edit', [TenantBranchController::class, 'edit'])->name('branches.edit');
            Route::put('/branches/{branch}', [TenantBranchController::class, 'update'])->name('branches.update');
            Route::delete('/branches/{branch}', [TenantBranchController::class, 'destroy'])->name('branches.destroy');
        });

        Route::middleware('permission:academics.department.view')->group(function () {
            Route::get('/departments', [TenantDepartmentController::class, 'index'])->name('departments.index');
        });
        Route::middleware('permission:academics.department.manage')->group(function () {
            Route::get('/departments/create', [TenantDepartmentController::class, 'create'])->name('departments.create');
            Route::post('/departments', [TenantDepartmentController::class, 'store'])->name('departments.store');
            Route::get('/departments/{department}/edit', [TenantDepartmentController::class, 'edit'])->name('departments.edit');
            Route::put('/departments/{department}', [TenantDepartmentController::class, 'update'])->name('departments.update');
            Route::delete('/departments/{department}', [TenantDepartmentController::class, 'destroy'])->name('departments.destroy');
        });

        Route::middleware('permission:academics.year.view')->group(function () {
            Route::get('/years', [TenantAcademicYearController::class, 'index'])->name('years.index');
        });
        Route::middleware('permission:academics.year.manage')->group(function () {
            Route::get('/years/create', [TenantAcademicYearController::class, 'create'])->name('years.create');
            Route::post('/years', [TenantAcademicYearController::class, 'store'])->name('years.store');
            Route::get('/years/{year}/edit', [TenantAcademicYearController::class, 'edit'])->name('years.edit');
            Route::put('/years/{year}', [TenantAcademicYearController::class, 'update'])->name('years.update');
            Route::delete('/years/{year}', [TenantAcademicYearController::class, 'destroy'])->name('years.destroy');
        });

        Route::middleware('permission:academics.term.view')->group(function () {
            Route::get('/terms', [TenantAcademicTermController::class, 'index'])->name('terms.index');
        });
        Route::middleware('permission:academics.term.manage')->group(function () {
            Route::get('/terms/create', [TenantAcademicTermController::class, 'create'])->name('terms.create');
            Route::post('/terms', [TenantAcademicTermController::class, 'store'])->name('terms.store');
            Route::get('/terms/{term}/edit', [TenantAcademicTermController::class, 'edit'])->name('terms.edit');
            Route::put('/terms/{term}', [TenantAcademicTermController::class, 'update'])->name('terms.update');
            Route::delete('/terms/{term}', [TenantAcademicTermController::class, 'destroy'])->name('terms.destroy');
        });

        Route::middleware('permission:academics.shift.view')->group(function () {
            Route::get('/shifts', [TenantShiftController::class, 'index'])->name('shifts.index');
        });
        Route::middleware('permission:academics.shift.manage')->group(function () {
            Route::get('/shifts/create', [TenantShiftController::class, 'create'])->name('shifts.create');
            Route::post('/shifts', [TenantShiftController::class, 'store'])->name('shifts.store');
            Route::get('/shifts/{shift}/edit', [TenantShiftController::class, 'edit'])->name('shifts.edit');
            Route::put('/shifts/{shift}', [TenantShiftController::class, 'update'])->name('shifts.update');
            Route::delete('/shifts/{shift}', [TenantShiftController::class, 'destroy'])->name('shifts.destroy');
        });

        Route::middleware('permission:academics.class.view')->group(function () {
            Route::get('/classes', [TenantSchoolClassController::class, 'index'])->name('classes.index');
        });
        Route::middleware('permission:academics.class.manage')->group(function () {
            Route::get('/classes/create', [TenantSchoolClassController::class, 'create'])->name('classes.create');
            Route::post('/classes', [TenantSchoolClassController::class, 'store'])->name('classes.store');
            Route::get('/classes/{class}/edit', [TenantSchoolClassController::class, 'edit'])->name('classes.edit');
            Route::put('/classes/{class}', [TenantSchoolClassController::class, 'update'])->name('classes.update');
            Route::delete('/classes/{class}', [TenantSchoolClassController::class, 'destroy'])->name('classes.destroy');
        });

        Route::middleware('permission:academics.section.view')->group(function () {
            Route::get('/sections', [TenantSectionController::class, 'index'])->name('sections.index');
        });
        Route::middleware('permission:academics.section.manage')->group(function () {
            Route::get('/sections/create', [TenantSectionController::class, 'create'])->name('sections.create');
            Route::post('/sections', [TenantSectionController::class, 'store'])->name('sections.store');
            Route::get('/sections/{section}/edit', [TenantSectionController::class, 'edit'])->name('sections.edit');
            Route::put('/sections/{section}', [TenantSectionController::class, 'update'])->name('sections.update');
            Route::delete('/sections/{section}', [TenantSectionController::class, 'destroy'])->name('sections.destroy');
        });

        Route::middleware('permission:academics.subject.view')->group(function () {
            Route::get('/subjects', [TenantSubjectController::class, 'index'])->name('subjects.index');
        });
        Route::middleware('permission:academics.subject.manage')->group(function () {
            Route::get('/subjects/create', [TenantSubjectController::class, 'create'])->name('subjects.create');
            Route::post('/subjects', [TenantSubjectController::class, 'store'])->name('subjects.store');
            Route::get('/subjects/{subject}/edit', [TenantSubjectController::class, 'edit'])->name('subjects.edit');
            Route::put('/subjects/{subject}', [TenantSubjectController::class, 'update'])->name('subjects.update');
            Route::delete('/subjects/{subject}', [TenantSubjectController::class, 'destroy'])->name('subjects.destroy');
        });

        Route::middleware('permission:academics.curriculum.view')->group(function () {
            Route::get('/curricula', [TenantCurriculumController::class, 'index'])->name('curricula.index');
        });
        Route::middleware('permission:academics.curriculum.manage')->group(function () {
            Route::get('/curricula/create', [TenantCurriculumController::class, 'create'])->name('curricula.create');
            Route::post('/curricula', [TenantCurriculumController::class, 'store'])->name('curricula.store');
            Route::get('/curricula/{curriculum}/edit', [TenantCurriculumController::class, 'edit'])->name('curricula.edit');
            Route::put('/curricula/{curriculum}', [TenantCurriculumController::class, 'update'])->name('curricula.update');
            Route::post('/curricula/{curriculum}/subjects', [TenantCurriculumController::class, 'attachSubject'])->name('curricula.attach-subject');
            Route::delete('/curricula/{curriculum}/subjects/{link}', [TenantCurriculumController::class, 'detachSubject'])->name('curricula.detach-subject');
            Route::delete('/curricula/{curriculum}', [TenantCurriculumController::class, 'destroy'])->name('curricula.destroy');
        });

        Route::middleware('permission:academics.grading.view')->group(function () {
            Route::get('/grading', [TenantGradingScaleController::class, 'index'])->name('grading.index');
        });
        Route::middleware('permission:academics.grading.manage')->group(function () {
            Route::get('/grading/create', [TenantGradingScaleController::class, 'create'])->name('grading.create');
            Route::post('/grading', [TenantGradingScaleController::class, 'store'])->name('grading.store');
            Route::get('/grading/{grading}/edit', [TenantGradingScaleController::class, 'edit'])->name('grading.edit');
            Route::put('/grading/{grading}', [TenantGradingScaleController::class, 'update'])->name('grading.update');
            Route::post('/grading/{grading}/bands', [TenantGradingScaleController::class, 'storeBand'])->name('grading.store-band');
            Route::delete('/grading/{grading}/bands/{band}', [TenantGradingScaleController::class, 'destroyBand'])->name('grading.destroy-band');
            Route::delete('/grading/{grading}', [TenantGradingScaleController::class, 'destroy'])->name('grading.destroy');
        });

        Route::middleware('permission:academics.calendar.view')->group(function () {
            Route::get('/calendar', [TenantCalendarEventController::class, 'index'])->name('calendar.index');
        });
        Route::middleware('permission:academics.calendar.manage')->group(function () {
            Route::get('/calendar/create', [TenantCalendarEventController::class, 'create'])->name('calendar.create');
            Route::post('/calendar', [TenantCalendarEventController::class, 'store'])->name('calendar.store');
            Route::get('/calendar/{calendar}/edit', [TenantCalendarEventController::class, 'edit'])->name('calendar.edit');
            Route::put('/calendar/{calendar}', [TenantCalendarEventController::class, 'update'])->name('calendar.update');
            Route::delete('/calendar/{calendar}', [TenantCalendarEventController::class, 'destroy'])->name('calendar.destroy');
        });

        // Phase 05 — Students & admissions (static paths before {student}/{application})
        Route::middleware('permission:students.student.view')->group(function () {
            Route::get('/students', [TenantStudentController::class, 'index'])->name('students.index');
            Route::get('/guardians', [TenantGuardianController::class, 'index'])->name('guardians.index');
            Route::get('/transfers', [TenantTransferController::class, 'index'])->name('transfers.index');
            Route::get('/promotions', [TenantPromotionController::class, 'index'])->name('promotions.index');
        });
        Route::middleware('permission:students.student.create')->group(function () {
            Route::get('/students/create', [TenantStudentController::class, 'create'])->name('students.create');
            Route::post('/students', [TenantStudentController::class, 'store'])->name('students.store');
            Route::get('/students/import', [TenantStudentImportController::class, 'create'])->name('students.import.create');
            Route::post('/students/import/preview', [TenantStudentImportController::class, 'preview'])->name('students.import.preview');
            Route::get('/students/import/{batch}', [TenantStudentImportController::class, 'show'])->name('students.import.show');
            Route::post('/students/import/{batch}/commit', [TenantStudentImportController::class, 'commit'])->name('students.import.commit');
            Route::get('/guardians/create', [TenantGuardianController::class, 'create'])->name('guardians.create');
            Route::post('/guardians', [TenantGuardianController::class, 'store'])->name('guardians.store');
            Route::get('/offline-admission', [TenantApplicationController::class, 'offlineCreate'])->name('applications.offline.create');
            Route::post('/offline-admission', [TenantApplicationController::class, 'offlineStore'])->name('applications.offline.store');
        });
        Route::middleware('permission:students.student.update')->group(function () {
            Route::get('/guardians/{guardian}/edit', [TenantGuardianController::class, 'edit'])->name('guardians.edit');
            Route::put('/guardians/{guardian}', [TenantGuardianController::class, 'update'])->name('guardians.update');
            Route::get('/students/{student}/edit', [TenantStudentController::class, 'edit'])->name('students.edit');
            Route::put('/students/{student}', [TenantStudentController::class, 'update'])->name('students.update');
            Route::post('/students/{student}/withdraw', [TenantStudentController::class, 'withdraw'])->name('students.withdraw');
            Route::post('/students/{student}/alumni', [TenantStudentController::class, 'markAlumni'])->name('students.alumni');
            Route::post('/students/{student}/documents', [TenantStudentDocumentController::class, 'store'])->name('students.documents.store');
            Route::delete('/students/{student}/documents/{document}', [TenantStudentDocumentController::class, 'destroy'])->name('students.documents.destroy');
            Route::post('/students/{student}/photo', [TenantStudentPhotoController::class, 'store'])->name('students.photo.store');
            Route::delete('/students/{student}/photo', [TenantStudentPhotoController::class, 'destroy'])->name('students.photo.destroy');
            Route::post('/students/{student}/guardians', [TenantGuardianController::class, 'attach'])->name('students.guardians.attach');
            Route::get('/students/{student}/enrollments/create', [TenantEnrollmentController::class, 'create'])->name('students.enrollments.create');
            Route::post('/students/{student}/enrollments', [TenantEnrollmentController::class, 'store'])->name('students.enrollments.store');
            Route::get('/students/{student}/transfers/create', [TenantTransferController::class, 'create'])->name('students.transfers.create');
            Route::post('/students/{student}/transfers', [TenantTransferController::class, 'store'])->name('students.transfers.store');
            Route::get('/students/{student}/promotions/create', [TenantPromotionController::class, 'create'])->name('students.promotions.create');
            Route::post('/students/{student}/promotions', [TenantPromotionController::class, 'store'])->name('students.promotions.store');
        });
        Route::middleware('permission:students.student.view')->group(function () {
            Route::get('/students/{student}/photo', [TenantStudentPhotoController::class, 'show'])->name('students.photo.show');
            Route::get('/students/{student}/id-card', [TenantIdCardController::class, 'show'])->name('students.id-card');
            Route::get('/students/{student}', [TenantStudentController::class, 'show'])->name('students.show');
        });

        Route::middleware('permission:admissions.application.view')->group(function () {
            Route::get('/admission-forms', [TenantAdmissionFormController::class, 'index'])->name('admission-forms.index');
            Route::get('/admission-sessions', [TenantAdmissionSessionController::class, 'index'])->name('admission-sessions.index');
            Route::get('/applications', [TenantApplicationController::class, 'index'])->name('applications.index');
        });
        Route::middleware('permission:admissions.application.manage')->group(function () {
            Route::get('/admission-forms/create', [TenantAdmissionFormController::class, 'create'])->name('admission-forms.create');
            Route::post('/admission-forms', [TenantAdmissionFormController::class, 'store'])->name('admission-forms.store');
            Route::get('/admission-forms/{admission_form}/edit', [TenantAdmissionFormController::class, 'edit'])->name('admission-forms.edit');
            Route::put('/admission-forms/{admission_form}', [TenantAdmissionFormController::class, 'update'])->name('admission-forms.update');
            Route::post('/admission-forms/{admission_form}/fields', [TenantAdmissionFormController::class, 'storeField'])->name('admission-forms.fields.store');
            Route::delete('/admission-forms/{admission_form}/fields/{field}', [TenantAdmissionFormController::class, 'destroyField'])->name('admission-forms.fields.destroy');

            Route::get('/admission-sessions/create', [TenantAdmissionSessionController::class, 'create'])->name('admission-sessions.create');
            Route::post('/admission-sessions', [TenantAdmissionSessionController::class, 'store'])->name('admission-sessions.store');
            Route::get('/admission-sessions/{admission_session}/edit', [TenantAdmissionSessionController::class, 'edit'])->name('admission-sessions.edit');
            Route::put('/admission-sessions/{admission_session}', [TenantAdmissionSessionController::class, 'update'])->name('admission-sessions.update');

            Route::post('/applications/{application}/review', [TenantApplicationController::class, 'review'])->name('applications.review');
            Route::post('/applications/{application}/approve', [TenantApplicationController::class, 'approve'])->name('applications.approve');
            Route::post('/applications/{application}/reject', [TenantApplicationController::class, 'reject'])->name('applications.reject');
            Route::post('/applications/{application}/enroll', [TenantApplicationController::class, 'enroll'])->name('applications.enroll');
        });
        Route::middleware('permission:admissions.application.view')->group(function () {
            Route::get('/applications/{application}', [TenantApplicationController::class, 'show'])->name('applications.show');
        });

        // Phase 06 — Teacher, Employee & HR
        Route::middleware('permission:hr.staff.view')->group(function () {
            Route::get('/staff', [TenantStaffController::class, 'index'])->name('staff.index');
            Route::get('/staff/{staff}', [TenantStaffController::class, 'show'])->name('staff.show');
        });
        Route::middleware('permission:hr.staff.create')->group(function () {
            Route::get('/staff-create', [TenantStaffController::class, 'create'])->name('staff.create');
            Route::post('/staff', [TenantStaffController::class, 'store'])->name('staff.store');
        });
        Route::middleware('permission:hr.staff.update')->group(function () {
            Route::get('/staff/{staff}/edit', [TenantStaffController::class, 'edit'])->name('staff.edit');
            Route::put('/staff/{staff}', [TenantStaffController::class, 'update'])->name('staff.update');
        });
        Route::middleware('permission:hr.document.manage')->group(function () {
            Route::post('/staff/{staff}/documents', [TenantStaffDocumentController::class, 'store'])->name('staff.documents.store');
            Route::delete('/staff/{staff}/documents/{document}', [TenantStaffDocumentController::class, 'destroy'])->name('staff.documents.destroy');
        });

        Route::middleware('permission:hr.teacher.view')->group(function () {
            Route::get('/teachers', [TenantTeacherController::class, 'index'])->name('teachers.index');
        });
        Route::middleware('permission:hr.assignment.manage')->group(function () {
            Route::get('/assignments', [TenantTeacherAssignmentController::class, 'index'])->name('assignments.index');
            Route::get('/assignments/create', [TenantTeacherAssignmentController::class, 'create'])->name('assignments.create');
            Route::post('/assignments', [TenantTeacherAssignmentController::class, 'store'])->name('assignments.store');
            Route::delete('/assignments/{assignment}', [TenantTeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');
        });

        Route::middleware('permission:hr.designation.manage')->group(function () {
            Route::get('/designations', [TenantDesignationController::class, 'index'])->name('designations.index');
            Route::get('/designations/create', [TenantDesignationController::class, 'create'])->name('designations.create');
            Route::post('/designations', [TenantDesignationController::class, 'store'])->name('designations.store');
            Route::get('/designations/{designation}/edit', [TenantDesignationController::class, 'edit'])->name('designations.edit');
            Route::put('/designations/{designation}', [TenantDesignationController::class, 'update'])->name('designations.update');
        });

        Route::middleware('permission:hr.attendance.view')->group(function () {
            Route::get('/staff-attendance', [TenantStaffAttendanceController::class, 'index'])->name('staff-attendance.index');
        });
        Route::middleware('permission:hr.attendance.manage')->group(function () {
            Route::post('/staff-attendance', [TenantStaffAttendanceController::class, 'store'])->name('staff-attendance.store');
        });

        Route::middleware('permission:hr.leave.view')->group(function () {
            Route::get('/leave-types', [TenantLeaveTypeController::class, 'index'])->name('leave-types.index');
            Route::get('/leave-requests', [TenantLeaveRequestController::class, 'index'])->name('leave-requests.index');
            Route::get('/leave-requests/{leaveRequest}', [TenantLeaveRequestController::class, 'show'])->name('leave-requests.show');
        });
        Route::middleware('permission:hr.leave.manage')->group(function () {
            Route::get('/leave-types/create', [TenantLeaveTypeController::class, 'create'])->name('leave-types.create');
            Route::post('/leave-types', [TenantLeaveTypeController::class, 'store'])->name('leave-types.store');
            Route::get('/leave-types/{leaveType}/edit', [TenantLeaveTypeController::class, 'edit'])->name('leave-types.edit');
            Route::put('/leave-types/{leaveType}', [TenantLeaveTypeController::class, 'update'])->name('leave-types.update');
            Route::get('/leave-requests-create', [TenantLeaveRequestController::class, 'create'])->name('leave-requests.create');
            Route::post('/leave-requests', [TenantLeaveRequestController::class, 'store'])->name('leave-requests.store');
        });
        Route::middleware('permission:hr.leave.approve')->group(function () {
            Route::post('/leave-requests/{leaveRequest}/approve', [TenantLeaveRequestController::class, 'approve'])->name('leave-requests.approve');
            Route::post('/leave-requests/{leaveRequest}/reject', [TenantLeaveRequestController::class, 'reject'])->name('leave-requests.reject');
        });

        Route::middleware('permission:hr.salary.view')->group(function () {
            Route::get('/salary', [TenantSalaryStructureController::class, 'index'])->name('salary.index');
        });
        Route::middleware('permission:hr.salary.manage')->group(function () {
            Route::get('/salary/create', [TenantSalaryStructureController::class, 'create'])->name('salary.create');
            Route::post('/salary', [TenantSalaryStructureController::class, 'store'])->name('salary.store');
            Route::get('/salary/{salary}/edit', [TenantSalaryStructureController::class, 'edit'])->name('salary.edit');
            Route::put('/salary/{salary}', [TenantSalaryStructureController::class, 'update'])->name('salary.update');
            Route::get('/salary-assign', [TenantSalaryStructureController::class, 'assignForm'])->name('salary.assign');
            Route::post('/salary-assign', [TenantSalaryStructureController::class, 'assign'])->name('salary.assign.store');
        });

        Route::middleware('permission:hr.payroll.view')->group(function () {
            Route::get('/payroll', [TenantPayrollController::class, 'index'])->name('payroll.index');
            Route::get('/payroll/{payroll}', [TenantPayrollController::class, 'show'])->name('payroll.show');
        });
        Route::middleware('permission:hr.payroll.manage')->group(function () {
            Route::get('/payroll-create', [TenantPayrollController::class, 'create'])->name('payroll.create');
            Route::post('/payroll', [TenantPayrollController::class, 'store'])->name('payroll.store');
            Route::post('/payroll/{payroll}/process', [TenantPayrollController::class, 'process'])->name('payroll.process');
            Route::post('/payroll/{payroll}/paid', [TenantPayrollController::class, 'markPaid'])->name('payroll.paid');
        });

        // Phase 07 — Academic Management
        Route::middleware('permission:academics.room.view')->group(function () {
            Route::get('/rooms', [TenantRoomController::class, 'index'])->name('rooms.index');
        });
        Route::middleware('permission:academics.room.manage')->group(function () {
            Route::get('/rooms/create', [TenantRoomController::class, 'create'])->name('rooms.create');
            Route::post('/rooms', [TenantRoomController::class, 'store'])->name('rooms.store');
            Route::get('/rooms/{room}/edit', [TenantRoomController::class, 'edit'])->name('rooms.edit');
            Route::put('/rooms/{room}', [TenantRoomController::class, 'update'])->name('rooms.update');
            Route::delete('/rooms/{room}', [TenantRoomController::class, 'destroy'])->name('rooms.destroy');
        });

        Route::middleware('permission:academics.period.view')->group(function () {
            Route::get('/periods', [TenantPeriodController::class, 'index'])->name('periods.index');
        });
        Route::middleware('permission:academics.period.manage')->group(function () {
            Route::get('/periods/create', [TenantPeriodController::class, 'create'])->name('periods.create');
            Route::post('/periods', [TenantPeriodController::class, 'store'])->name('periods.store');
            Route::get('/periods/{period}/edit', [TenantPeriodController::class, 'edit'])->name('periods.edit');
            Route::put('/periods/{period}', [TenantPeriodController::class, 'update'])->name('periods.update');
            Route::delete('/periods/{period}', [TenantPeriodController::class, 'destroy'])->name('periods.destroy');
        });

        Route::middleware('permission:academics.routine.view')->group(function () {
            Route::get('/routines', [TenantRoutineController::class, 'index'])->name('routines.index');
            Route::get('/teacher-routines', [TenantTeacherRoutineController::class, 'index'])->name('teacher-routines.index');
        });
        Route::middleware('permission:academics.routine.manage')->group(function () {
            Route::get('/routines/create', [TenantRoutineController::class, 'create'])->name('routines.create');
            Route::post('/routines', [TenantRoutineController::class, 'store'])->name('routines.store');
            Route::delete('/routines/{routine}', [TenantRoutineController::class, 'destroy'])->name('routines.destroy');
        });

        Route::middleware('permission:academics.subject.view')->group(function () {
            Route::get('/subject-offerings', [TenantSubjectOfferingController::class, 'index'])->name('subject-offerings.index');
        });
        Route::middleware('permission:academics.subject.manage')->group(function () {
            Route::get('/subject-offerings/create', [TenantSubjectOfferingController::class, 'create'])->name('subject-offerings.create');
            Route::post('/subject-offerings', [TenantSubjectOfferingController::class, 'store'])->name('subject-offerings.store');
            Route::put('/subject-offerings/{offering}', [TenantSubjectOfferingController::class, 'update'])->name('subject-offerings.update');
            Route::delete('/subject-offerings/{offering}', [TenantSubjectOfferingController::class, 'destroy'])->name('subject-offerings.destroy');
        });

        Route::middleware('permission:academics.syllabus.view')->group(function () {
            Route::get('/syllabi', [TenantSyllabusController::class, 'index'])->name('syllabi.index');
            Route::get('/syllabi/{syllabus}', [TenantSyllabusController::class, 'show'])->name('syllabi.show');
        });
        Route::middleware('permission:academics.syllabus.manage')->group(function () {
            Route::get('/syllabi-create', [TenantSyllabusController::class, 'create'])->name('syllabi.create');
            Route::post('/syllabi', [TenantSyllabusController::class, 'store'])->name('syllabi.store');
            Route::get('/syllabi/{syllabus}/edit', [TenantSyllabusController::class, 'edit'])->name('syllabi.edit');
            Route::put('/syllabi/{syllabus}', [TenantSyllabusController::class, 'update'])->name('syllabi.update');
            Route::delete('/syllabi/{syllabus}', [TenantSyllabusController::class, 'destroy'])->name('syllabi.destroy');
            Route::post('/syllabi/{syllabus}/topics', [TenantSyllabusController::class, 'storeTopic'])->name('syllabi.topics.store');
            Route::delete('/syllabi/{syllabus}/topics/{topic}', [TenantSyllabusController::class, 'destroyTopic'])->name('syllabi.topics.destroy');
        });

        Route::middleware('permission:academics.lesson_plan.view')->group(function () {
            Route::get('/lesson-plans', [TenantLessonPlanController::class, 'index'])->name('lesson-plans.index');
        });
        Route::middleware('permission:academics.lesson_plan.manage')->group(function () {
            Route::get('/lesson-plans/create', [TenantLessonPlanController::class, 'create'])->name('lesson-plans.create');
            Route::post('/lesson-plans', [TenantLessonPlanController::class, 'store'])->name('lesson-plans.store');
            Route::get('/lesson-plans/{lesson_plan}/edit', [TenantLessonPlanController::class, 'edit'])->name('lesson-plans.edit');
            Route::put('/lesson-plans/{lesson_plan}', [TenantLessonPlanController::class, 'update'])->name('lesson-plans.update');
            Route::delete('/lesson-plans/{lesson_plan}', [TenantLessonPlanController::class, 'destroy'])->name('lesson-plans.destroy');
        });

        Route::middleware('permission:academics.homework.view')->group(function () {
            Route::get('/homework', [TenantHomeworkController::class, 'index'])->name('homework.index');
        });
        Route::middleware('permission:academics.homework.manage')->group(function () {
            Route::get('/homework/create', [TenantHomeworkController::class, 'create'])->name('homework.create');
            Route::post('/homework', [TenantHomeworkController::class, 'store'])->name('homework.store');
            Route::get('/homework/{homework}/edit', [TenantHomeworkController::class, 'edit'])->name('homework.edit');
            Route::put('/homework/{homework}', [TenantHomeworkController::class, 'update'])->name('homework.update');
            Route::delete('/homework/{homework}', [TenantHomeworkController::class, 'destroy'])->name('homework.destroy');
        });

        Route::middleware('permission:academics.classwork.view')->group(function () {
            Route::get('/classwork', [TenantClassworkController::class, 'index'])->name('classwork.index');
        });
        Route::middleware('permission:academics.classwork.manage')->group(function () {
            Route::get('/classwork/create', [TenantClassworkController::class, 'create'])->name('classwork.create');
            Route::post('/classwork', [TenantClassworkController::class, 'store'])->name('classwork.store');
            Route::get('/classwork/{classwork}/edit', [TenantClassworkController::class, 'edit'])->name('classwork.edit');
            Route::put('/classwork/{classwork}', [TenantClassworkController::class, 'update'])->name('classwork.update');
            Route::delete('/classwork/{classwork}', [TenantClassworkController::class, 'destroy'])->name('classwork.destroy');
        });

        Route::middleware('permission:academics.material.view')->group(function () {
            Route::get('/materials', [TenantLearningMaterialController::class, 'index'])->name('materials.index');
        });
        Route::middleware('permission:academics.material.manage')->group(function () {
            Route::get('/materials/create', [TenantLearningMaterialController::class, 'create'])->name('materials.create');
            Route::post('/materials', [TenantLearningMaterialController::class, 'store'])->name('materials.store');
            Route::delete('/materials/{material}', [TenantLearningMaterialController::class, 'destroy'])->name('materials.destroy');
        });

        Route::middleware('permission:academics.report.view')->group(function () {
            Route::get('/academic-reports', [TenantAcademicReportController::class, 'index'])->name('academic-reports.index');
        });

        // Attendance (Phase 08)
        Route::middleware('permission:attendance.daily.view')->group(function () {
            Route::get('/attendance/daily', [TenantDailyAttendanceController::class, 'index'])->name('attendance.daily.index');
        });
        Route::middleware('permission:attendance.daily.manage')->group(function () {
            Route::post('/attendance/daily', [TenantDailyAttendanceController::class, 'store'])->name('attendance.daily.store');
        });
        Route::middleware('permission:attendance.period.view')->group(function () {
            Route::get('/attendance/period', [TenantPeriodAttendanceController::class, 'index'])->name('attendance.period.index');
        });
        Route::middleware('permission:attendance.period.manage')->group(function () {
            Route::post('/attendance/period', [TenantPeriodAttendanceController::class, 'store'])->name('attendance.period.store');
        });
        Route::middleware('permission:attendance.report.view')->group(function () {
            Route::get('/attendance/reports', [TenantAttendanceReportController::class, 'index'])->name('attendance.reports.index');
        });
        Route::middleware('permission:attendance.qr.manage')->group(function () {
            Route::get('/attendance/qr', [TenantAttendanceQrController::class, 'index'])->name('attendance.qr.index');
            Route::post('/attendance/qr', [TenantAttendanceQrController::class, 'store'])->name('attendance.qr.store');
        });

        // Examinations (Phase 09)
        Route::middleware('permission:exams.exam.view')->group(function () {
            Route::get('/exams', [TenantExamController::class, 'index'])->name('exams.index');
        });
        Route::middleware('permission:exams.exam.manage')->group(function () {
            Route::get('/exams/create', [TenantExamController::class, 'create'])->name('exams.create');
            Route::post('/exams', [TenantExamController::class, 'store'])->name('exams.store');
            Route::get('/exams/{exam}/edit', [TenantExamController::class, 'edit'])->name('exams.edit');
            Route::put('/exams/{exam}', [TenantExamController::class, 'update'])->name('exams.update');
            Route::delete('/exams/{exam}', [TenantExamController::class, 'destroy'])->name('exams.destroy');
        });
        Route::middleware('permission:exams.exam.view')->group(function () {
            Route::get('/exams/{exam}', [TenantExamController::class, 'show'])->name('exams.show');
        });
        Route::middleware('permission:exams.subject.manage')->group(function () {
            Route::post('/exams/{exam}/subjects', [TenantExamSubjectController::class, 'store'])->name('exams.subjects.store');
            Route::put('/exams/{exam}/subjects/{examSubject}', [TenantExamSubjectController::class, 'update'])->name('exams.subjects.update');
            Route::delete('/exams/{exam}/subjects/{examSubject}', [TenantExamSubjectController::class, 'destroy'])->name('exams.subjects.destroy');
        });
        Route::middleware('permission:exams.marks.view')->group(function () {
            Route::get('/marks', [TenantMarksEntryController::class, 'index'])->name('marks.index');
        });
        Route::middleware('permission:exams.marks.manage')->group(function () {
            Route::post('/marks', [TenantMarksEntryController::class, 'store'])->name('marks.store');
        });
        Route::middleware('permission:exams.exam.view')->group(function () {
            Route::get('/grading-schemes', [TenantGradingSchemeController::class, 'index'])->name('grading-schemes.index');
        });
        Route::middleware('permission:exams.exam.manage')->group(function () {
            Route::post('/grading-schemes', [TenantGradingSchemeController::class, 'store'])->name('grading-schemes.store');
            Route::delete('/grading-schemes/{scheme}', [TenantGradingSchemeController::class, 'destroy'])->name('grading-schemes.destroy');
        });
        Route::middleware('permission:exams.publish.manage')->group(function () {
            Route::get('/results/publish', [TenantResultPublishController::class, 'index'])->name('results.publish.index');
            Route::post('/results/publish', [TenantResultPublishController::class, 'store'])->name('results.publish.store');
        });
        Route::middleware('permission:exams.pdf.export')->group(function () {
            Route::get('/results/pdf', [TenantMarksheetPdfController::class, 'index'])->name('results.pdf.index');
            Route::get('/results/pdf/{exam}/marksheet/{student}', [TenantMarksheetPdfController::class, 'studentMarksheet'])->name('results.pdf.marksheet');
            Route::get('/results/pdf/{exam}/class-sheet', [TenantMarksheetPdfController::class, 'classSheet'])->name('results.pdf.class');
        });

        Route::middleware('permission:settings.domains.view')->group(function () {
            Route::get('/domains', [TenantDomainController::class, 'index'])->name('domains.index');
        });
        Route::middleware('permission:settings.domains.update')->group(function () {
            Route::post('/domains', [TenantDomainController::class, 'store'])->name('domains.store');
            Route::post('/domains/{domain}/stub-verify', [TenantDomainController::class, 'stubVerify'])->name('domains.stub-verify');
        });

        Route::middleware('permission:settings.subscription.view')->group(function () {
            Route::get('/subscription', [TenantSubscriptionController::class, 'show'])->name('subscription.show');
        });
        Route::middleware('permission:settings.modules.view')->group(function () {
            Route::get('/modules', [TenantModuleController::class, 'index'])->name('modules.index');
        });
        Route::middleware('permission:settings.usage.view')->group(function () {
            Route::get('/usage', [TenantUsageController::class, 'index'])->name('usage.index');
        });
        Route::middleware('permission:settings.usage.update')->group(function () {
            Route::put('/usage/stub', [TenantUsageController::class, 'updateStub'])->name('usage.stub');
        });

        // Identity / RBAC
        Route::middleware('permission:settings.users.view')->group(function () {
            Route::get('/users', [TenantUserController::class, 'index'])->name('users.index');
        });
        Route::middleware('permission:settings.users.create')->group(function () {
            Route::get('/users/create', [TenantUserController::class, 'create'])->name('users.create');
            Route::post('/users', [TenantUserController::class, 'store'])->name('users.store');
        });
        Route::middleware('permission:settings.users.update')->group(function () {
            Route::get('/users/{user}/edit', [TenantUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [TenantUserController::class, 'update'])->name('users.update');
        });
        Route::middleware('permission:settings.users.delete')->group(function () {
            Route::delete('/users/{user}', [TenantUserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:settings.roles.view')->group(function () {
            Route::get('/roles', [TenantRoleController::class, 'index'])->name('roles.index');
        });
        Route::middleware('permission:settings.roles.create')->group(function () {
            Route::get('/roles/create', [TenantRoleController::class, 'create'])->name('roles.create');
            Route::post('/roles', [TenantRoleController::class, 'store'])->name('roles.store');
        });
        Route::middleware('permission:settings.roles.update')->group(function () {
            Route::get('/roles/{role}/edit', [TenantRoleController::class, 'edit'])->name('roles.edit');
            Route::put('/roles/{role}', [TenantRoleController::class, 'update'])->name('roles.update');
        });
        Route::middleware('permission:settings.roles.delete')->group(function () {
            Route::delete('/roles/{role}', [TenantRoleController::class, 'destroy'])->name('roles.destroy');
        });

        Route::get('/permissions', [TenantPermissionController::class, 'index'])->name('permissions.index');

        Route::middleware('permission:settings.memberships.view')->group(function () {
            Route::get('/memberships', [TenantMembershipController::class, 'index'])->name('memberships.index');
        });
        Route::middleware('permission:settings.memberships.update')->group(function () {
            Route::patch('/memberships/{membership}', [TenantMembershipController::class, 'update'])->name('memberships.update');
        });
        Route::middleware('permission:settings.memberships.delete')->group(function () {
            Route::delete('/memberships/{membership}', [TenantMembershipController::class, 'destroy'])->name('memberships.destroy');
        });

        Route::middleware('permission:settings.audit.view')->group(function () {
            Route::get('/audit', [TenantAuditLogController::class, 'index'])->name('audit.index');
        });
        Route::middleware('permission:settings.login_history.view')->group(function () {
            Route::get('/login-history', [TenantLoginHistoryController::class, 'index'])->name('login-history.index');
        });
        Route::middleware('permission:settings.sessions.view')->group(function () {
            Route::get('/sessions', [TenantSessionController::class, 'index'])->name('sessions.index');
        });
        Route::middleware('permission:settings.sessions.revoke')->group(function () {
            Route::delete('/sessions/others', [TenantSessionController::class, 'destroyOthers'])->name('sessions.destroy-others');
            Route::delete('/sessions/{session}', [TenantSessionController::class, 'destroy'])->name('sessions.destroy');
        });
    });

// Back-compat alias used by Phase 01 tests / bookmarks / Try Live
Route::get('/dashboard', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->is_platform_admin
        ? redirect()->route('platform.dashboard')
        : redirect()->route('tenant.dashboard');
})->name('dashboard');
