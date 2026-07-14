<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PmbCbtAiQuestionService
{
    /**
     * @param  array{category: string, categoryLabel: string, count: int, topic?: string|null, difficulty?: string}  $input
     * @return list<array{question: string, options: array<string, string>, correct_option: string}>
     */
    public function generate(array $input): array
    {
        $apiKey = (string) config('services.openai.api_key', '');

        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY belum dikonfigurasi di backend.');
        }

        $count = max(1, min(10, (int) $input['count']));
        $category = (string) $input['category'];
        $categoryLabel = (string) $input['categoryLabel'];
        $topic = trim((string) ($input['topic'] ?? ''));
        $difficulty = (string) ($input['difficulty'] ?? 'sedang');

        $topicLine = $topic !== ''
            ? "Fokus topik: {$topic}."
            : 'Topik bebas yang relevan untuk seleksi masuk perguruan tinggi.';

        $prompt = <<<PROMPT
Buatkan {$count} soal pilihan ganda untuk tes seleksi PMB (Computer Based Test).

Kategori: {$categoryLabel} ({$category})
Tingkat kesulitan: {$difficulty}
{$topicLine}

Ketentuan:
- Bahasa Indonesia
- Setiap soal punya tepat 4 opsi: A, B, C, D
- Hanya satu jawaban benar
- Soal jelas, tidak ambigu, dan layak untuk calon mahasiswa
- Jangan ulangi soal yang terlalu mirip

Kembalikan JSON dengan format tepat:
{
  "questions": [
    {
      "question": "teks pertanyaan",
      "options": {
        "A": "opsi A",
        "B": "opsi B",
        "C": "opsi C",
        "D": "opsi D"
      },
      "correct_option": "A"
    }
  ]
}
PROMPT;

        $response = Http::timeout((int) config('services.openai.timeout', 60))
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu adalah pembuat soal CBT PMB yang teliti. Jawab hanya dengan JSON valid sesuai skema yang diminta.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            $detail = $response->json('error.message') ?: $response->body();

            throw new RuntimeException('Gagal memanggil OpenAI: '.$detail);
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content', '');
        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! isset($decoded['questions']) || ! is_array($decoded['questions'])) {
            throw new RuntimeException('Respons AI tidak berisi daftar soal yang valid.');
        }

        $normalized = [];

        foreach ($decoded['questions'] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? ''));
            $options = $item['options'] ?? null;
            $correct = strtoupper(trim((string) ($item['correct_option'] ?? '')));

            if ($question === '' || ! is_array($options)) {
                continue;
            }

            $mappedOptions = [
                'A' => trim((string) ($options['A'] ?? '')),
                'B' => trim((string) ($options['B'] ?? '')),
                'C' => trim((string) ($options['C'] ?? '')),
                'D' => trim((string) ($options['D'] ?? '')),
            ];

            if (in_array('', $mappedOptions, true) || ! in_array($correct, ['A', 'B', 'C', 'D'], true)) {
                continue;
            }

            $normalized[] = [
                'question' => $question,
                'options' => $mappedOptions,
                'correct_option' => $correct,
            ];
        }

        if ($normalized === []) {
            throw new RuntimeException('AI tidak menghasilkan soal yang dapat disimpan.');
        }

        return array_slice($normalized, 0, $count);
    }
}
