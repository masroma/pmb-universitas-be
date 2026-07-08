<?php

namespace App\Services;

use App\Models\PmbLocalApplication;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PmbMailService
{
    public function sendToUser(?User $user, Mailable $mailable): void
    {
        if (! $user || blank($user->email)) {
            return;
        }

        $this->sendToAddress($user->email, $mailable);
    }

    public function sendToApplication(PmbLocalApplication $application, Mailable $mailable): void
    {
        $email = $application->email ?: $application->user?->email;

        if (blank($email)) {
            return;
        }

        $this->sendToAddress($email, $mailable);
    }

    private function sendToAddress(string $email, Mailable $mailable): void
    {
        try {
            Mail::to($email)->send($mailable);
        } catch (\Throwable $exception) {
            Log::error('PMB email gagal dikirim.', [
                'email' => $email,
                'mailable' => $mailable::class,
                'message' => $exception->getMessage(),
            ]);

            report($exception);
        }
    }
}
