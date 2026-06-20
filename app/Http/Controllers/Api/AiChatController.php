<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'conversation_id' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $response = Http::timeout((int) config('services.ai_pmb.timeout', 20))
                ->acceptJson()
                ->asJson()
                ->post(config('services.ai_pmb.url'), $payload);
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Layanan AI PMB belum dapat dihubungi.',
            ], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'Layanan AI PMB belum dapat menjawab pertanyaan.',
                'detail' => $response->json('detail'),
            ], 502);
        }

        return response()->json([
            'data' => $response->json(),
        ]);
    }
}
