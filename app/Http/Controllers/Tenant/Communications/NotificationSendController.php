<?php

namespace App\Http\Controllers\Tenant\Communications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Communications\Concerns\LoadsCommunicationsFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Communications\Models\NotificationTemplate;
use Modules\Communications\Services\NotificationSendService;
use RuntimeException;
use Throwable;

class NotificationSendController extends Controller
{
    use LoadsCommunicationsFormOptions;

    public function __construct(protected NotificationSendService $sender) {}

    public function create(): View
    {
        return view('tenant.communications.send.create', $this->communicationsFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notification_template_id' => ['required', 'integer'],
            'recipient' => ['required', 'string', 'max:255'],
            'student_name' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'string', 'max:64'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'extra_placeholders' => ['nullable', 'string'],
        ]);

        $template = NotificationTemplate::query()->findOrFail($data['notification_template_id']);
        $this->assertTenantOwned($template);
        abort_unless($template->is_active, 422);

        $placeholders = array_filter([
            'student_name' => $data['student_name'] ?? null,
            'amount' => $data['amount'] ?? null,
            'institution_name' => $data['institution_name']
                ?? app(\App\Core\Tenancy\CurrentTenant::class)->get()?->name,
        ], fn ($v) => $v !== null && $v !== '');

        if (! empty($data['extra_placeholders'])) {
            foreach (preg_split('/\r\n|\r|\n/', $data['extra_placeholders']) as $line) {
                $line = trim($line);
                if ($line === '' || ! str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = array_map('trim', explode('=', $line, 2));
                if ($key !== '') {
                    $placeholders[$key] = $value;
                }
            }
        }

        try {
            $this->sender->send(
                channel: $template->channel,
                recipient: $data['recipient'],
                template: $template,
                placeholders: $placeholders,
                meta: ['source' => 'compose_ui'],
            );
        } catch (RuntimeException|Throwable $e) {
            return back()->withInput()->withErrors(['recipient' => $e->getMessage()]);
        }

        return redirect()
            ->route('tenant.communications.logs.index')
            ->with('status', __('ui.notification_send_queued'));
    }
}
