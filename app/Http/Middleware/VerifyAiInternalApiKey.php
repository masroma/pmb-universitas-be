<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAiInternalApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.ai_pmb.internal_api_key', '');

        if ($configuredKey === '') {
            if (app()->environment('local', 'testing')) {
                return $next($request);
            }

            return response()->json([
                'message' => 'AI internal API key belum dikonfigurasi.',
            ], 503);
        }

        $providedKey = (string) $request->header('X-AI-Internal-Key', '');

        if (! hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
