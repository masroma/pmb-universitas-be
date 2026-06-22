<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MasterPmbController extends Controller
{
    public function index(Request $request, string $resource): View
    {
        $config = $this->resource($resource);
        $search = $request->string('q')->toString();

        $query = DB::table($config['table']);
        $this->applyJoins($query, $config);

        if ($search !== '' && ($config['search'] ?? []) !== []) {
            $query->where(function ($query) use ($search, $config): void {
                foreach ($config['search'] as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        foreach ($config['order'] ?? ['id'] as $order) {
            $query->orderBy($order);
        }

        $records = $query
            ->select($config['select'] ?? [$config['table'].'.*'])
            ->paginate(20)
            ->withQueryString();

        return view('admin.master-pmb.index', [
            'campusSetting' => $this->campusSetting(),
            'config' => $config,
            'records' => $records,
            'resource' => $resource,
            'resources' => $this->resources(),
            'search' => $search,
        ]);
    }

    public function create(string $resource): View
    {
        $config = $this->resource($resource);

        return view('admin.master-pmb.form', [
            'campusSetting' => $this->campusSetting(),
            'config' => $config,
            'record' => (object) $this->defaults($config),
            'resource' => $resource,
            'resources' => $this->resources(),
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $config = $this->resource($resource);
        $data = $this->validatedData($request, $config);
        $id = DB::table($config['table'])->insertGetId([
            ...$data,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AuditLogger::record('created', $config['table'], $id, null, $data, $request);

        return redirect()
            ->route('admin.master-pmb.index', $resource)
            ->with('status', $config['label'].' berhasil ditambahkan.');
    }

    public function edit(string $resource, int $id): View
    {
        $config = $this->resource($resource);
        $record = DB::table($config['table'])->where('id', $id)->first();
        abort_if(! $record, 404);

        $record = $this->hydrateJsonFields($record, $config);

        return view('admin.master-pmb.form', [
            'campusSetting' => $this->campusSetting(),
            'config' => $config,
            'record' => $record,
            'resource' => $resource,
            'resources' => $this->resources(),
        ]);
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $config = $this->resource($resource);
        $before = (array) DB::table($config['table'])->where('id', $id)->first();
        abort_if($before === [], 404);

        $data = $this->validatedData($request, $config);

        DB::table($config['table'])->where('id', $id)->update([
            ...$data,
            'updated_at' => now(),
        ]);

        AuditLogger::record('updated', $config['table'], $id, $before, $data, $request);

        return redirect()
            ->route('admin.master-pmb.index', [$resource, ...$request->only(['q', 'page'])])
            ->with('status', $config['label'].' berhasil diperbarui.');
    }

    public function destroy(Request $request, string $resource, int $id): RedirectResponse
    {
        $config = $this->resource($resource);
        $before = (array) DB::table($config['table'])->where('id', $id)->first();
        abort_if($before === [], 404);

        DB::table($config['table'])->where('id', $id)->delete();
        AuditLogger::record('deleted', $config['table'], $id, $before, null, $request);

        return redirect()
            ->route('admin.master-pmb.index', $resource)
            ->with('status', $config['label'].' berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, array $config): array
    {
        $rules = collect($config['fields'])
            ->mapWithKeys(fn (array $field, string $name): array => [$name => $field['rules'] ?? ['nullable']])
            ->all();
        $validated = $request->validate($rules);

        foreach ($config['fields'] as $name => $field) {
            if (($field['type'] ?? 'text') === 'checkbox') {
                $validated[$name] = $request->boolean($name);
            }

            if (($field['type'] ?? 'text') === 'json_lines') {
                $validated[$name] = json_encode(
                    collect(preg_split('/\r\n|\r|\n/', $validated[$name] ?? ''))
                        ->map(fn (string $item): string => trim($item))
                        ->filter()
                        ->values()
                        ->all(),
                );
            }
        }

        foreach ($config['force'] ?? [] as $name => $value) {
            $validated[$name] = $value instanceof \Closure ? $value() : $value;
        }

        return $validated;
    }

    private function hydrateJsonFields(object $record, array $config): object
    {
        foreach ($config['fields'] as $name => $field) {
            if (($field['type'] ?? 'text') !== 'json_lines') {
                continue;
            }

            $decoded = json_decode($record->{$name} ?? '[]', true);
            $record->{$name} = is_array($decoded) ? implode(PHP_EOL, $decoded) : '';
        }

        return $record;
    }

    private function defaults(array $config): array
    {
        return collect($config['fields'])
            ->mapWithKeys(fn (array $field, string $name): array => [$name => $field['default'] ?? (($field['type'] ?? null) === 'checkbox')])
            ->all();
    }

    private function applyJoins($query, array $config): void
    {
        foreach ($config['joins'] ?? [] as $join) {
            $query->leftJoin(...$join);
        }
    }

    private function resource(string $resource): array
    {
        $resources = $this->resources();
        abort_unless(isset($resources[$resource]), 404);

        return $resources[$resource];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function resources(): array
    {
        $institutionId = fn (): int => $this->institutionId();

        return [
            'campuses' => [
                'label' => 'Lokasi Kampus',
                'table' => 'campuses',
                'search' => ['campuses.code', 'campuses.name', 'campuses.city', 'campuses.province'],
                'order' => ['campuses.sort_order', 'campuses.name'],
                'columns' => ['code' => 'Kode', 'name' => 'Nama', 'city' => 'Kota', 'is_main' => 'Utama', 'is_active' => 'Aktif'],
                'fields' => [
                    'code' => ['label' => 'Kode', 'rules' => ['required', 'string', 'max:255']],
                    'name' => ['label' => 'Nama Lokasi', 'rules' => ['required', 'string', 'max:255']],
                    'city' => ['label' => 'Kota', 'rules' => ['nullable', 'string', 'max:255']],
                    'province' => ['label' => 'Provinsi', 'rules' => ['nullable', 'string', 'max:255']],
                    'address' => ['label' => 'Alamat', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                    'maps_url' => ['label' => 'URL Maps', 'rules' => ['nullable', 'string', 'max:255']],
                    'is_main' => ['label' => 'Lokasi Utama', 'type' => 'checkbox'],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
                'force' => ['institution_id' => $institutionId],
            ],
            'campus-contacts' => [
                'label' => 'Kontak Lokasi',
                'table' => 'campus_contacts',
                'search' => ['campus_contacts.type', 'campus_contacts.label', 'campus_contacts.value', 'campuses.name'],
                'joins' => [['campuses', 'campuses.id', '=', 'campus_contacts.campus_id']],
                'select' => ['campus_contacts.*', 'campuses.name as campus_name'],
                'columns' => ['campus_name' => 'Lokasi', 'type' => 'Tipe', 'label' => 'Label', 'value' => 'Kontak', 'is_primary' => 'Utama'],
                'fields' => [
                    'campus_id' => ['label' => 'Lokasi', 'type' => 'select', 'options' => fn () => $this->options('campuses'), 'rules' => ['required', 'integer', 'exists:campuses,id']],
                    'type' => ['label' => 'Tipe', 'type' => 'select', 'options' => ['whatsapp' => 'WhatsApp', 'email' => 'Email', 'phone' => 'Telepon', 'website' => 'Website', 'social' => 'Media Sosial'], 'rules' => ['required', 'string', 'max:30']],
                    'label' => ['label' => 'Label', 'rules' => ['nullable', 'string', 'max:255']],
                    'value' => ['label' => 'Nilai Kontak', 'rules' => ['required', 'string', 'max:255']],
                    'is_primary' => ['label' => 'Kontak Utama', 'type' => 'checkbox'],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
            ],
            'faculties' => [
                'label' => 'Fakultas',
                'table' => 'faculties',
                'search' => ['faculties.code', 'faculties.name'],
                'columns' => ['code' => 'Kode', 'name' => 'Nama', 'is_active' => 'Aktif'],
                'fields' => [
                    'code' => ['label' => 'Kode', 'rules' => ['required', 'string', 'max:255']],
                    'name' => ['label' => 'Nama Fakultas', 'rules' => ['required', 'string', 'max:255']],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                ],
                'force' => ['institution_id' => $institutionId],
            ],
            'study-programs' => [
                'label' => 'Program Studi',
                'table' => 'study_programs',
                'search' => ['study_programs.code', 'study_programs.name', 'study_programs.level', 'study_programs.accreditation', 'faculties.name'],
                'joins' => [['faculties', 'faculties.id', '=', 'study_programs.faculty_id']],
                'select' => ['study_programs.*', 'faculties.name as faculty_name'],
                'columns' => ['code' => 'Kode', 'name' => 'Prodi', 'faculty_name' => 'Fakultas', 'level' => 'Jenjang', 'accreditation' => 'Akreditasi', 'is_active' => 'Aktif'],
                'fields' => [
                    'faculty_id' => ['label' => 'Fakultas', 'type' => 'select', 'options' => fn () => $this->options('faculties'), 'rules' => ['nullable', 'integer', 'exists:faculties,id']],
                    'code' => ['label' => 'Kode', 'rules' => ['required', 'string', 'max:255']],
                    'level' => ['label' => 'Jenjang', 'type' => 'select', 'options' => ['S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3', 'D3' => 'D3', 'D4' => 'D4'], 'rules' => ['required', 'string', 'max:20']],
                    'name' => ['label' => 'Nama Prodi', 'rules' => ['required', 'string', 'max:255']],
                    'degree' => ['label' => 'Gelar', 'rules' => ['nullable', 'string', 'max:255']],
                    'accreditation' => ['label' => 'Akreditasi', 'rules' => ['nullable', 'string', 'max:255']],
                    'description' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
                'force' => ['institution_id' => $institutionId],
            ],
            'campus-programs' => [
                'label' => 'Prodi per Lokasi',
                'table' => 'campus_study_programs',
                'search' => ['campuses.name', 'study_programs.name'],
                'joins' => [['campuses', 'campuses.id', '=', 'campus_study_programs.campus_id'], ['study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id']],
                'select' => ['campus_study_programs.*', 'campuses.name as campus_name', 'study_programs.name as program_name'],
                'columns' => ['campus_name' => 'Lokasi', 'program_name' => 'Program Studi', 'is_open' => 'Dibuka'],
                'fields' => [
                    'campus_id' => ['label' => 'Lokasi', 'type' => 'select', 'options' => fn () => $this->options('campuses'), 'rules' => ['required', 'integer', 'exists:campuses,id']],
                    'study_program_id' => ['label' => 'Program Studi', 'type' => 'select', 'options' => fn () => $this->options('study_programs', 'name'), 'rules' => ['required', 'integer', 'exists:study_programs,id']],
                    'is_open' => ['label' => 'Dibuka', 'type' => 'checkbox', 'default' => true],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
            ],
            'class-types' => [
                'label' => 'Kelas/Waktu Kuliah',
                'table' => 'class_types',
                'search' => ['class_types.code', 'class_types.name', 'class_types.schedule_label'],
                'columns' => ['code' => 'Kode', 'name' => 'Nama', 'schedule_label' => 'Jadwal', 'is_online' => 'Online', 'is_active' => 'Aktif'],
                'fields' => [
                    'code' => ['label' => 'Kode', 'rules' => ['required', 'string', 'max:255']],
                    'name' => ['label' => 'Nama Kelas', 'rules' => ['required', 'string', 'max:255']],
                    'schedule_label' => ['label' => 'Label Jadwal', 'rules' => ['nullable', 'string', 'max:255']],
                    'description' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                    'is_online' => ['label' => 'Online/Hybrid', 'type' => 'checkbox'],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
                'force' => ['institution_id' => $institutionId],
            ],
            'periods' => [
                'label' => 'Periode PMB',
                'table' => 'pmb_admission_periods',
                'search' => ['code', 'name', 'academic_year'],
                'columns' => ['code' => 'Kode', 'name' => 'Nama', 'academic_year' => 'Tahun Ajar', 'starts_at' => 'Mulai', 'ends_at' => 'Selesai', 'is_active' => 'Aktif'],
                'fields' => [
                    'code' => ['label' => 'Kode', 'rules' => ['required', 'string', 'max:255']],
                    'name' => ['label' => 'Nama Periode', 'rules' => ['required', 'string', 'max:255']],
                    'academic_year' => ['label' => 'Tahun Ajar', 'rules' => ['required', 'string', 'max:255']],
                    'starts_at' => ['label' => 'Tanggal Mulai', 'type' => 'date', 'rules' => ['nullable', 'date']],
                    'ends_at' => ['label' => 'Tanggal Selesai', 'type' => 'date', 'rules' => ['nullable', 'date']],
                    'brochure_url' => ['label' => 'URL Brosur', 'rules' => ['nullable', 'string', 'max:255']],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                ],
                'force' => ['institution_id' => $institutionId],
            ],
            'waves' => [
                'label' => 'Gelombang PMB',
                'table' => 'pmb_waves',
                'search' => ['pmb_waves.code', 'pmb_waves.name', 'pmb_admission_periods.name'],
                'joins' => [['pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_waves.admission_period_id']],
                'select' => ['pmb_waves.*', 'pmb_admission_periods.name as period_name'],
                'columns' => ['period_name' => 'Periode', 'code' => 'Kode', 'name' => 'Nama', 'starts_at' => 'Mulai', 'ends_at' => 'Selesai', 'is_active' => 'Aktif'],
                'fields' => [
                    'admission_period_id' => ['label' => 'Periode', 'type' => 'select', 'options' => fn () => $this->options('pmb_admission_periods'), 'rules' => ['required', 'integer', 'exists:pmb_admission_periods,id']],
                    'code' => ['label' => 'Kode', 'rules' => ['required', 'string', 'max:255']],
                    'name' => ['label' => 'Nama Gelombang', 'rules' => ['required', 'string', 'max:255']],
                    'starts_at' => ['label' => 'Tanggal Mulai', 'type' => 'date', 'rules' => ['nullable', 'date']],
                    'ends_at' => ['label' => 'Tanggal Selesai', 'type' => 'date', 'rules' => ['nullable', 'date']],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
            ],
            'paths' => [
                'label' => 'Jalur Pendaftaran',
                'table' => 'admission_paths',
                'search' => ['code', 'name', 'description'],
                'columns' => ['code' => 'Kode', 'name' => 'Nama', 'registration_fee' => 'Biaya Daftar', 'is_active' => 'Aktif'],
                'fields' => [
                    'code' => ['label' => 'Kode', 'rules' => ['required', 'string', 'max:255']],
                    'name' => ['label' => 'Nama Jalur', 'rules' => ['required', 'string', 'max:255']],
                    'description' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                    'registration_fee' => ['label' => 'Biaya Daftar', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
                'force' => ['institution_id' => $institutionId],
            ],
            'registration-options' => [
                'label' => 'Opsi Pendaftaran',
                'table' => 'pmb_registration_options',
                'search' => ['pmb_admission_periods.name', 'campuses.name', 'study_programs.name', 'admission_paths.name', 'class_types.name'],
                'joins' => [
                    ['pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_registration_options.admission_period_id'],
                    ['pmb_waves', 'pmb_waves.id', '=', 'pmb_registration_options.wave_id'],
                    ['campus_study_programs', 'campus_study_programs.id', '=', 'pmb_registration_options.campus_study_program_id'],
                    ['campuses', 'campuses.id', '=', 'campus_study_programs.campus_id'],
                    ['study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id'],
                    ['admission_paths', 'admission_paths.id', '=', 'pmb_registration_options.admission_path_id'],
                    ['class_types', 'class_types.id', '=', 'pmb_registration_options.class_type_id'],
                ],
                'select' => ['pmb_registration_options.*', 'pmb_admission_periods.name as period_name', 'pmb_waves.name as wave_name', 'campuses.name as campus_name', 'study_programs.name as program_name', 'admission_paths.name as path_name', 'class_types.name as class_name'],
                'columns' => ['period_name' => 'Periode', 'wave_name' => 'Gelombang', 'campus_name' => 'Lokasi', 'program_name' => 'Prodi', 'path_name' => 'Jalur', 'class_name' => 'Kelas', 'is_active' => 'Aktif'],
                'fields' => [
                    'admission_period_id' => ['label' => 'Periode', 'type' => 'select', 'options' => fn () => $this->options('pmb_admission_periods'), 'rules' => ['required', 'integer', 'exists:pmb_admission_periods,id']],
                    'wave_id' => ['label' => 'Gelombang', 'type' => 'select', 'options' => fn () => $this->options('pmb_waves'), 'rules' => ['nullable', 'integer', 'exists:pmb_waves,id']],
                    'campus_study_program_id' => ['label' => 'Prodi per Lokasi', 'type' => 'select', 'options' => fn () => $this->campusProgramOptions(), 'rules' => ['required', 'integer', 'exists:campus_study_programs,id']],
                    'admission_path_id' => ['label' => 'Jalur', 'type' => 'select', 'options' => fn () => $this->options('admission_paths'), 'rules' => ['required', 'integer', 'exists:admission_paths,id']],
                    'class_type_id' => ['label' => 'Kelas', 'type' => 'select', 'options' => fn () => $this->options('class_types'), 'rules' => ['nullable', 'integer', 'exists:class_types,id']],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                ],
            ],
            'tuition-fees' => [
                'label' => 'Biaya Kuliah',
                'table' => 'tuition_fee_schemes',
                'search' => ['study_programs.name', 'campuses.name', 'pmb_waves.name'],
                'joins' => [
                    ['pmb_registration_options', 'pmb_registration_options.id', '=', 'tuition_fee_schemes.registration_option_id'],
                    ['pmb_waves', 'pmb_waves.id', '=', 'pmb_registration_options.wave_id'],
                    ['campus_study_programs', 'campus_study_programs.id', '=', 'pmb_registration_options.campus_study_program_id'],
                    ['campuses', 'campuses.id', '=', 'campus_study_programs.campus_id'],
                    ['study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id'],
                ],
                'select' => ['tuition_fee_schemes.*', 'pmb_waves.name as wave_name', 'campuses.name as campus_name', 'study_programs.name as program_name'],
                'columns' => ['program_name' => 'Prodi', 'campus_name' => 'Lokasi', 'wave_name' => 'Gelombang', 'registration_fee' => 'Daftar', 'installment_amount' => 'Angsuran', 'semester_fee' => 'Semester', 'is_active' => 'Aktif'],
                'fields' => [
                    'registration_option_id' => ['label' => 'Opsi Pendaftaran', 'type' => 'select', 'options' => fn () => $this->registrationOptionOptions(), 'rules' => ['required', 'integer', 'exists:pmb_registration_options,id']],
                    'registration_fee' => ['label' => 'Biaya Daftar', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                    'installment_count' => ['label' => 'Jumlah Angsuran', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:24'], 'default' => 6],
                    'installment_amount' => ['label' => 'Nominal Angsuran', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                    'semester_fee' => ['label' => 'Biaya Semester', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                    'total_first_payment' => ['label' => 'Pembayaran Awal', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    'currency' => ['label' => 'Mata Uang', 'rules' => ['required', 'string', 'max:3'], 'default' => 'IDR'],
                    'notes' => ['label' => 'Catatan', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                ],
            ],
            'scholarships' => [
                'label' => 'Beasiswa',
                'table' => 'scholarships',
                'search' => ['code', 'name', 'description'],
                'columns' => ['code' => 'Kode', 'name' => 'Nama', 'is_active' => 'Aktif'],
                'fields' => [
                    'code' => ['label' => 'Kode', 'rules' => ['required', 'string', 'max:255']],
                    'name' => ['label' => 'Nama Beasiswa', 'rules' => ['required', 'string', 'max:255']],
                    'description' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                    'requirements' => ['label' => 'Syarat Beasiswa', 'type' => 'json_lines', 'rules' => ['nullable', 'string']],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
                'force' => ['institution_id' => $institutionId],
            ],
            'content-blocks' => [
                'label' => 'Konten PMB',
                'table' => 'pmb_content_blocks',
                'search' => ['category', 'title', 'subtitle', 'body'],
                'columns' => ['category' => 'Kategori', 'title' => 'Judul', 'is_active' => 'Aktif'],
                'fields' => [
                    'category' => ['label' => 'Kategori', 'rules' => ['required', 'string', 'max:255']],
                    'title' => ['label' => 'Judul', 'rules' => ['required', 'string', 'max:255']],
                    'subtitle' => ['label' => 'Subjudul', 'rules' => ['nullable', 'string', 'max:255']],
                    'body' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                    'items' => ['label' => 'Poin Informasi', 'type' => 'json_lines', 'rules' => ['nullable', 'string']],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
                'force' => ['institution_id' => $institutionId],
            ],
            'faqs' => [
                'label' => 'FAQ',
                'table' => 'pmb_faqs',
                'search' => ['category', 'question', 'answer'],
                'columns' => ['category' => 'Kategori', 'question' => 'Pertanyaan', 'is_active' => 'Aktif'],
                'fields' => [
                    'category' => ['label' => 'Kategori', 'rules' => ['nullable', 'string', 'max:255']],
                    'question' => ['label' => 'Pertanyaan', 'type' => 'textarea', 'rules' => ['required', 'string']],
                    'answer' => ['label' => 'Jawaban', 'type' => 'textarea', 'rules' => ['required', 'string']],
                    'is_active' => ['label' => 'Aktif', 'type' => 'checkbox', 'default' => true],
                    'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0'], 'default' => 0],
                ],
                'force' => ['institution_id' => $institutionId],
            ],
        ];
    }

    private function options(string $table, string $labelColumn = 'name'): array
    {
        return DB::table($table)
            ->orderBy($labelColumn)
            ->pluck($labelColumn, 'id')
            ->map(fn ($label): string => (string) $label)
            ->all();
    }

    private function campusProgramOptions(): array
    {
        return DB::table('campus_study_programs')
            ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
            ->join('study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id')
            ->orderBy('campuses.name')
            ->orderBy('study_programs.name')
            ->select('campus_study_programs.id', 'campuses.name as campus_name', 'study_programs.level', 'study_programs.name as program_name')
            ->get()
            ->mapWithKeys(fn ($row): array => [$row->id => "{$row->campus_name} - {$row->level} {$row->program_name}"])
            ->all();
    }

    private function registrationOptionOptions(): array
    {
        return DB::table('pmb_registration_options')
            ->join('pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_registration_options.admission_period_id')
            ->leftJoin('pmb_waves', 'pmb_waves.id', '=', 'pmb_registration_options.wave_id')
            ->join('campus_study_programs', 'campus_study_programs.id', '=', 'pmb_registration_options.campus_study_program_id')
            ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
            ->join('study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id')
            ->join('admission_paths', 'admission_paths.id', '=', 'pmb_registration_options.admission_path_id')
            ->leftJoin('class_types', 'class_types.id', '=', 'pmb_registration_options.class_type_id')
            ->select('pmb_registration_options.id', 'pmb_admission_periods.name as period_name', 'pmb_waves.name as wave_name', 'campuses.name as campus_name', 'study_programs.name as program_name', 'admission_paths.name as path_name', 'class_types.name as class_name')
            ->get()
            ->mapWithKeys(fn ($row): array => [
                $row->id => "{$row->period_name} / {$row->wave_name} / {$row->campus_name} / {$row->program_name} / {$row->path_name} / {$row->class_name}",
            ])
            ->all();
    }

    private function institutionId(): int
    {
        return (int) DB::table('institutions')->where('is_active', true)->orderBy('id')->value('id');
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);
    }
}
