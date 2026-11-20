<?php

namespace Modules\Academics\Services;

class InstitutionTypePreset
{
    /**
     * Feature / structure presets keyed by institution_type (ADR-007 — no fork).
     *
     * @return array<string, mixed>
     */
    public function for(string $type): array
    {
        $presets = config('sme.institution_presets', []);

        return $presets[$type] ?? $presets['school'] ?? [
            'label' => 'General School',
            'features' => [
                'classes_sections' => true,
                'departments' => false,
                'standard_subjects' => true,
                'hifz' => false,
                'board_exam_fields' => true,
            ],
            'terminology' => [
                'class' => 'Class',
                'section' => 'Section',
                'department' => 'Department',
                'teacher' => 'Teacher',
                'principal' => 'Principal',
            ],
            'default_classes' => range(6, 10),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return config('sme.institution_types', []);
    }
}
