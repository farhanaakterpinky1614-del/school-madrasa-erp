<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Marksheet — {{ $student->full_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
        .header { text-align: center; border-bottom: 2px solid #312e81; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 18px; color: #312e81; }
        .header p { margin: 4px 0; color: #475569; }
        .meta { width: 100%; margin-bottom: 14px; }
        .meta td { padding: 3px 0; }
        table.marks { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.marks th, table.marks td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.marks th { background: #eef2ff; color: #312e81; }
        .footer { margin-top: 18px; font-size: 10px; color: #64748b; }
        .totals { margin-top: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institution?->name ?? 'School & Madrasa ERP' }}</h1>
        <p>{{ $exam->name }} — Academic Result Card</p>
        <p>{{ $exam->schoolClass?->name }} @if($exam->academicYear) · {{ $exam->academicYear->name }} @endif</p>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Student:</strong> {{ $student->full_name }}</td>
            <td><strong>Code:</strong> {{ $student->student_code }}</td>
        </tr>
        <tr>
            <td><strong>Roll:</strong> {{ $enrollment?->roll_number ?? '—' }}</td>
            <td><strong>Board:</strong> {{ $exam->board_name ?: '—' }} {{ $exam->board_roll_prefix ? '(' . $exam->board_roll_prefix . '…)' : '' }}</td>
        </tr>
    </table>

    <table class="marks">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Full</th>
                <th>Pass</th>
                <th>Obtained</th>
                <th>Grade</th>
                <th>GPA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['subject'] }}</td>
                    <td>{{ $row['full_marks'] }}</td>
                    <td>{{ $row['pass_marks'] }}</td>
                    <td>{{ $row['absent'] ? 'Absent' : ($row['obtained'] ?? '—') }}</td>
                    <td>{{ $row['grade'] ?? '—' }}</td>
                    <td>{{ $row['gpa'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="totals">
        Total: {{ $totalObtained }} / {{ $totalFull }}
        @if ($averageGpa !== null) · Average GPA: {{ $averageGpa }} @endif
    </p>

    <p class="footer">
        Generated {{ now('Asia/Dhaka')->format('d M Y H:i') }} (Asia/Dhaka).
        @if (! empty($fontNote))
            Bangla font embedding is not configured in this DomPDF spike — English layout used.
        @endif
    </p>
</body>
</html>
