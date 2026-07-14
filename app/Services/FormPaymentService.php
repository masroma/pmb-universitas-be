<?php

namespace App\Services;

use App\Models\PmbLocalApplication;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class FormPaymentService
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function markPaid(
        PmbLocalApplication $application,
        ?User $paidBy = null,
        ?string $note = null,
        ?string $bank = null,
        array $extra = [],
        ?Request $request = null,
    ): PmbLocalApplication {
        $before = $application->only([
            'form_payment_status',
            'form_payment_bank',
            'form_payment_amount',
            'form_paid_at',
            'form_paid_by',
            'form_payment_note',
            'doku_invoice_number',
            'doku_payment_channel',
            'doku_paid_at',
        ]);

        $cbtUpdate = [];
        if (($application->cbt_status ?? PmbLocalApplication::CBT_STATUS_LOCKED) === PmbLocalApplication::CBT_STATUS_LOCKED) {
            $cbtUpdate['cbt_status'] = PmbLocalApplication::CBT_STATUS_AVAILABLE;
        }

        $application->update([
            'form_payment_status' => PmbLocalApplication::FORM_PAYMENT_PAID,
            'form_payment_bank' => $bank,
            'form_payment_note' => $note ?? $application->form_payment_note,
            'form_paid_at' => now(),
            'form_paid_by' => $paidBy?->id,
            'status' => PmbLocalApplication::STATUS_DRAFT,
            'doku_paid_at' => $extra['doku_paid_at'] ?? now(),
            'doku_payment_channel' => $extra['doku_payment_channel'] ?? $application->doku_payment_channel,
            'doku_raw_payload' => $extra['doku_raw_payload'] ?? $application->doku_raw_payload,
            ...$cbtUpdate,
        ]);

        AuditLogger::record(
            'application_form_payment_updated',
            'pmb_local_applications',
            $application->id,
            $before,
            $application->fresh()->only([
                'form_payment_status',
                'form_payment_bank',
                'form_payment_amount',
                'form_paid_at',
                'form_paid_by',
                'form_payment_note',
                'doku_invoice_number',
                'doku_payment_channel',
                'doku_paid_at',
            ]),
            $request,
        );

        return $application->fresh();
    }
}
