<?php

namespace App\Http\Controllers\Tenant\Communications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Communications\Concerns\LoadsCommunicationsFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Communications\Models\NotificationTemplate;

class NotificationTemplateController extends Controller
{
    use LoadsCommunicationsFormOptions;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $channel = (string) $request->query('channel', '');

        $templates = NotificationTemplate::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('code', 'like', '%'.$q.'%')
                        ->orWhere('body', 'like', '%'.$q.'%');
                });
            })
            ->when($channel !== '' && in_array($channel, NotificationTemplate::CHANNELS, true), fn ($query) => $query->where('channel', $channel))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.communications.templates.index', array_merge($this->communicationsFormOptions(), [
            'notificationTemplates' => $templates,
            'filterQ' => $q,
            'filterChannel' => $channel,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        NotificationTemplate::query()->create($this->payload($request));

        return back()->with('status', __('ui.notification_template_saved'));
    }

    public function update(Request $request, NotificationTemplate $template): RedirectResponse
    {
        $this->assertTenantOwned($template);
        $template->update($this->payload($request, $template));

        return back()->with('status', __('ui.notification_template_saved'));
    }

    public function destroy(NotificationTemplate $template): RedirectResponse
    {
        $this->assertTenantOwned($template);
        abort_if($template->logs()->exists(), 422);
        $template->delete();

        return back()->with('status', __('ui.notification_template_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?NotificationTemplate $existing = null): array
    {
        $tenantId = app(\App\Core\Tenancy\CurrentTenant::class)->id();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:64',
                Rule::unique('notification_templates', 'code')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId))
                    ->ignore($existing?->id),
            ],
            'channel' => ['required', Rule::in(NotificationTemplate::CHANNELS)],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'placeholders_help' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['channel'] === NotificationTemplate::CHANNEL_SMS) {
            $data['subject'] = null;
        }

        return $data;
    }
}
