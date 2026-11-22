<?php

namespace App\Http\Controllers\Tenant\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\ExpenseCategory;

class ExpenseReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->string('from')->toString() ?: now('Asia/Dhaka')->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: now('Asia/Dhaka')->toDateString();

        $byCategory = Expense::query()
            ->select('expense_category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as entries'))
            ->whereDate('spent_on', '>=', $from)
            ->whereDate('spent_on', '<=', $to)
            ->groupBy('expense_category_id')
            ->get()
            ->load('category');

        $grandTotal = Expense::query()
            ->whereDate('spent_on', '>=', $from)
            ->whereDate('spent_on', '<=', $to)
            ->sum('amount');

        return view('tenant.finance.reports.index', [
            'from' => $from,
            'to' => $to,
            'byCategory' => $byCategory,
            'grandTotal' => $grandTotal,
            'categories' => ExpenseCategory::query()->orderBy('name')->get(),
        ]);
    }
}
