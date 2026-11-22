<?php

namespace App\Http\Controllers\Tenant\Communications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Communications\Services\AnnouncementService;

class InboxController extends Controller
{
    public function __construct(protected AnnouncementService $announcements) {}

    public function index(Request $request): View
    {
        $items = $this->announcements
            ->inboxQueryFor($request->user())
            ->with('schoolClass')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.communications.inbox.index', [
            'items' => $items,
        ]);
    }
}
