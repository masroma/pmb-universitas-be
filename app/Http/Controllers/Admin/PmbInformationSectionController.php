<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbInformationSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PmbInformationSectionController extends Controller
{
    public const CATEGORIES = [
        'info-program' => 'Info Program',
        'lokasi-kampus' => 'Lokasi Kampus',
        'jadwal' => 'Jadwal',
        'kelas' => 'Waktu Kuliah',
        'syarat' => 'Syarat Masuk',
        'kurikulum' => 'Kurikulum',
        'biaya' => 'Catatan Biaya',
        'kontak' => 'Kontak & Link',
    ];

    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $selectedProgramLevel = $request->string('program_level')->toString();
        $selectedCategory = $request->string('category')->toString();

        $sections = PmbInformationSection::query()
            ->when($selectedProgramLevel !== '', fn ($query) => $query->where('program_level', $selectedProgramLevel))
            ->when($selectedCategory !== '', fn ($query) => $query->where('category', $selectedCategory))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            }))
            ->orderBy('program_level')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pmb-information.index', [
            'campusSetting' => $this->campusSetting(),
            'categories' => self::CATEGORIES,
            'programLevels' => $this->programLevels(),
            'search' => $search,
            'sections' => $sections,
            'selectedCategory' => $selectedCategory,
            'selectedProgramLevel' => $selectedProgramLevel,
            'totalActiveSections' => PmbInformationSection::query()->where('is_active', true)->count(),
            'totalSections' => PmbInformationSection::query()->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pmb-information.create', [
            'campusSetting' => $this->campusSetting(),
            'categories' => self::CATEGORIES,
            'programLevels' => $this->programLevels(),
            'section' => new PmbInformationSection([
                'program_level' => 'Umum',
                'category' => 'info-program',
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        PmbInformationSection::query()->create($this->validatedData($request));

        return redirect()
            ->route('admin.pmb-information.index')
            ->with('status', 'Konten PMB berhasil ditambahkan.');
    }

    public function edit(PmbInformationSection $pmbInformation): View
    {
        return view('admin.pmb-information.edit', [
            'campusSetting' => $this->campusSetting(),
            'categories' => self::CATEGORIES,
            'programLevels' => $this->programLevels(),
            'section' => $pmbInformation,
        ]);
    }

    public function update(Request $request, PmbInformationSection $pmbInformation): RedirectResponse
    {
        $pmbInformation->update($this->validatedData($request));

        return redirect()
            ->route('admin.pmb-information.index', [
                'q' => $request->string('q')->toString(),
                'program_level' => $request->string('filter_program_level')->toString(),
                'category' => $request->string('filter_category')->toString(),
                'page' => $request->string('page')->toString(),
            ])
            ->with('status', 'Konten PMB berhasil diperbarui.');
    }

    public function destroy(PmbInformationSection $pmbInformation): RedirectResponse
    {
        $pmbInformation->delete();

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

        $validated['items'] = collect(preg_split('/\r\n|\r|\n/', $validated['items_text'] ?? ''))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['items_text']);

        return $validated;
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);
    }

    private function programLevels(): array
    {
        return ['Umum', 'S1', 'S2', 'S3'];
    }
}
