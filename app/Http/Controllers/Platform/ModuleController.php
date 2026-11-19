<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Platform\Models\ModuleRegistry;

class ModuleController extends Controller
{
    public function index(): View
    {
        $modules = ModuleRegistry::query()->orderBy('sort_order')->get();

        return view('platform.modules.index', compact('modules'));
    }
}
