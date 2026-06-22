<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PmbInformationSectionController extends Controller
{
    public const CATEGORIES = [
        'info-program' => 'Info Program',
        'keunggulan' => 'Keunggulan Kampus',
        'lokasi-kampus' => 'Lokasi Kampus',
        'jadwal' => 'Jadwal',
        'kelas' => 'Waktu Kuliah',
        'syarat' => 'Syarat Masuk',
        'alur-pendaftaran' => 'Alur Pendaftaran',
        'kurikulum' => 'Kurikulum',
        'biaya' => 'Catatan Biaya',
        'kontak' => 'Kontak & Link',
    ];

    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $selectedProgramLevel = $request->string('program_level')->toString();
        $selectedCategory = $request->string('category')->toString();

        $sections = DB::table('pmb_content_blocks')
            ->when($selectedCategory !== '', fn ($query) => $query->where('category', $selectedCategory))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            }))
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        $sections->setCollection($sections->getCollection()->map(fn ($section) => $this->hydrateSection($section)));

        return view('admin.pmb-information.index', [
            'campusSetting' => $this->campusSetting(),
            'categories' => self::CATEGORIES,
            'programLevels' => $this->programLevels(),
            'search' => $search,
            'sections' => $sections,
            'selectedCategory' => $selectedCategory,
            'selectedProgramLevel' => $selectedProgramLevel,
            'totalActiveSections' => DB::table('pmb_content_blocks')->where('is_active', true)->count(),
            'totalSections' => DB::table('pmb_content_blocks')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pmb-information.create', [
            'campusSetting' => $this->campusSetting(),
            'categories' => self::CATEGORIES,
            'programLevels' => $this->programLevels(),
            'section' => (object) [
                'id' => null,
                'program_level' => 'Umum',
                'category' => 'info-program',
                'title' => '',
                'subtitle' => '',
                'body' => '',
                'items' => [],
                'is_active' => true,
                'sort_order' => 0,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DB::table('pmb_content_blocks')->insert([
            ...$this->validatedData($request),
            'institution_id' => $this->institutionId(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.pmb-information.index')
            ->with('status', 'Konten PMB berhasil ditambahkan.');
    }

    public function edit(int $pmbInformation): View
    {
        $section = DB::table('pmb_content_blocks')->where('id', $pmbInformation)->first();
        abort_if(! $section, 404);

        return view('admin.pmb-information.edit', [
            'campusSetting' => $this->campusSetting(),
            'categories' => self::CATEGORIES,
            'programLevels' => $this->programLevels(),
            'section' => $this->hydrateSection($section),
        ]);
    }

    public function update(Request $request, int $pmbInformation): RedirectResponse
    {
        DB::table('pmb_content_blocks')
            ->where('id', $pmbInformation)
            ->update([
                ...$this->validatedData($request),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.pmb-information.index', [
                'q' => $request->string('q')->toString(),
                'program_level' => $request->string('filter_program_level')->toString(),
                'category' => $request->string('filter_category')->toString(),
                'page' => $request->string('page')->toString(),
            ])
            ->with('status', 'Konten PMB berhasil diperbarui.');
    }

    public function destroy(int $pmbInformation): RedirectResponse
    {
        DB::table('pmb_content_blocks')->where('id', $pmbInformation)->delete();

        return redirect()
            ->route('admin.pmb-information.index')
            ->with('status', 'Konten PMB berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'program_level' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'items_text' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);

        $items = collect(preg_split('/\r\n|\r|\n/', $validated['items_text'] ?? ''))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();

        return [
            'category' => $validated['category'],
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'body' => $validated['body'] ?? null,
            'items' => json_encode($items),
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function hydrateSection(object $section): object
    {
        $section->program_level = 'Umum';
        $section->items = $this->decodeJson($section->items ?? null);

        return $section;
    }

    private function institutionId(): int
    {
        $institutionId = DB::table('institutions')->where('is_active', true)->orderBy('id')->value('id');

        if ($institutionId) {
            return (int) $institutionId;
        }

        return (int) DB::table('institutions')->insertGetId([
            'code' => 'default',
            'name' => config('app.name', 'Kampus'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function decodeJson(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);
    }

    private function programLevels(): array
    {
        return ['Umum'];
    }
}
