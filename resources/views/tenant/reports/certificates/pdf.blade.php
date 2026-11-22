<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 24px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .frame {
            border: 3px solid #4f46e5;
            border-radius: 8px;
            padding: 28px 36px;
            min-height: 480px;
            background: #f8fafc;
            position: relative;
        }
        .inner {
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            padding: 28px 32px;
            background: #ffffff;
            min-height: 420px;
        }
        .org {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 12px;
            color: #475569;
            margin: 0 0 8px;
        }
        .title {
            text-align: center;
            font-size: 28px;
            color: #312e81;
            margin: 0 0 18px;
        }
        .body {
            font-size: 14px;
            line-height: 1.7;
            text-align: center;
            white-space: pre-wrap;
            margin: 24px auto;
            max-width: 720px;
        }
        .meta {
            margin-top: 28px;
            font-size: 11px;
            color: #64748b;
            text-align: center;
        }
        .footer {
            margin-top: 48px;
            width: 100%;
        }
        .footer td {
            width: 33%;
            text-align: center;
            vertical-align: bottom;
            font-size: 11px;
            color: #475569;
        }
        .line {
            border-top: 1px solid #94a3b8;
            margin: 0 auto 6px;
            width: 140px;
        }
        .qr {
            width: 72px;
            height: 72px;
        }
        .seal {
            display: inline-block;
            width: 70px;
            height: 70px;
            border: 2px dashed #6366f1;
            border-radius: 50%;
            line-height: 70px;
            color: #6366f1;
            font-size: 10px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="frame">
    <div class="inner">
        <p class="org">{{ $institution?->name ?? config('app.name') }}</p>
        <h1 class="title">{{ $title }}</h1>
        <div class="body">{{ $body }}</div>
        <p class="meta">
            {{ __('ui.issue_number') }}: {{ $issue->issue_number }}
            @if (!empty($placeholders['issue_date']))
                · {{ __('ui.issued_at') }}: {{ $placeholders['issue_date'] }}
            @endif
        </p>
        <table class="footer">
            <tr>
                <td>
                    <div class="seal">{{ $template->seal_label ?: __('ui.seal') }}</div>
                    <div>{{ $template->seal_label ?: __('ui.seal') }}</div>
                </td>
                <td>
                    @if ($qrDataUri)
                        <img class="qr" src="{{ $qrDataUri }}" alt="QR">
                        <div>QR</div>
                    @endif
                </td>
                <td>
                    <div class="line"></div>
                    <div>{{ $template->signature_label ?: __('ui.id_card_signature') }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
