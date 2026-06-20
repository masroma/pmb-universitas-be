<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $campusSetting = CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);

        return view('admin.dashboard', [
            'campusSetting' => $campusSetting,
        ]);
    }
}
