<?php

namespace App\Services;

use App\Models\PaymentGatewaySetting;
use App\Models\PmbLocalApplication;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class DokuPaymentService
{
    public function __construct(
        private readonly FormPaymentService $formPaymentService,
    ) {}

    /**
     * @return array{paymentUrl: string, invoiceNumber: string, checkoutJsUrl: string, environment: string}
     */
    public function createCheckoutPayment(PmbLocalApplication $application, User $user): array
    {
        $settings = PaymentGatewaySetting::current();

        if (! $settings->isConfigured()) {
            throw new RuntimeException('Pembayaran DOKU belum dikonfigurasi di admin.');
        }

        $amount = (int) ($application->form_payment_amount ?? 0);

        if ($amount <= 0) {
            throw new RuntimeException('Nominal pembayaran formulir tidak valid.');
        }

        if (($application->form_payment_status ?? '') === PmbLocalApplication::FORM_PAYMENT_PAID) {
            throw new RuntimeException('Pembayaran formulir sudah lunas.');
        }

        $invoiceNumber = $application->doku_invoice_number ?: $this->makeInvoiceNumber($application);
        $requestId = (string) Str::uuid();
        $timestamp = now('UTC')->format('Y-m-d\TH:i:s\Z');
        $requestTarget = '/checkout/v1/payment';
        $callbackUrl = $this->callbackUrl($settings);

        $payload = [
            'order' => [
                'amount' => $amount,
                'invoice_number' => $invoiceNumber,
                'currency' => 'IDR',
                'callback_url' => $callbackUrl,
                'callback_url_cancel' => $callbackUrl,
                'line_items' => [[
                    'name' => 'Biaya Formulir Pendaftaran PMB',
                    'price' => $amount,
                    'quantity' => 1,
                ]],
            ],
            'payment' => [
                'payment_due_date' => 60,
            ],
            'customer' => [
                'id' => (string) $user->id,
                'name' => $application->name ?: $user->name,
                'email' => $application->email ?: $user->email,
                'phone' => $this->normalizePhone($application->phone ?: $user->phone),
                'address' => $application->address ?: 'Indonesia',
                'country' => 'ID',
            ],
        ];

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $headers = $this->signedHeaders(
            $settings,
            $requestId,
            $timestamp,
            $requestTarget,
            (string) $body,
        );

        try {
            $response = Http::withHeaders($headers)
                ->withBody((string) $body, 'application/json')
                ->timeout(30)
                ->post($settings->dokuApiBaseUrl().$requestTarget)
                ->throw();
        } catch (RequestException $exception) {
            $detail = $exception->response?->json('message')
                ?? $exception->response?->body()
                ?? $exception->getMessage();

            throw new RuntimeException('Gagal membuat pembayaran DOKU: '.$detail, 0, $exception);
        }

        $data = $response->json();
        $paymentUrl = data_get($data, 'response.payment.url')
            ?? data_get($data, 'payment.url')
            ?? data_get($data, 'payment_url');

        if (! is_string($paymentUrl) || $paymentUrl === '') {
            throw new RuntimeException('Respons DOKU tidak mengandung payment.url.');
        }

        $application->update([
            'doku_invoice_number' => $invoiceNumber,
            'doku_request_id' => $requestId,
            'doku_payment_url' => $paymentUrl,
            'doku_raw_payload' => [
                'create' => $data,
                'created_at' => now()->toIso8601String(),
            ],
        ]);

        return [
            'paymentUrl' => $paymentUrl,
            'invoiceNumber' => $invoiceNumber,
            'checkoutJsUrl' => $settings->dokuCheckoutJsUrl(),
            'environment' => $settings->environment,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleNotification(array $payload, ?string $signatureHeader = null): PmbLocalApplication
    {
        $settings = PaymentGatewaySetting::current();

        if (! $settings->isConfigured()) {
            throw new RuntimeException('Pembayaran DOKU belum dikonfigurasi.');
        }

        $invoiceNumber = (string) (
            data_get($payload, 'order.invoice_number')
            ?? data_get($payload, 'invoice_number')
            ?? ''
        );

        if ($invoiceNumber === '') {
            throw new RuntimeException('invoice_number tidak ditemukan pada notifikasi DOKU.');
        }

        $application = PmbLocalApplication::query()
            ->where('doku_invoice_number', $invoiceNumber)
            ->first();

        if (! $application) {
            throw new RuntimeException('Pendaftaran untuk invoice DOKU tidak ditemukan.');
        }

        $status = strtoupper((string) (
            data_get($payload, 'transaction.status')
            ?? data_get($payload, 'transaction_status')
            ?? data_get($payload, 'status')
            ?? ''
        ));

        $channel = data_get($payload, 'channel.name')
            ?? data_get($payload, 'payment_channel')
            ?? data_get($payload, 'acquirer.channel');

        $raw = $application->doku_raw_payload;
        if (! is_array($raw)) {
            $raw = [];
        }
        $raw['notification'] = $payload;
        $raw['notified_at'] = now()->toIso8601String();

        $application->update([
            'doku_payment_channel' => is_string($channel) ? $channel : $application->doku_payment_channel,
            'doku_raw_payload' => $raw,
        ]);

        $successStatuses = ['SUCCESS', 'PAID', 'SETTLEMENT', 'COMPLETED'];

        if (in_array($status, $successStatuses, true)
            && ($application->form_payment_status ?? '') !== PmbLocalApplication::FORM_PAYMENT_PAID) {
            $this->formPaymentService->markPaid(
                application: $application->fresh(),
                paidBy: null,
                note: 'Pembayaran otomatis via DOKU ('.$status.')',
                bank: null,
                extra: [
                    'doku_paid_at' => now(),
                    'doku_payment_channel' => is_string($channel) ? $channel : null,
                    'doku_raw_payload' => $raw,
                ],
            );
        }

        return $application->fresh();
    }

    /**
     * @return array<string, string>
     */
    private function signedHeaders(
        PaymentGatewaySetting $settings,
        string $requestId,
        string $timestamp,
        string $requestTarget,
        string $body,
    ): array {
        $digest = base64_encode(hash('sha256', $body, true));
        $component = implode("\n", [
            'Client-Id:'.$settings->client_id,
            'Request-Id:'.$requestId,
            'Request-Timestamp:'.$timestamp,
            'Request-Target:'.$requestTarget,
            'Digest:'.$digest,
        ]);

        $signature = 'HMACSHA256='.base64_encode(
            hash_hmac('sha256', $component, (string) $settings->plainSecretKey(), true)
        );

        return [
            'Client-Id' => (string) $settings->client_id,
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function makeInvoiceNumber(PmbLocalApplication $application): string
    {
        return 'PMB-'.$application->id.'-'.now()->format('YmdHis');
    }

    private function callbackUrl(PaymentGatewaySetting $settings): string
    {
        if (filled($settings->callback_url)) {
            return (string) $settings->callback_url;
        }

        $frontend = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');

        return $frontend.'/portal-mahasiswa?view=pembayaran';
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '08000000000';

        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }
}
