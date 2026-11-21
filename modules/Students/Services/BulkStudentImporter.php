<?php

namespace Modules\Students\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Students\Models\Guardian;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentImportBatch;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkStudentImporter
{
    public function __construct(
        protected StudentIdGenerator $ids,
        protected EnrollmentService $enrollments,
    ) {}

    public function preview(UploadedFile $file, int $userId): StudentImportBatch
    {
        $rows = $this->parseFile($file);
        $errors = [];
        $valid = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // header is line 1
            $rowErrors = $this->validateRow($row);
            if ($rowErrors !== []) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors, 'row' => $row];
            } else {
                $valid[] = $row;
            }
        }

        return StudentImportBatch::query()->create([
            'filename' => $file->getClientOriginalName(),
            'status' => 'preview',
            'total_rows' => count($rows),
            'valid_rows' => count($valid),
            'invalid_rows' => count($errors),
            'imported_rows' => 0,
            'preview_rows' => array_slice($valid, 0, 50),
            'preview_errors' => array_slice($errors, 0, 50),
            'created_by' => $userId,
        ]);
    }

    public function commit(StudentImportBatch $batch, ?int $academicYearId = null, ?int $classId = null, ?int $sectionId = null): StudentImportBatch
    {
        if ($batch->status === 'imported') {
            return $batch;
        }

        $filePath = null; // rows already validated in preview_rows only — re-parse not required for safe limited import
        $rows = $batch->preview_rows ?? [];

        $imported = 0;

        DB::transaction(function () use ($rows, $academicYearId, $classId, $sectionId, &$imported) {
            foreach ($rows as $row) {
                $student = Student::query()->create([
                    'student_code' => $this->ids->nextStudentCode(),
                    'admission_number' => $this->ids->nextAdmissionNumber(),
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'] ?? null,
                    'gender' => $row['gender'] ?? null,
                    'date_of_birth' => $row['date_of_birth'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'email' => $row['email'] ?? null,
                    'address' => $row['address'] ?? null,
                    'status' => Student::STATUS_ACTIVE,
                    'enrolled_on' => now()->toDateString(),
                    'qr_payload' => null,
                ]);
                $student->update(['qr_payload' => $this->ids->qrPayload($student)]);

                if (! empty($row['guardian_name'])) {
                    $guardian = Guardian::query()->create([
                        'name' => $row['guardian_name'],
                        'phone' => $row['guardian_phone'] ?? null,
                    ]);
                    $student->guardians()->attach($guardian->id, [
                        'tenant_id' => $student->tenant_id,
                        'relationship' => $row['guardian_relation'] ?? 'guardian',
                        'is_primary' => true,
                        'is_emergency' => true,
                        'can_pickup' => true,
                    ]);
                }

                if ($academicYearId && $classId) {
                    $this->enrollments->enroll($student, [
                        'academic_year_id' => $academicYearId,
                        'school_class_id' => $classId,
                        'section_id' => $sectionId,
                        'roll_number' => $row['roll_number'] ?? null,
                    ]);
                }

                $imported++;
            }
        });

        $batch->update([
            'status' => 'imported',
            'imported_rows' => $imported,
        ]);

        return $batch->fresh();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseFile(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, ['csv', 'txt'], true)) {
            return $this->parseCsv($file->getRealPath());
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        if ($sheet === []) {
            return [];
        }

        $headerRow = array_shift($sheet);
        $headers = [];
        foreach ($headerRow as $col => $label) {
            $headers[$col] = $this->normalizeHeader((string) $label);
        }

        $rows = [];
        foreach ($sheet as $line) {
            $row = [];
            $empty = true;
            foreach ($headers as $col => $key) {
                if ($key === '') {
                    continue;
                }
                $value = is_string($line[$col] ?? null) ? trim($line[$col]) : ($line[$col] ?? null);
                if ($value !== null && $value !== '') {
                    $empty = false;
                }
                $row[$key] = $value === '' ? null : $value;
            }
            if (! $empty) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return [];
        }

        $keys = array_map(fn ($h) => $this->normalizeHeader((string) $h), $header);
        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            $row = [];
            $empty = true;
            foreach ($keys as $i => $key) {
                if ($key === '') {
                    continue;
                }
                $value = isset($data[$i]) ? trim((string) $data[$i]) : null;
                if ($value !== null && $value !== '') {
                    $empty = false;
                }
                $row[$key] = $value === '' ? null : $value;
            }
            if (! $empty) {
                $rows[] = $row;
            }
        }
        fclose($handle);

        return $rows;
    }

    protected function normalizeHeader(string $label): string
    {
        $key = strtolower(trim($label));
        $key = str_replace([' ', '-'], '_', $key);

        return match ($key) {
            'firstname', 'first' => 'first_name',
            'lastname', 'last' => 'last_name',
            'dob', 'birth_date', 'dateofbirth' => 'date_of_birth',
            'guardian', 'parent_name' => 'guardian_name',
            'guardian_mobile', 'parent_phone' => 'guardian_phone',
            'relation' => 'guardian_relation',
            'roll', 'roll_no' => 'roll_number',
            default => $key,
        };
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    protected function validateRow(array $row): array
    {
        $errors = [];
        if (empty($row['first_name'])) {
            $errors[] = 'first_name is required';
        }
        if (! empty($row['email']) && ! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'email is invalid';
        }
        if (! empty($row['gender']) && ! in_array(strtolower((string) $row['gender']), ['male', 'female', 'other'], true)) {
            $errors[] = 'gender must be male, female, or other';
        }

        return $errors;
    }
}
