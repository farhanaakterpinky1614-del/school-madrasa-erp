<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Hifz Muhtamim Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
        .header { text-align: center; border-bottom: 2px solid #312e81; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 18px; color: #312e81; }
        .header p { margin: 4px 0; color: #475569; }
        h2 { font-size: 13px; color: #312e81; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #eef2ff; color: #312e81; }
        .meta { margin-bottom: 12px; }
        .footer { margin-top: 18px; font-size: 10px; color: #64748b; }
        .grid td { border: none; padding: 4px 8px 4px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institution?->name ?? 'School & Madrasa ERP' }}</h1>
        <p>Muhtamim Hifz Progress Summary</p>
        <p>Generated {{ $generatedAt->timezone('Asia/Dhaka')->format('d M Y H:i') }} (Asia/Dhaka)</p>
    </div>

    <table class="grid meta">
        <tr>
            <td><strong>Total Hifz students:</strong> {{ $summary['total_profiles'] }}</td>
            <td><strong>Active teachers (ustaz):</strong> {{ $summary['teachers_count'] }}</td>
        </tr>
    </table>

    <h2>Progress bands (juz / para)</h2>
    <table>
        <thead>
            <tr>
                <th>Band</th>
                <th>Students</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summary['by_band'] as $band)
                <tr>
                    <td>{{ $band['label'] }}</td>
                    <td>{{ $band['count'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Status</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summary['by_status'] as $status => $count)
                <tr>
                    <td>{{ ucfirst($status) }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Ustaz / teacher load</h2>
    <table>
        <thead>
            <tr>
                <th>Teacher</th>
                <th>Assigned students</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($summary['teacher_load'] as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['count'] }}</td>
                </tr>
            @empty
                <tr><td colspan="2">No assignments</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Recent dawr / revision cycles</h2>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Label</th>
                <th>Range</th>
                <th>Scheduled</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($summary['recent_revisions'] as $revision)
                <tr>
                    <td>{{ $revision->profile?->student?->full_name }}</td>
                    <td>{{ $revision->label ?: '—' }}</td>
                    <td>{{ $revision->rangeLabel() }}</td>
                    <td>{{ $revision->scheduled_on?->format('d M Y') ?? '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $revision->status)) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No revisions</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Terminology: juz/para (1–30), ayah position, dawr = revision cycle, Muhtamim = principal oversight.<br>
        {{ $fontNote }}
    </div>
</body>
</html>
