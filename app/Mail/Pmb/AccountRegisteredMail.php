<?php

namespace App\Mail\Pmb;

use App\Models\CampusSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public CampusSetting $campusSetting,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun PMB '.$this->campusSetting->campus_name.' Berhasil Dibuat',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.pmb.account-registered',
        );
    }
}
