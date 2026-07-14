<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ResolvesApiUser;
use App\Models\PaymentGatewaySetting;
use App\Models\PmbLocalApplication;
use App\Services\DokuPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DokuPaymentController extends Controller
{
    use ResolvesApiUser;

    public function __construct(
        private readonly DokuPaymentService $dokuPaymentService,
    ) {}

    public function create(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = PmbLocalApplication::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $application) {
            return response()->json(['message' => 'Data pendaftaran belum ditemukan.'], 404);
        }

        if (! $application->program_option_id) {
            return response()->json(['message' => 'Lengkapi pilihan program terlebih dahulu.'], 422);
        }

        try {
            $payment = $this->dokuPaymentService->createCheckoutPayment($application, $user);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'data' => $payment,
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = PmbLocalApplication::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $application) {
            return response()->json(['message' => 'Data pendaftaran belum ditemukan.'], 404);
        }

        $settings = PaymentGatewaySetting::current();

        return response()->json([
            'data' => [
                'formPaymentStatus' => $application->form_payment_status ?? PmbLocalApplication::FORM_PAYMENT_PENDING,
                'dokuInvoiceNumber' => $application->doku_invoice_number,
                'dokuPaymentUrl' => $application->doku_payment_url,
                'dokuPaymentChannel' => $application->doku_payment_channel,
                'dokuPaidAt' => $application->doku_paid_at?->toDateTimeString(),
                'dokuEnabled' => $settings->isConfigured(),
                'checkoutJsUrl' => $settings->isConfigured() ? $settings->dokuCheckoutJsUrl() : null,
            ],
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            $application = $this->dokuPaymentService->handleNotification(
                $request->all(),
                $request->header('Signature'),
            );
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'OK',
            'invoice_number' => $application->doku_invoice_number,
            'form_payment_status' => $application->form_payment_status,
        ]);
    }
}
