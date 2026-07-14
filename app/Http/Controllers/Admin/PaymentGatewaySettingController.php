<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentGatewaySettingController extends Controller
{
    public function edit(): View
    {
        $setting = PaymentGatewaySetting::current();

        return view('admin.payment-gateway.edit', [
            'setting' => $setting,
            'campusSetting' => $this->campusSetting(),
            'notificationUrl' => url('/api/webhooks/doku'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = PaymentGatewaySetting::current();

        $validated = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'environment' => ['required', 'in:sandbox,production'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string', 'max:2000'],
            'callback_url' => ['nullable', 'url', 'max:500'],
        ]);

        $setting->is_enabled = $request->boolean('is_enabled');
        $setting->provider = 'doku';
        $setting->environment = $validated['environment'];
        $setting->client_id = $validated['client_id'] ?: null;
        $setting->callback_url = $validated['callback_url'] ?: null;

        if (filled($validated['secret_key'] ?? null)) {
            $setting->secret_key = $validated['secret_key'];
        }

        $setting->save();

        return redirect()
            ->route('admin.payment-gateway.edit')
            ->with('status', 'Pengaturan pembayaran DOKU berhasil disimpan.');
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first() ?? CampusSetting::query()->create([
                'campus_name' => 'Universitas',
            ]);
    }
}
