<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbCbtQuestion;
use App\Models\PmbCbtSetting;
use App\Services\PmbCbtAiQuestionService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PmbCbtAdminController extends Controller
{
    public const CATEGORIES = [
        'umum' => 'Pengetahuan Umum',
        'penalaran' => 'Penalaran',
        'numerik' => 'Numerik',
        'bahasa' => 'Bahasa',
    ];

    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $selectedCategory = $request->string('category')->toString();
        $selectedStatus = $request->string('status')->toString();

        $questions = PmbCbtQuestion::query()
            ->when($selectedCategory !== '', fn ($query) => $query->where('category', $selectedCategory))
            ->when($selectedStatus === 'active', fn ($query) => $query->where('is_active', true))
            ->when($selectedStatus === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('question', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pmb-cbt.index', [
            'campusSetting' => $this->campusSetting(),
            'categories' => self::CATEGORIES,
            'questions' => $questions,
            'search' => $search,
            'selectedCategory' => $selectedCategory,
            'selectedStatus' => $selectedStatus,
            'settings' => PmbCbtSetting::current(),
            'openaiConfigured' => filled(config('services.openai.api_key')),
            'totalQuestions' => PmbCbtQuestion::query()->count(),
            'totalActiveQuestions' => PmbCbtQuestion::query()->where('is_active', true)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pmb-cbt.create', [
            'campusSetting' => $this->campusSetting(),
            'categories' => self::CATEGORIES,
            'question' => new PmbCbtQuestion([
                'category' => 'umum',
                'question' => '',
                'options' => ['A' => '', 'B' => '', 'C' => '', 'D' => ''],
                'correct_option' => 'A',
                'sort_order' => (int) PmbCbtQuestion::query()->max('sort_order') + 1,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedQuestion($request);
        $question = PmbCbtQuestion::query()->create($data);

        AuditLogger::record('created', 'pmb_cbt_questions', $question->id, null, $data, $request);

        return redirect()
            ->route('admin.pmb-cbt.index')
            ->with('status', 'Soal CBT berhasil ditambahkan.');
    }

    public function edit(PmbCbtQuestion $question): View
    {
        return view('admin.pmb-cbt.edit', [
            'campusSetting' => $this->campusSetting(),
            'categories' => self::CATEGORIES,
            'question' => $question,
        ]);
    }

    public function update(Request $request, PmbCbtQuestion $question): RedirectResponse
    {
        $before = $question->only([
            'category',
            'question',
            'options',
            'correct_option',
            'sort_order',
            'is_active',
        ]);
        $data = $this->validatedQuestion($request);
        $question->update($data);

        AuditLogger::record('updated', 'pmb_cbt_questions', $question->id, $before, $data, $request);

        return redirect()
            ->route('admin.pmb-cbt.index', $request->only(['q', 'category', 'status', 'page']))
            ->with('status', 'Soal CBT berhasil diperbarui.');
    }

    public function destroy(Request $request, PmbCbtQuestion $question): RedirectResponse
    {
        $before = $question->only([
            'category',
            'question',
            'options',
            'correct_option',
            'sort_order',
            'is_active',
        ]);
        $questionId = $question->id;
        $question->delete();

        AuditLogger::record('deleted', 'pmb_cbt_questions', $questionId, $before, null, $request);

        return redirect()
            ->route('admin.pmb-cbt.index')
            ->with('status', 'Soal CBT berhasil dihapus.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'questions_per_attempt' => ['required', 'integer', 'min:1', 'max:100'],
            'pass_score' => ['required', 'integer', 'min:1', 'max:100'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $settings = PmbCbtSetting::current();
        $before = $settings->only([
            'title',
            'duration_minutes',
            'questions_per_attempt',
            'pass_score',
            'max_attempts',
            'instructions',
            'is_active',
        ]);

        $settings->update([
            ...$payload,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::record(
            'updated',
            'pmb_cbt_settings',
            $settings->id,
            $before,
            $settings->fresh()->only([
                'title',
                'duration_minutes',
                'questions_per_attempt',
                'pass_score',
                'max_attempts',
                'instructions',
                'is_active',
            ]),
            $request,
        );

        return redirect()
            ->route('admin.pmb-cbt.index')
            ->with('status', 'Pengaturan CBT berhasil disimpan.');
    }

    public function generate(Request $request, PmbCbtAiQuestionService $generator): RedirectResponse
    {
        $payload = $request->validate([
            'category' => ['required', 'in:'.implode(',', array_keys(self::CATEGORIES))],
            'count' => ['required', 'integer', 'min:1', 'max:10'],
            'topic' => ['nullable', 'string', 'max:255'],
            'difficulty' => ['required', 'in:mudah,sedang,sulit'],
        ]);

        try {
            $generated = $generator->generate([
                'category' => $payload['category'],
                'categoryLabel' => self::CATEGORIES[$payload['category']],
                'count' => (int) $payload['count'],
                'topic' => $payload['topic'] ?? null,
                'difficulty' => $payload['difficulty'],
            ]);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.pmb-cbt.index')
                ->with('error', $exception->getMessage());
        }

        $sortOrder = (int) PmbCbtQuestion::query()->max('sort_order');
        $createdIds = [];

        foreach ($generated as $item) {
            $sortOrder++;
            $question = PmbCbtQuestion::query()->create([
                'category' => $payload['category'],
                'question' => $item['question'],
                'options' => $item['options'],
                'correct_option' => $item['correct_option'],
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]);
            $createdIds[] = $question->id;
        }

        AuditLogger::record(
            'cbt_questions_generated',
            'pmb_cbt_questions',
            $createdIds[0] ?? null,
            null,
            [
                'count' => count($createdIds),
                'category' => $payload['category'],
                'topic' => $payload['topic'] ?? null,
                'difficulty' => $payload['difficulty'],
                'ids' => $createdIds,
            ],
            $request,
        );

        return redirect()
            ->route('admin.pmb-cbt.index')
            ->with('status', count($createdIds).' soal berhasil digenerate oleh AI dan disimpan ke bank soal.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedQuestion(Request $request): array
    {
        $payload = $request->validate([
            'category' => ['required', 'string', 'max:50'],
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string', 'max:500'],
            'option_b' => ['required', 'string', 'max:500'],
            'option_c' => ['required', 'string', 'max:500'],
            'option_d' => ['required', 'string', 'max:500'],
            'correct_option' => ['required', 'in:A,B,C,D'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'category' => $payload['category'],
            'question' => $payload['question'],
            'options' => [
                'A' => $payload['option_a'],
                'B' => $payload['option_b'],
                'C' => $payload['option_c'],
                'D' => $payload['option_d'],
            ],
            'correct_option' => $payload['correct_option'],
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);
    }
}
