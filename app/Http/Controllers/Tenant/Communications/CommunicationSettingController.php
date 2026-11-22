<?php

namespace App\Http\Controllers\Tenant\Communications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Communications\Concerns\LoadsCommunicationsFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Communications\Models\CommunicationSetting;
use Modules\Communications\Services\GatewayManager;

class CommunicationSettingController extends Controller
{
    use LoadsCommunicationsFormOptions;

    public function __construct(protected GatewayManager $gateways) {}

    public function edit(): View
    {
        return view('tenant.communications.settings.edit', array_merge($this->communicationsFormOptions(), [
            'settings' => $this->gateways->settings(),
        ]));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = $this->gateways->settings();
        $this->assertTenantOwned($settings);

        $data = $request->validate([
            'sms_driver' => ['required', Rule::in(CommunicationSetting::SMS_DRIVERS)],
            'email_driver' => ['required', Rule::in(CommunicationSetting::EMAIL_DRIVERS)],
            'sms_enabled' => ['nullable', 'boolean'],
            'email_enabled' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $settings->update([
            'sms_driver' => $data['sms_driver'],
            'email_driver' => $data['email_driver'],
            'sms_enabled' => $request->boolean('sms_enabled'),
            'email_enabled' => $request->boolean('email_enabled'),
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', __('ui.communication_settings_saved'));
    }
}
