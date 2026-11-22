@extends('layouts.app')

@section('title', __('ui.staff_id_card'))

@php
    $primary = $template?->primary_color ?: ($institution?->primary_color ?: '#4F46E5');
    $secondary = $template?->secondary_color ?: ($institution?->secondary_color ?: '#0F172A');
    $showPhoto = $template?->show_photo ?? true;
    $showQr = $template?->show_qr ?? true;
    $footerText = $template?->footer_text ?: __('ui.id_card_signature');
    $roleLabel = $staff->designation?->name ?? ($staff->is_teacher ? __('ui.teacher') : __('ui.staff'));
    $validThrough = now('Asia/Dhaka')->addYear()->format('M Y');
@endphp

@push('head')
<style>
    .sme-id-card {
        --id-primary: {{ $primary }};
        --id-secondary: {{ $secondary }};
        width: 85.6mm; height: 53.98mm; position: relative; overflow: hidden; border-radius: 3.2mm;
        background: linear-gradient(135deg, rgba(255,255,255,0.97) 0%, rgba(248,250,252,0.98) 55%, rgba(238,242,255,0.95) 100%);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(15, 23, 42, 0.08);
        color: #0f172a; font-family: "Noto Sans", "Noto Sans Bengali", ui-sans-serif, system-ui, sans-serif;
    }
    .sme-id-card::before { content:""; position:absolute; inset:0; background-image: repeating-linear-gradient(-28deg, rgba(79,70,229,0.035) 0, rgba(79,70,229,0.035) 1px, transparent 1px, transparent 7px); pointer-events:none; z-index:0; }
    .sme-id-card > * { position: relative; z-index: 1; }
    .sme-id-header { display:flex; align-items:center; gap:2.4mm; min-height:12.5mm; padding:2.2mm 3.2mm; background:linear-gradient(105deg, var(--id-secondary) 0%, #1e293b 48%, var(--id-primary) 100%); color:#fff; }
    .sme-id-mark { width:7.5mm; height:7.5mm; border-radius:1.6mm; display:grid; place-items:center; background:rgba(255,255,255,0.14); border:0.25mm solid rgba(255,255,255,0.28); font-size:3.2mm; font-weight:700; flex-shrink:0; }
    .sme-id-org { min-width:0; flex:1; }
    .sme-id-org-name { margin:0; font-size:3.4mm; font-weight:700; text-transform:uppercase; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sme-id-org-meta { margin:0.6mm 0 0; font-size:2.1mm; opacity:0.82; letter-spacing:0.08em; text-transform:uppercase; }
    .sme-id-badge { flex-shrink:0; padding:0.8mm 1.8mm; border-radius:999px; border:0.25mm solid rgba(255,255,255,0.35); background:rgba(255,255,255,0.1); font-size:1.9mm; font-weight:650; letter-spacing:0.12em; text-transform:uppercase; }
    .sme-id-body { display:grid; grid-template-columns:18.5mm 1fr 16.5mm; gap:2.6mm; padding:2.6mm 3.2mm 2.2mm; height:calc(53.98mm - 12.5mm - 2.2mm); box-sizing:border-box; }
    .sme-id-photo, .sme-id-photo-ph { width:18.5mm; height:23mm; border-radius:1.4mm; }
    .sme-id-photo { object-fit:cover; background:#e2e8f0; border:0.35mm solid rgba(15,23,42,0.18); }
    .sme-id-photo-ph { border:0.35mm dashed rgba(100,116,139,0.55); background:linear-gradient(180deg,#f8fafc,#e2e8f0); display:grid; place-items:center; text-align:center; color:#64748b; font-size:2mm; font-weight:650; letter-spacing:0.08em; text-transform:uppercase; padding:1mm; box-sizing:border-box; }
    .sme-id-fields { min-width:0; display:flex; flex-direction:column; gap:1.1mm; }
    .sme-id-label { margin:0; font-size:1.8mm; font-weight:650; letter-spacing:0.14em; text-transform:uppercase; color:#64748b; }
    .sme-id-name { margin:0; font-size:4.1mm; font-weight:750; line-height:1.1; color:#0f172a; }
    .sme-id-code { margin:0.4mm 0 0; font-family:ui-monospace,monospace; font-size:2.6mm; font-weight:700; color:var(--id-primary); }
    .sme-id-meta { display:grid; grid-template-columns:1fr 1fr; gap:1.2mm 2mm; margin-top:0.6mm; }
    .sme-id-meta dt { margin:0; font-size:1.7mm; font-weight:650; letter-spacing:0.1em; text-transform:uppercase; color:#64748b; }
    .sme-id-meta dd { margin:0.3mm 0 0; font-size:2.5mm; font-weight:650; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sme-id-qr-wrap { display:flex; flex-direction:column; align-items:center; gap:1mm; }
    .sme-id-qr { width:15.5mm; height:15.5mm; border-radius:1.2mm; background:#fff; border:0.3mm solid rgba(15,23,42,0.12); padding:0.7mm; box-sizing:border-box; object-fit:contain; }
    .sme-id-qr-caption { margin:0; font-size:1.6mm; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; font-weight:650; }
    .sme-id-foot { position:absolute; left:3.2mm; right:3.2mm; bottom:2mm; display:grid; grid-template-columns:1.3fr 1fr; gap:3mm; align-items:end; }
    .sme-id-sign-line { border-top:0.25mm solid rgba(100,116,139,0.55); padding-top:0.7mm; font-size:1.7mm; letter-spacing:0.08em; text-transform:uppercase; color:#64748b; font-weight:600; }
    .sme-id-valid { text-align:right; font-size:1.7mm; letter-spacing:0.08em; text-transform:uppercase; color:#64748b; font-weight:600; }
    .sme-id-valid strong { display:block; margin-top:0.4mm; color:#0f172a; font-size:2.2mm; text-transform:none; }
    .sme-id-strip { position:absolute; left:0; right:0; bottom:0; height:1.1mm; background:linear-gradient(90deg, var(--id-primary), #6366f1 45%, var(--id-secondary)); }
    .sme-id-stage { display:flex; justify-content:center; padding:8px 0 4px; }
    @media print {
        @page { size: 85.6mm 53.98mm; margin: 0; }
        html, body { background:#fff !important; -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; }
        .sme-id-screen-only { display:none !important; }
        .sme-id-stage { padding:0 !important; margin:0 !important; }
        .sme-id-card { box-shadow:none !important; border-radius:0 !important; }
    }
</style>
@endpush

@section('page-header')
    <div class="sme-id-screen-only">
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.staff_id_card') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.staff_id_cards_help') }}</p>
    </div>
    <div class="sme-id-screen-only flex flex-wrap gap-2">
        <button type="button" onclick="window.print()"><x-ui.button type="button">{{ __('ui.print') }}</x-ui.button></button>
        <a href="{{ route('tenant.reports.id-cards.staff.index') }}"><x-ui.button type="button" variant="outline">{{ __('ui.back') }}</x-ui.button></a>
    </div>
@endsection

@section('content')
    <div class="sme-id-stage">
        <article class="sme-id-card" aria-label="{{ __('ui.staff_id_card') }}">
            <header class="sme-id-header">
                <div class="sme-id-mark" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($institution?->name ?? 'S', 0, 1)) }}</div>
                <div class="sme-id-org">
                    <p class="sme-id-org-name">{{ $institution?->name ?? config('app.name') }}</p>
                    <p class="sme-id-org-meta">{{ collect([$institution?->city, $institution?->eiin ? 'EIIN '.$institution->eiin : null])->filter()->implode(' · ') ?: __('ui.staff_id_card') }}</p>
                </div>
                <div class="sme-id-badge">{{ __('ui.staff_id') }}</div>
            </header>
            <div class="sme-id-body">
                <div>
                    @if ($showPhoto && $staff->photo_path)
                        <img class="sme-id-photo" src="{{ asset('storage/'.$staff->photo_path) }}" alt="{{ $staff->full_name }}" />
                    @else
                        <div class="sme-id-photo-ph">{{ __('ui.photo_placeholder') }}</div>
                    @endif
                </div>
                <div class="sme-id-fields">
                    <p class="sme-id-label">{{ __('ui.col_name') }}</p>
                    <h2 class="sme-id-name">{{ $staff->full_name }}</h2>
                    <p class="sme-id-code">{{ $staff->employee_code }}</p>
                    <dl class="sme-id-meta">
                        <div>
                            <dt>{{ __('ui.role') }}</dt>
                            <dd>{{ $roleLabel }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.department') }}</dt>
                            <dd>{{ $staff->department?->name ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="sme-id-qr-wrap">
                    @if ($showQr)
                        <img class="sme-id-qr" src="{{ $qrDataUri }}" alt="QR" />
                        <p class="sme-id-qr-caption">QR</p>
                    @endif
                </div>
            </div>
            <div class="sme-id-foot">
                <div class="sme-id-sign-line">{{ $footerText }}</div>
                <div class="sme-id-valid">{{ __('ui.id_card_valid_through') }} <strong>{{ $validThrough }}</strong></div>
            </div>
            <div class="sme-id-strip" aria-hidden="true"></div>
        </article>
    </div>
    <p class="sme-id-screen-only mx-auto mt-4 max-w-xl text-xs text-muted">{{ __('ui.verify_stub_hint') }}: {{ $verifyUrl }}</p>
@endsection
