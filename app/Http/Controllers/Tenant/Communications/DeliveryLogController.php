<?php

namespace App\Http\Controllers\Tenant\Communications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Communications\Concerns\LoadsCommunicationsFormOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Communications\Models\NotificationLog;

class DeliveryLogController extends Controller
{
    use LoadsCommunicationsFormOptions;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $channel = (string) $request->query('channel', '');

        $logs = NotificationLog::query()
            ->with('template')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('recipient', 'like', '%'.$q.'%')
                        ->orWhere('subject', 'like', '%'.$q.'%')
                        ->orWhere('body', 'like', '%'.$q.'%')
                        ->orWhere('error_message', 'like', '%'.$q.'%');
                });
            })
            ->when($status !== '' && in_array($status, NotificationLog::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->when($channel !== '' && in_array($channel, NotificationLog::CHANNELS, true), fn ($query) => $query->where('channel', $channel))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('tenant.communications.logs.index', array_merge($this->communicationsFormOptions(), [
            'logs' => $logs,
            'filterQ' => $q,
            'filterStatus' => $status,
            'filterChannel' => $channel,
        ]));
    }
}
