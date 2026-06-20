<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use Illuminate\Http\JsonResponse;

class CampusSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = CampusSetting::query()->firstOrCreate(
            ['id' => 1],
            ['campus_name' => config('app.name')],
        );

        return response()->json([
            'data' => $settings,
        ]);
    }
}
