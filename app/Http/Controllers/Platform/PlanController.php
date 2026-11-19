<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Platform\Models\Plan;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->with('features')
            ->orderBy('sort_order')
            ->get();

        return view('platform.plans.index', compact('plans'));
    }
}
