<?php

namespace App\Http\Controllers\Tenant\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\ExpenseCategory;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::query()
            ->with('category')
            ->orderByDesc('spent_on')
            ->orderByDesc('id');

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->integer('category_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('spent_on', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('spent_on', '<=', $request->date('to'));
        }

        $periodTotal = (clone $query)->sum('amount');

        return view('tenant.finance.expenses.index', [
            'expenses' => $query->paginate(20)->withQueryString(),
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'methods' => Expense::METHODS,
            'filterCategoryId' => $request->integer('category_id') ?: null,
            'filterFrom' => $request->string('from')->toString(),
            'filterTo' => $request->string('to')->toString(),
            'periodTotal' => $periodTotal,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'expense_category_id' => ['required', 'integer', Rule::exists('expense_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'spent_on' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(Expense::METHODS)],
            'reference' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['recorded_by'] = $request->user()?->id;
        Expense::query()->create($data);

        return back()->with('status', __('ui.expense_saved'));
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->assertTenantOwned($expense);
        $expense->delete();

        return back()->with('status', __('ui.expense_deleted'));
    }
}
