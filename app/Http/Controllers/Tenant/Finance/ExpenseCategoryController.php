<?php

namespace App\Http\Controllers\Tenant\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Finance\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        return view('tenant.finance.expense-categories.index', [
            'categories' => ExpenseCategory::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ExpenseCategory::query()->create($this->payload($request));

        return back()->with('status', __('ui.expense_category_saved'));
    }

    public function update(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $this->assertTenantOwned($category);
        $category->update($this->payload($request, $category));

        return back()->with('status', __('ui.expense_category_saved'));
    }

    public function destroy(ExpenseCategory $category): RedirectResponse
    {
        $this->assertTenantOwned($category);
        abort_if($category->expenses()->exists(), 422);
        $category->delete();

        return back()->with('status', __('ui.expense_category_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?ExpenseCategory $existing = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:64',
                Rule::unique('expense_categories', 'code')
                    ->where(fn ($q) => $q->where('tenant_id', app(\App\Core\Tenancy\CurrentTenant::class)->id()))
                    ->ignore($existing?->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
