<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CampusBranding;
use Illuminate\Http\JsonResponse;

class CampusSettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => CampusBranding::apiPayload(),
        ]);
    }
}
