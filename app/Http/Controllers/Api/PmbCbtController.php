<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PmbCbtAttempt;
use App\Models\PmbCbtAttemptAnswer;
use App\Models\PmbCbtQuestion;
use App\Models\PmbCbtSetting;
use App\Models\PmbLocalApplication;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PmbCbtController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user);

        if (! $application) {
            return response()->json(['message' => 'Simpan formulir pendaftaran terlebih dahulu.'], 422);
        }

        if (
            ($application->form_payment_status ?? 'pending') === PmbLocalApplication::FORM_PAYMENT_PAID
            && ($application->cbt_status ?? PmbLocalApplication::CBT_STATUS_LOCKED) === PmbLocalApplication::CBT_STATUS_LOCKED
        ) {
            $application->update(['cbt_status' => PmbLocalApplication::CBT_STATUS_AVAILABLE]);
            $application->refresh();
        }

        $this->syncExpiredAttempt($application);
        $settings = PmbCbtSetting::current();
        $activeAttempt = $application->cbtAttempts()
            ->where('status', PmbCbtAttempt::STATUS_IN_PROGRESS)
            ->latest('id')
            ->first();

        return response()->json([
            'data' => [
                'settings' => $this->settingsPayload($settings),
                'application' => [
                    'cbtStatus' => $application->cbt_status ?? PmbLocalApplication::CBT_STATUS_LOCKED,
                    'cbtScore' => $application->cbt_score,
                    'cbtAttemptCount' => (int) ($application->cbt_attempt_count ?? 0),
                    'cbtPassedAt' => $application->cbt_passed_at?->toDateTimeString(),
                    'formPaymentStatus' => $application->form_payment_status,
                    'remainingAttempts' => max(0, (int) $settings->max_attempts - (int) ($application->cbt_attempt_count ?? 0)),
                    'canStart' => $this->canStart($application, $settings, $activeAttempt),
                    'canContinue' => $activeAttempt !== null && ! $activeAttempt->isExpired(),
                ],
                'activeAttempt' => $activeAttempt ? $this->attemptPayload($activeAttempt, false) : null,
                'history' => $application->cbtAttempts()
                    ->whereIn('status', [PmbCbtAttempt::STATUS_SUBMITTED, PmbCbtAttempt::STATUS_EXPIRED])
                    ->orderByDesc('id')
                    ->get()
                    ->map(fn (PmbCbtAttempt $attempt): array => $this->attemptPayload($attempt, false))
                    ->values(),
            ],
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user);

        if (! $application) {
            return response()->json(['message' => 'Simpan formulir pendaftaran terlebih dahulu.'], 422);
        }

        $this->syncExpiredAttempt($application);
        $settings = PmbCbtSetting::current();

        if (! $settings->is_active) {
            return response()->json(['message' => 'Tes CBT sedang tidak aktif.'], 422);
        }

        if (($application->form_payment_status ?? 'pending') !== PmbLocalApplication::FORM_PAYMENT_PAID) {
            return response()->json(['message' => 'Anda belum membayar formulir pendaftaran.'], 422);
        }

        if ($application->hasPassedCbt()) {
            return response()->json(['message' => 'Anda sudah lulus tes CBT.'], 422);
        }

        $activeAttempt = $application->cbtAttempts()
            ->where('status', PmbCbtAttempt::STATUS_IN_PROGRESS)
            ->latest('id')
            ->first();

        if ($activeAttempt && ! $activeAttempt->isExpired()) {
            $activeAttempt->load(['answers.question']);

            return response()->json([
                'data' => $this->attemptPayload($activeAttempt, true),
            ]);
        }

        if ((int) ($application->cbt_attempt_count ?? 0) >= (int) $settings->max_attempts) {
            return response()->json(['message' => 'Kesempatan tes CBT sudah habis. Hubungi admin PMB.'], 422);
        }

        $questions = PmbCbtQuestion::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit((int) $settings->questions_per_attempt)
            ->get();

        if ($questions->count() < 1) {
            return response()->json(['message' => 'Bank soal CBT belum tersedia.'], 422);
        }

        $attempt = DB::transaction(function () use ($application, $settings, $questions): PmbCbtAttempt {
            $attemptNumber = (int) ($application->cbt_attempt_count ?? 0) + 1;
            $startedAt = now();

            $attempt = PmbCbtAttempt::query()->create([
                'pmb_local_application_id' => $application->id,
                'attempt_number' => $attemptNumber,
                'status' => PmbCbtAttempt::STATUS_IN_PROGRESS,
                'total_questions' => $questions->count(),
                'started_at' => $startedAt,
                'expires_at' => $startedAt->copy()->addMinutes((int) $settings->duration_minutes),
            ]);

            foreach ($questions->values() as $index => $question) {
                PmbCbtAttemptAnswer::query()->create([
                    'pmb_cbt_attempt_id' => $attempt->id,
                    'pmb_cbt_question_id' => $question->id,
                    'question_order' => $index + 1,
                ]);
            }

            $application->update([
                'cbt_status' => PmbLocalApplication::CBT_STATUS_IN_PROGRESS,
                'cbt_attempt_count' => $attemptNumber,
            ]);

            return $attempt->load(['answers.question']);
        });

        return response()->json([
            'data' => $this->attemptPayload($attempt, true),
        ]);
    }

    public function attempt(Request $request, int $attemptId): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user);

        if (! $application) {
            return response()->json(['message' => 'Pendaftaran tidak ditemukan.'], 422);
        }

        $attempt = $application->cbtAttempts()->with(['answers.question'])->find($attemptId);

        if (! $attempt) {
            return response()->json(['message' => 'Sesi tes tidak ditemukan.'], 404);
        }

        if ($attempt->status === PmbCbtAttempt::STATUS_IN_PROGRESS && $attempt->isExpired()) {
            $this->finalizeExpiredAttempt($attempt, $application);
            $attempt->refresh()->load(['answers.question']);
        }

        return response()->json([
            'data' => $this->attemptPayload($attempt, $attempt->status === PmbCbtAttempt::STATUS_IN_PROGRESS),
        ]);
    }

    public function submit(Request $request, int $attemptId): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user);

        if (! $application) {
            return response()->json(['message' => 'Pendaftaran tidak ditemukan.'], 422);
        }

        $attempt = $application->cbtAttempts()->with(['answers.question'])->find($attemptId);

        if (! $attempt) {
            return response()->json(['message' => 'Sesi tes tidak ditemukan.'], 404);
        }

        if ($attempt->status !== PmbCbtAttempt::STATUS_IN_PROGRESS) {
            return response()->json(['message' => 'Sesi tes sudah diselesaikan.'], 422);
        }

        $payload = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.questionId' => ['required', 'integer'],
            'answers.*.selectedOption' => ['nullable', 'string', 'in:A,B,C,D'],
        ]);

        $settings = PmbCbtSetting::current();
        $answersByQuestion = collect($payload['answers'])->keyBy('questionId');

        $result = DB::transaction(function () use ($attempt, $application, $answersByQuestion, $settings): PmbCbtAttempt {
            $correctCount = 0;

            foreach ($attempt->answers as $answer) {
                $selected = $answersByQuestion->get($answer->pmb_cbt_question_id)['selectedOption'] ?? null;
                $isCorrect = $selected !== null
                    && strtoupper((string) $selected) === strtoupper((string) $answer->question->correct_option);

                if ($isCorrect) {
                    $correctCount++;
                }

                $answer->update([
                    'selected_option' => $selected ? strtoupper((string) $selected) : null,
                    'is_correct' => $isCorrect,
                ]);
            }

            $total = max(1, (int) $attempt->total_questions);
            $score = (int) round(($correctCount / $total) * 100);
            $passed = $score >= (int) $settings->pass_score;

            $attempt->update([
                'status' => PmbCbtAttempt::STATUS_SUBMITTED,
                'score' => $score,
                'correct_count' => $correctCount,
                'passed' => $passed,
                'submitted_at' => now(),
            ]);

            $application->update([
                'cbt_status' => $passed
                    ? PmbLocalApplication::CBT_STATUS_PASSED
                    : PmbLocalApplication::CBT_STATUS_FAILED,
                'cbt_score' => $score,
                'cbt_passed_at' => $passed ? now() : null,
            ]);

            return $attempt->fresh(['answers.question']);
        });

        return response()->json([
            'data' => $this->attemptPayload($result, false),
            'application' => [
                'cbtStatus' => $application->fresh()->cbt_status,
                'cbtScore' => $application->fresh()->cbt_score,
                'cbtPassedAt' => $application->fresh()->cbt_passed_at?->toDateTimeString(),
                'cbtAttemptCount' => (int) $application->fresh()->cbt_attempt_count,
            ],
        ]);
    }

    private function canStart(PmbLocalApplication $application, PmbCbtSetting $settings, ?PmbCbtAttempt $activeAttempt): bool
    {
        if (! $settings->is_active) {
            return false;
        }

        if (($application->form_payment_status ?? 'pending') !== PmbLocalApplication::FORM_PAYMENT_PAID) {
            return false;
        }

        if ($application->hasPassedCbt()) {
            return false;
        }

        if ($activeAttempt && ! $activeAttempt->isExpired()) {
            return false;
        }

        return (int) ($application->cbt_attempt_count ?? 0) < (int) $settings->max_attempts;
    }

    private function syncExpiredAttempt(PmbLocalApplication $application): void
    {
        $activeAttempt = $application->cbtAttempts()
            ->where('status', PmbCbtAttempt::STATUS_IN_PROGRESS)
            ->latest('id')
            ->first();

        if ($activeAttempt && $activeAttempt->isExpired()) {
            $this->finalizeExpiredAttempt($activeAttempt, $application);
        }
    }

    private function finalizeExpiredAttempt(PmbCbtAttempt $attempt, PmbLocalApplication $application): void
    {
        if ($attempt->status !== PmbCbtAttempt::STATUS_IN_PROGRESS) {
            return;
        }

        $settings = PmbCbtSetting::current();
        $attempt->loadMissing(['answers.question']);

        $correctCount = 0;

        foreach ($attempt->answers as $answer) {
            $isCorrect = $answer->selected_option !== null
                && strtoupper((string) $answer->selected_option) === strtoupper((string) $answer->question->correct_option);

            if ($isCorrect) {
                $correctCount++;
            }

            $answer->update(['is_correct' => $isCorrect]);
        }

        $total = max(1, (int) $attempt->total_questions);
        $score = (int) round(($correctCount / $total) * 100);
        $passed = $score >= (int) $settings->pass_score;

        $attempt->update([
            'status' => PmbCbtAttempt::STATUS_EXPIRED,
            'score' => $score,
            'correct_count' => $correctCount,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);

        if (! $application->hasPassedCbt()) {
            $application->update([
                'cbt_status' => $passed
                    ? PmbLocalApplication::CBT_STATUS_PASSED
                    : PmbLocalApplication::CBT_STATUS_FAILED,
                'cbt_score' => $score,
                'cbt_passed_at' => $passed ? now() : null,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsPayload(PmbCbtSetting $settings): array
    {
        return [
            'title' => $settings->title,
            'durationMinutes' => (int) $settings->duration_minutes,
            'questionsPerAttempt' => (int) $settings->questions_per_attempt,
            'passScore' => (int) $settings->pass_score,
            'maxAttempts' => (int) $settings->max_attempts,
            'instructions' => $settings->instructions,
            'isActive' => (bool) $settings->is_active,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attemptPayload(PmbCbtAttempt $attempt, bool $includeQuestions): array
    {
        $payload = [
            'id' => $attempt->id,
            'attemptNumber' => (int) $attempt->attempt_number,
            'status' => $attempt->status,
            'score' => $attempt->score,
            'totalQuestions' => (int) $attempt->total_questions,
            'correctCount' => (int) $attempt->correct_count,
            'passed' => $attempt->passed,
            'startedAt' => $attempt->started_at?->toDateTimeString(),
            'expiresAt' => $attempt->expires_at?->toDateTimeString(),
            'submittedAt' => $attempt->submitted_at?->toDateTimeString(),
            'remainingSeconds' => $attempt->expires_at
                ? max(0, $attempt->expires_at->getTimestamp() - now()->getTimestamp())
                : 0,
        ];

        if ($includeQuestions) {
            $payload['questions'] = $attempt->answers->map(function (PmbCbtAttemptAnswer $answer): array {
                $question = $answer->question;

                return [
                    'id' => $question->id,
                    'order' => (int) $answer->question_order,
                    'category' => $question->category,
                    'question' => $question->question,
                    'options' => $question->options,
                    'selectedOption' => $answer->selected_option,
                ];
            })->values();
        }

        return $payload;
    }

    private function applicationForUser(User $user): ?PmbLocalApplication
    {
        return PmbLocalApplication::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
    }

    private function userFromBearerToken(Request $request): ?User
    {
        $token = $request->bearerToken();

        if (! $token || ! str_contains($token, '|')) {
            return null;
        }

        [$userId, $plainToken] = explode('|', $token, 2);
        $user = User::query()->find($userId);

        if (! $user || ! $user->api_token) {
            return null;
        }

        return hash_equals($user->api_token, hash('sha256', $plainToken)) ? $user : null;
    }
}
