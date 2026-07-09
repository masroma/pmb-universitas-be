<?php

namespace App\Services;

use App\Models\PmbOpenStudyProgram;
use App\Models\PmbSyncedRegistrationPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PmbRegistrationCascadeService
{
  public const S1_CLASS_SCHEDULES = [
    'S1 Kelas A (09.45 - 18.00 WIB)',
    'S1 Kelas B (18.30 - 21.00 WIB) + Online (Sabtu)',
    'S1 Kelas C (Sabtu Sesi 1) + Online (On Weekdays)',
    'S1 Kelas D (Sabtu sesi 2) + Online (On Weekdays)',
  ];

  public const S1_JENIS_PENDAFTARAN = [
    'Jalur SMA/SMK',
    'Pindahan',
    'RPL Perolehan SKS',
    'RPL Transfer SKS',
  ];

  public const CAMPUS_LOCATIONS = [
    'Cipayung',
    'Cikarang',
    'Kuningan',
  ];

  /**
   * @return array<int, array{value: string, label: string}>
   */
  public function jenjang(): array
  {
    return PmbOpenStudyProgram::query()
      ->active()
      ->whereNotNull('jenjang_program_studi')
      ->select('jenjang_program_studi')
      ->distinct()
      ->orderBy('jenjang_program_studi')
      ->get()
      ->map(fn (PmbOpenStudyProgram $row): array => [
        'value' => (string) $row->jenjang_program_studi,
        'label' => (string) $row->jenjang_program_studi,
      ])
      ->values()
      ->all();
  }

  /**
   * @return array<int, array{value: int, label: string}>
   */
  public function programStudi(string $jenjang): array
  {
    return PmbOpenStudyProgram::query()
      ->active()
      ->forJenjang($jenjang)
      ->whereNotNull('id_program_studi')
      ->select(['id_program_studi', 'program_studi'])
      ->distinct()
      ->orderBy('program_studi')
      ->get()
      ->map(fn (PmbOpenStudyProgram $row): array => [
        'value' => (int) $row->id_program_studi,
        'label' => (string) $row->program_studi,
      ])
      ->values()
      ->all();
  }

  /**
   * @return array<int, array{value: string, label: string, available: bool}>
   */
  public function lokasi(string $jenjang, int $studyProgramId): array
  {
    $available = PmbOpenStudyProgram::query()
      ->active()
      ->forJenjang($jenjang)
      ->forStudyProgram($studyProgramId)
      ->withActivePeriod()
      ->whereNotNull('lokasi')
      ->pluck('lokasi')
      ->map(fn ($lokasi): string => trim((string) $lokasi))
      ->unique()
      ->values()
      ->all();

    return collect(self::CAMPUS_LOCATIONS)
      ->map(fn (string $campus): array => [
        'value' => $campus,
        'label' => $campus,
        'available' => in_array($campus, $available, true),
      ])
      ->values()
      ->all();
  }

  /**
   * @return array<int, array{value: string, label: string, available: bool, idJalurPendaftaran: int|null}>
   */
  public function jenisPendaftaran(string $jenjang, int $studyProgramId, string $lokasi): array
  {
    if ($jenjang === 'S2') {
      return [[
        'value' => 'lulusan-s1',
        'label' => 'Lulusan S1',
        'available' => true,
        'idJalurPendaftaran' => null,
      ]];
    }

    $available = PmbOpenStudyProgram::query()
      ->active()
      ->forJenjang($jenjang)
      ->forStudyProgram($studyProgramId)
      ->forLokasi($lokasi)
      ->select(['jalur_pendaftaran', 'id_jalur_pendaftaran'])
      ->distinct()
      ->get()
      ->keyBy('jalur_pendaftaran');

    return collect(self::S1_JENIS_PENDAFTARAN)
      ->map(function (string $jenis) use ($available): array {
        $row = $available->get($jenis);

        return [
          'value' => $jenis,
          'label' => $jenis,
          'available' => $row !== null,
          'idJalurPendaftaran' => $row?->id_jalur_pendaftaran,
        ];
      })
      ->values()
      ->all();
  }

  /**
   * @return array<int, array{value: string, label: string, available: bool}>
   */
  public function waktuPerkuliahan(
    string $jenjang,
    int $studyProgramId,
    string $lokasi,
    string $jenisPendaftaran,
  ): array {
    if ($jenjang === 'S1') {
      $rows = $this->baseQuery($jenjang, $studyProgramId, $lokasi)
        ->when($jenisPendaftaran === 'Jalur SMA/SMK', function (Builder $query): void {
          $query->where('jalur_pendaftaran', '!=', 'Alih Jenjang (D3 ke S1)')
            ->where('jalur_pendaftaran', '!=', 'Pindahan');
        }, function (Builder $query) use ($jenisPendaftaran): void {
          $query->where('jalur_pendaftaran', $jenisPendaftaran);
        })
        ->select('nama_periode_pendaftaran')
        ->distinct()
        ->pluck('nama_periode_pendaftaran')
        ->map(function (?string $nama) use ($jenisPendaftaran): ?string {
          if ($nama === 'Jalur Transfer ( Mahasiswa Pindahan)' && $jenisPendaftaran === 'Pindahan') {
            return 'S1 Kelas A (09.45 - 18.00 WIB)';
          }

          return $nama;
        })
        ->filter()
        ->unique()
        ->values()
        ->all();

      return collect(self::S1_CLASS_SCHEDULES)
        ->map(fn (string $schedule): array => [
          'value' => $schedule,
          'label' => $schedule,
          'available' => in_array($schedule, $rows, true),
        ])
        ->values()
        ->all();
    }

    return $this->baseQuery($jenjang, $studyProgramId, $lokasi)
      ->where('jalur_pendaftaran', '!=', 'RPL Perolehan SKS')
      ->select(['nama_periode_pendaftaran'])
      ->distinct()
      ->orderBy('nama_periode_pendaftaran')
      ->get()
      ->map(fn (PmbOpenStudyProgram $row): array => [
        'value' => (string) $row->nama_periode_pendaftaran,
        'label' => (string) $row->nama_periode_pendaftaran,
        'available' => true,
      ])
      ->values()
      ->all();
  }

  /**
   * @return array{
   *   main: array<int, array{value: int, label: string, group: string}>,
   *   beasiswa: array<int, array{value: int, label: string, group: string}>,
   *   khusus: array<int, array{value: int, label: string, group: string}>,
   *   showBeasiswa: bool,
   *   showKhusus: bool
   * }
   */
  public function jalurMasuk(
    string $jenjang,
    int $studyProgramId,
    string $lokasi,
    string $waktuPerkuliahan,
    string $jenisPendaftaran,
  ): array {
    if ($jenjang === 'S2') {
      $items = $this->baseQuery($jenjang, $studyProgramId, $lokasi)
        ->where('nama_periode_pendaftaran', 'like', '%'.$waktuPerkuliahan.'%')
        ->get()
        ->map(fn (PmbOpenStudyProgram $row): array => $this->jalurItem($row, 'main'))
        ->unique('value')
        ->values()
        ->all();

      return [
        'main' => $items,
        'beasiswa' => [],
        'khusus' => [],
        'showBeasiswa' => false,
        'showKhusus' => false,
      ];
    }

    $rows = $this->baseQuery($jenjang, $studyProgramId, $lokasi)
      ->where('nama_periode_pendaftaran', $waktuPerkuliahan)
      ->when($jenisPendaftaran === 'Pindahan', fn (Builder $query) => $query->where('jalur_pendaftaran', 'Pindahan'))
      ->when($jenisPendaftaran === 'Alih Jenjang (D3 ke S1)', fn (Builder $query) => $query->where('jalur_pendaftaran', 'Alih Jenjang (D3 ke S1)'))
      ->when(! in_array($jenisPendaftaran, ['Pindahan', 'Alih Jenjang (D3 ke S1)'], true), function (Builder $query): void {
        $query->where('jalur_pendaftaran', '!=', 'Pindahan')
          ->where('jalur_pendaftaran', '!=', 'Alih Jenjang (D3 ke S1)');
      })
      ->get();

    $rows = $rows->filter(function (PmbOpenStudyProgram $row): bool {
      return $this->periodIsActive($row->id_periode_pendaftaran);
    });

    $excluded = [
      'Alih Jenjang (D3 ke S1)',
      'Pindahan',
      'RPL Transfer SKS',
      'RPL Perolehan SKS',
    ];
    $special = ['Beasiswa KIP Kuliah', 'Beasiswa Kerjasama Perusahaan', 'Beasiswa Gojek-Paramadina'];

    $main = [];
    $beasiswa = [];
    $khusus = [];

    foreach ($rows as $row) {
      $label = $this->displayJalurLabel($row->jalur_pendaftaran, $waktuPerkuliahan, $jenisPendaftaran);

      if (in_array($label, $excluded, true)) {
        continue;
      }

      $item = [
        'value' => (int) $row->id_jalur_pendaftaran,
        'label' => $label,
        'group' => 'main',
      ];

      if ($label === 'Jalur Tes Potensial Akademik') {
        $main[] = $item;
      } elseif (in_array($label, $special, true)) {
        $item['group'] = 'khusus';
        $khusus[] = $item;
      } else {
        $item['group'] = 'beasiswa';
        $beasiswa[] = $item;
      }
    }

    $visibility = $this->jalurGroupVisibility($rows, $waktuPerkuliahan, $jenisPendaftaran);

    if (
      in_array($jenisPendaftaran, ['RPL Transfer SKS', 'RPL Perolehan SKS'], true)
      && in_array($waktuPerkuliahan, self::S1_CLASS_SCHEDULES, true)
    ) {
      $main = array_values(array_filter($main, fn (array $item): bool => $item['label'] === 'Jalur Tes Potensial Akademik'));
      $beasiswa = [];
      $khusus = [];
      $visibility['showBeasiswa'] = false;
      $visibility['showKhusus'] = false;
    }

    return [
      'main' => $this->uniqueJalurItems($main),
      'beasiswa' => $this->uniqueJalurItems($beasiswa),
      'khusus' => $this->uniqueJalurItems($khusus),
      'showBeasiswa' => $visibility['showBeasiswa'],
      'showKhusus' => $visibility['showKhusus'] || collect($khusus)->contains(fn (array $item): bool => $item['label'] === 'Beasiswa Gojek-Paramadina'),
    ];
  }

  /**
   * @return array<string, mixed>
   */
  public function resolve(
    string $jenjang,
    int $studyProgramId,
    string $lokasi,
    string $jenisPendaftaran,
    string $waktuPerkuliahan,
    int $jalurMasukId,
  ): array {
    $namaPeriode = $waktuPerkuliahan;

    if ($jenisPendaftaran === 'Alih Jenjang (D3 ke S1)') {
      $jalurMasukId = 2;
    }

    $openProgram = $this->baseQuery($jenjang, $studyProgramId, $lokasi)
      ->where('id_jalur_pendaftaran', $jalurMasukId)
      ->where('nama_periode_pendaftaran', $namaPeriode)
      ->first();

    if (! $openProgram) {
      $openProgram = $this->baseQuery($jenjang, $studyProgramId, $lokasi)
        ->where('id_jalur_pendaftaran', $jalurMasukId)
        ->where('nama_periode_pendaftaran', 'like', '%'.$namaPeriode.'%')
        ->first();
    }

    if (! $openProgram) {
      return ['matched' => false];
    }

    $period = PmbSyncedRegistrationPeriod::query()
      ->where('sevima_id', (string) $openProgram->id_periode_pendaftaran)
      ->first();

    $registrationPath = DB::table('admission_paths')
      ->where('is_active', true)
      ->where('sevima_id', $jalurMasukId)
      ->first();

    $programOption = $this->resolveProgramOption($openProgram, $lokasi, $namaPeriode);

    $registrationFee = $openProgram->registration_fee ?: 300000;
    if ($jenjang === 'S2') {
      $registrationFee = 500000;
    }
    if (in_array($openProgram->jalur_pendaftaran, ['RPL Transfer SKS', 'RPL Perolehan SKS'], true)) {
      $registrationFee = 1000000;
    }

    return [
      'matched' => true,
      'openStudyProgramId' => $openProgram->id,
      'programOptionId' => $programOption?->registration_option_id,
      'academicPeriodId' => $programOption?->period_id,
      'registrationPeriodId' => $programOption?->wave_id,
      'registrationPathId' => $registrationPath?->id,
      'summary' => [
        'jenjang' => $jenjang,
        'programStudi' => $openProgram->program_studi,
        'lokasi' => $lokasi,
        'jenisPendaftaran' => $jenisPendaftaran === 'lulusan-s1' ? 'Lulusan S1' : $jenisPendaftaran,
        'waktuPerkuliahan' => $waktuPerkuliahan,
        'jalurMasuk' => $registrationPath?->name ?: $openProgram->jalur_pendaftaran,
        'gelombang' => $openProgram->gelombang ?: 'Gelombang 1',
        'registrationStartsAt' => $period?->tanggal_awal_pendaftaran?->toDateString(),
        'registrationEndsAt' => $period?->tanggal_akhir_pendaftaran?->toDateString(),
        'registrationFee' => $registrationFee,
        'keterangan' => $period?->keterangan,
      ],
    ];
  }

  private function baseQuery(string $jenjang, int $studyProgramId, string $lokasi): Builder
  {
    return PmbOpenStudyProgram::query()
      ->active()
      ->forJenjang($jenjang)
      ->forStudyProgram($studyProgramId)
      ->forLokasi($lokasi)
      ->withActivePeriod();
  }

  private function periodIsActive(?string $periodSevimaId): bool
  {
    if (! filled($periodSevimaId)) {
      return true;
    }

    $period = PmbSyncedRegistrationPeriod::query()
      ->where('sevima_id', (string) $periodSevimaId)
      ->first();

    if (! $period) {
      return true;
    }

    $today = now()->toDateString();

    return (! $period->tanggal_akhir_pendaftaran || $period->tanggal_akhir_pendaftaran->toDateString() >= $today)
      && ($period->status_periode_pendaftaran === null || $period->status_periode_pendaftaran === 'Aktif');
  }

  private function displayJalurLabel(?string $jalur, string $waktuPerkuliahan, string $jenisPendaftaran): string
  {
    $label = (string) $jalur;
    $eveningClasses = [
      'S1 Kelas B (18.30 - 21.00 WIB) + Online (Sabtu)',
      'S1 Kelas C (Sabtu Sesi 1) + Online (On Weekdays)',
      'S1 Kelas D (Sabtu sesi 2) + Online (On Weekdays)',
    ];

    if ($label === 'Jalur SMA/SMK' && in_array($waktuPerkuliahan, $eveningClasses, true)) {
      return 'Jalur Tes Potensial Akademik';
    }

    if ($label === 'Pindahan' && $waktuPerkuliahan === 'S1 Kelas A (09.45 - 18.00 WIB)') {
      return 'Jalur Tes Potensial Akademik';
    }

    if ($label === 'Alih Jenjang (D3 ke S1)' && in_array($waktuPerkuliahan, $eveningClasses, true)) {
      return 'Jalur Tes Potensial Akademik';
    }

    return $label;
  }

  /**
   * @param  Collection<int, PmbOpenStudyProgram>  $rows
   * @return array{showBeasiswa: bool, showKhusus: bool}
   */
  private function jalurGroupVisibility(Collection $rows, string $waktuPerkuliahan, string $jenisPendaftaran): array
  {
    $eveningClasses = [
      'S1 Kelas B (18.30 - 21.00 WIB) + Online (Sabtu)',
      'S1 Kelas C (Sabtu Sesi 1) + Online (On Weekdays)',
      'S1 Kelas D (Sabtu sesi 2) + Online (On Weekdays)',
    ];

    $shouldHideBeasiswa = $rows->contains(function (PmbOpenStudyProgram $row) use ($waktuPerkuliahan, $eveningClasses): bool {
      return in_array($row->jalur_pendaftaran, ['Jalur SMA/SMK', 'Beasiswa Kerjasama Perusahaan', 'Pindahan', 'Alih Jenjang (D3 ke S1)'], true)
        && in_array($waktuPerkuliahan, $eveningClasses, true);
    }) || $rows->contains(fn (PmbOpenStudyProgram $row): bool => $row->jalur_pendaftaran === 'Pindahan'
      && $waktuPerkuliahan === 'S1 Kelas A (09.45 - 18.00 WIB)');

    $shouldHideKhusus = $rows->contains(function (PmbOpenStudyProgram $row) use ($waktuPerkuliahan): bool {
      return in_array($row->jalur_pendaftaran, ['Jalur Tes Potensial Akademik', 'Pindahan', 'Alih Jenjang (D3 ke S1)'], true)
        && $waktuPerkuliahan === 'S1 Kelas A (09.45 - 18.00 WIB)';
    }) || $rows->contains(function (PmbOpenStudyProgram $row) use ($eveningClasses, $waktuPerkuliahan): bool {
      return $row->jalur_pendaftaran === 'Alih Jenjang (D3 ke S1)'
        && in_array($waktuPerkuliahan, $eveningClasses, true);
    });

    return [
      'showBeasiswa' => ! $shouldHideBeasiswa,
      'showKhusus' => ! $shouldHideKhusus,
    ];
  }

  /**
   * @param  array<int, array{value: int, label: string, group: string}>  $items
   * @return array<int, array{value: int, label: string, group: string}>
   */
  private function uniqueJalurItems(array $items): array
  {
    $seen = [];

    return collect($items)
      ->filter(function (array $item) use (&$seen): bool {
        $key = $item['value'].'|'.$item['label'];

        if (isset($seen[$key])) {
          return false;
        }

        $seen[$key] = true;

        return true;
      })
      ->values()
      ->all();
  }

  /**
   * @return array{value: int, label: string, group: string}
   */
  private function jalurItem(PmbOpenStudyProgram $row, string $group): array
  {
    return [
      'value' => (int) $row->id_jalur_pendaftaran,
      'label' => (string) $row->jalur_pendaftaran,
      'group' => $group,
    ];
  }

  private function resolveProgramOption(PmbOpenStudyProgram $openProgram, string $lokasi, string $classLabel): ?object
  {
    $classKeyword = $this->classKeyword($classLabel);

    $query = DB::table('pmb_registration_options')
      ->join('pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_registration_options.admission_period_id')
      ->leftJoin('pmb_waves', 'pmb_waves.id', '=', 'pmb_registration_options.wave_id')
      ->join('campus_study_programs', 'campus_study_programs.id', '=', 'pmb_registration_options.campus_study_program_id')
      ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
      ->join('study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id')
      ->leftJoin('class_types', 'class_types.id', '=', 'pmb_registration_options.class_type_id')
      ->where('pmb_registration_options.is_active', true)
      ->where('pmb_admission_periods.is_active', true)
      ->where('campus_study_programs.is_open', true)
      ->where('study_programs.level', $openProgram->jenjang_program_studi)
      ->where('study_programs.name', $openProgram->program_studi)
      ->where('campuses.name', 'like', '%'.$lokasi.'%');

    if ($classKeyword) {
      $query->where(function ($builder) use ($classKeyword, $classLabel): void {
        $builder->where('class_types.name', 'like', '%'.$classKeyword.'%')
          ->orWhere('class_types.schedule_label', 'like', '%'.$classKeyword.'%')
          ->orWhere('class_types.name', 'like', '%'.$classLabel.'%');
      });
    }

    return $query
      ->select([
        'pmb_registration_options.id as registration_option_id',
        'pmb_admission_periods.id as period_id',
        'pmb_waves.id as wave_id',
      ])
      ->first()
      ?? DB::table('pmb_registration_options')
        ->join('pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_registration_options.admission_period_id')
        ->leftJoin('pmb_waves', 'pmb_waves.id', '=', 'pmb_registration_options.wave_id')
        ->join('campus_study_programs', 'campus_study_programs.id', '=', 'pmb_registration_options.campus_study_program_id')
        ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
        ->join('study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id')
        ->where('pmb_registration_options.is_active', true)
        ->where('study_programs.level', $openProgram->jenjang_program_studi)
        ->where('study_programs.name', $openProgram->program_studi)
        ->where('campuses.name', 'like', '%'.$lokasi.'%')
        ->select([
          'pmb_registration_options.id as registration_option_id',
          'pmb_admission_periods.id as period_id',
          'pmb_waves.id as wave_id',
        ])
        ->first();
  }

  private function classKeyword(string $classLabel): ?string
  {
    if (preg_match('/Kelas\s+([A-D])/i', $classLabel, $matches)) {
      return 'Kelas '.$matches[1];
    }

    return null;
  }
}
