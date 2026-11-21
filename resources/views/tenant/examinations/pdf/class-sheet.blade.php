<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Class Result — {{ $exam->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        .header { text-align: center; border-bottom: 2px solid #312e81; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 16px; color: #312e81; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; }
        th { background: #eef2ff; color: #312e81; }
        td.name { text-align: left; }
        .footer { margin-top: 12px; font-size: 9px; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institution?->name ?? 'School & Madrasa ERP' }}</h1>
        <p>{{ $exam->name }} — Class Result Sheet ({{ $exam->schoolClass?->name }})</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Roll</th>
                <th>Student</th>
                @foreach ($papers as $paper)
                    <th>{{ $paper->subject?->name }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $row)
                <tr>
                    <td>{{ $row['roll'] }}</td>
                    <td class="name">{{ $row['name'] }}</td>
                    @foreach ($row['cells'] as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                    <td>{{ $row['total'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">
        Generated {{ now('Asia/Dhaka')->format('d M Y H:i') }} (Asia/Dhaka).
        @if (! empty($fontNote))
            Bangla font embedding is not configured in this DomPDF spike — English layout used.
        @endif
    </p>
</body>
</html>
