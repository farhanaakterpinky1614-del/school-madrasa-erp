<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Reports\Models\CertificateTemplate;

class CertificateTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $templates = CertificateTemplate::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('code', 'like', '%'.$q.'%')
                        ->orWhere('title', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('certificate_type')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.reports.certificates.templates', [
            'templates' => $templates,
            'filterQ' => $q,
            'certificateTypes' => CertificateTemplate::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CertificateTemplate::query()->create($this->payload($request));

        return back()->with('status', __('ui.certificate_template_saved'));
    }

    public function update(Request $request, CertificateTemplate $template): RedirectResponse
    {
        $this->assertTenantOwned($template);
        $template->update($this->payload($request, $template));

        return back()->with('status', __('ui.certificate_template_saved'));
    }

    public function destroy(CertificateTemplate $template): RedirectResponse
    {
        $this->assertTenantOwned($template);
        $template->delete();

        return back()->with('status', __('ui.certificate_template_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?CertificateTemplate $existing = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:64',
                Rule::unique('certificate_templates', 'code')
                    ->where(fn ($q) => $q->where('tenant_id', app(\App\Core\Tenancy\CurrentTenant::class)->id()))
                    ->ignore($existing?->id),
            ],
            'certificate_type' => ['required', Rule::in(CertificateTemplate::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'seal_label' => ['nullable', 'string', 'max:255'],
            'signature_label' => ['nullable', 'string', 'max:255'],
            'show_qr' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'placeholders_help' => ['nullable', 'string'],
        ]);

        $data['show_qr'] = $request->boolean('show_qr');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['code'] = strtoupper($data['code']);

        return $data;
    }
}
