<?php

namespace App\Support;

use App\Models\PmbLocalApplication;

class PmbCascadeSnapshot
{
    /**
     * @return array{
     *   jenjang: string|null,
     *   programStudi: string|null,
     *   lokasi: string|null,
     *   jenisPendaftaran: string|null,
     *   waktuPerkuliahan: string|null,
     *   jalurMasuk: string|null,
     *   gelombang: string|null,
     *   registrationFee: int|null,
     *   registrationStartsAt: string|null,
     *   registrationEndsAt: string|null,
     *   openStudyProgramId: int|null
     * }
     */
    public static function fromApplication(PmbLocalApplication $application): array
    {
        $snapshot = $application->registration_snapshot ?? [];
        $cascade = is_array($snapshot['cascade'] ?? null) ? $snapshot['cascade'] : [];

        return [
            'jenjang' => $cascade['jenjang'] ?? data_get($snapshot, 'programLevel'),
            'programStudi' => $cascade['programStudi'] ?? $application->study_program_name,
            'lokasi' => $cascade['lokasi'] ?? $application->campus_name ?? data_get($snapshot, 'campusName'),
            'jenisPendaftaran' => $cascade['jenisPendaftaran'] ?? null,
            'waktuPerkuliahan' => $cascade['waktuPerkuliahan'] ?? $application->study_system_name ?? data_get($snapshot, 'studySystemName'),
            'jalurMasuk' => $cascade['jalurMasuk'] ?? $application->registration_path_name ?? data_get($snapshot, 'registrationPathName'),
            'gelombang' => $cascade['gelombang'] ?? $application->registration_period_name,
            'registrationFee' => isset($cascade['registrationFee']) ? (int) $cascade['registrationFee'] : (data_get($snapshot, 'registrationFee') ? (int) data_get($snapshot, 'registrationFee') : null),
            'registrationStartsAt' => $cascade['registrationStartsAt'] ?? null,
            'registrationEndsAt' => $cascade['registrationEndsAt'] ?? null,
            'openStudyProgramId' => isset($cascade['openStudyProgramId']) ? (int) $cascade['openStudyProgramId'] : null,
        ];
    }
}
