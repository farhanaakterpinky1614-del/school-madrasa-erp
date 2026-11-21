<?php

use Illuminate\Http\UploadedFile;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Students\Models\Student;
use Modules\Students\Services\BulkStudentImporter;

it('previews and safely imports validated csv rows', function () {
    $registered = $this->registerTenant([
        'slug' => 'import-stu',
        'admin_email' => 'import-stu@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    $this->bindTenant($registered['tenant']);

    $csv = "first_name,last_name,gender,guardian_name,guardian_phone\n".
        "Imran,Hossain,male,Karim Hossain,01720000001\n".
        ",Broken,,,\n";

    $file = UploadedFile::fake()->createWithContent('students.csv', $csv);

    $importer = app(BulkStudentImporter::class);
    $batch = $importer->preview($file, $registered['user']->id);

    expect($batch->total_rows)->toBe(2)
        ->and($batch->valid_rows)->toBe(1)
        ->and($batch->invalid_rows)->toBe(1)
        ->and($batch->status)->toBe('preview');

    $yearId = \Modules\Academics\Models\AcademicYear::query()->value('id');
    $classId = \Modules\Academics\Models\SchoolClass::query()->value('id');

    $committed = $importer->commit($batch, $yearId, $classId, null);

    expect($committed->status)->toBe('imported')
        ->and($committed->imported_rows)->toBe(1)
        ->and(Student::query()->where('first_name', 'Imran')->exists())->toBeTrue();
});
