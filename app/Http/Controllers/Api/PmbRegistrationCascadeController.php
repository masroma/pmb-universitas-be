<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PmbRegistrationCascadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PmbRegistrationCascadeController extends Controller
{
    public function __construct(
        private readonly PmbRegistrationCascadeService $cascade,
    ) {}

    public function jenjang(): JsonResponse
    {
        return response()->json(['data' => $this->cascade->jenjang()]);
    }

    public function programStudi(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'jenjang' => ['required', 'string', 'max:10'],
        ]);

        return response()->json([
            'data' => $this->cascade->programStudi($payload['jenjang']),
        ]);
    }

    public function lokasi(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'jenjang' => ['required', 'string', 'max:10'],
            'study_program_id' => ['required', 'integer'],
        ]);

        return response()->json([
            'data' => $this->cascade->lokasi($payload['jenjang'], (int) $payload['study_program_id']),
        ]);
    }

    public function jenisPendaftaran(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'jenjang' => ['required', 'string', 'max:10'],
            'study_program_id' => ['required', 'integer'],
            'lokasi' => ['required', 'string', 'max:100'],
        ]);

        return response()->json([
            'data' => $this->cascade->jenisPendaftaran(
                $payload['jenjang'],
                (int) $payload['study_program_id'],
                $payload['lokasi'],
            ),
        ]);
    }

    public function waktuPerkuliahan(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'jenjang' => ['required', 'string', 'max:10'],
            'study_program_id' => ['required', 'integer'],
            'lokasi' => ['required', 'string', 'max:100'],
            'jenis_pendaftaran' => ['required', 'string', 'max:100'],
        ]);

        return response()->json([
            'data' => $this->cascade->waktuPerkuliahan(
                $payload['jenjang'],
                (int) $payload['study_program_id'],
                $payload['lokasi'],
                $payload['jenis_pendaftaran'],
            ),
        ]);
    }

    public function jalurMasuk(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'jenjang' => ['required', 'string', 'max:10'],
            'study_program_id' => ['required', 'integer'],
            'lokasi' => ['required', 'string', 'max:100'],
            'waktu_perkuliahan' => ['required', 'string', 'max:255'],
            'jenis_pendaftaran' => ['required', 'string', 'max:100'],
        ]);

        return response()->json([
            'data' => $this->cascade->jalurMasuk(
                $payload['jenjang'],
                (int) $payload['study_program_id'],
                $payload['lokasi'],
                $payload['waktu_perkuliahan'],
                $payload['jenis_pendaftaran'],
            ),
        ]);
    }

    public function resolve(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'jenjang' => ['required', 'string', 'max:10'],
            'study_program_id' => ['required', 'integer'],
            'lokasi' => ['required', 'string', 'max:100'],
            'jenis_pendaftaran' => ['required', 'string', 'max:100'],
            'waktu_perkuliahan' => ['required', 'string', 'max:255'],
            'jalur_masuk_id' => ['required', 'integer'],
        ]);

        $result = $this->cascade->resolve(
            $payload['jenjang'],
            (int) $payload['study_program_id'],
            $payload['lokasi'],
            $payload['jenis_pendaftaran'],
            $payload['waktu_perkuliahan'],
            (int) $payload['jalur_masuk_id'],
        );

        if (! ($result['matched'] ?? false)) {
            return response()->json(['message' => 'Kombinasi pilihan tidak ditemukan.'], 422);
        }

        return response()->json(['data' => $result]);
    }
}
