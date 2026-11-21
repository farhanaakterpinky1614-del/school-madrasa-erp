<?php

namespace App\Http\Controllers\Tenant\Fees;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Fees\Concerns\LoadsFeeFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Fees\Models\FeeCategory;

class FeeCategoryController extends Controller
{
    use LoadsFeeFormOptions;

    public function index(): View
    {
        return view('tenant.fees.categories.index', array_merge($this->feeFormOptions(), [
            'categories' => FeeCategory::query()->orderBy('name')->paginate(20),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        FeeCategory::query()->create($this->payload($request));

        return back()->with('status', __('ui.fee_category_saved'));
    }

    public function update(Request $request, FeeCategory $category): RedirectResponse
    {
        $this->assertTenantOwned($category);
        $category->update($this->payload($request, $category));

        return back()->with('status', __('ui.fee_category_saved'));
    }

    public function destroy(FeeCategory $category): RedirectResponse
    {
        $this->assertTenantOwned($category);
        abort_if($category->structures()->exists(), 422);
        $category->delete();

        return back()->with('status', __('ui.fee_category_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?FeeCategory $existing = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:64',
                Rule::unique('fee_categories', 'code')
                    ->where(fn ($q) => $q->where('tenant_id', app(\App\Core\Tenancy\CurrentTenant::class)->id()))
                    ->ignore($existing?->id),
            ],
            'kind' => ['required', Rule::in(FeeCategory::KINDS)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
