<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewaySetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'provider',
        'environment',
        'client_id',
        'secret_key',
        'callback_url',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([
            'is_enabled' => false,
            'provider' => 'doku',
            'environment' => 'sandbox',
        ]);
    }

    public function setSecretKeyAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['secret_key'] = Crypt::encryptString($value);
    }

    public function plainSecretKey(): ?string
    {
        $value = $this->attributes['secret_key'] ?? null;

        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function maskedSecretKey(): ?string
    {
        $plain = $this->plainSecretKey();

        if (! $plain) {
            return null;
        }

        if (strlen($plain) <= 8) {
            return str_repeat('*', strlen($plain));
        }

        return substr($plain, 0, 4).str_repeat('*', max(4, strlen($plain) - 8)).substr($plain, -4);
    }

    public function isConfigured(): bool
    {
        return $this->is_enabled
            && filled($this->client_id)
            && filled($this->plainSecretKey());
    }

    public function isSandbox(): bool
    {
        return ($this->environment ?? 'sandbox') !== 'production';
    }

    public function dokuApiBaseUrl(): string
    {
        return $this->isSandbox()
            ? 'https://api-sandbox.doku.com'
            : 'https://api.doku.com';
    }

    public function dokuCheckoutJsUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox.doku.com/jokul-checkout-js/v1/jokul-checkout-1.0.0.js'
            : 'https://jokul.doku.com/jokul-checkout-js/v1/jokul-checkout-1.0.0.js';
    }
}
