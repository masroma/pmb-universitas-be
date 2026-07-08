<?php

namespace App\Mail\Pmb;

use App\Models\CampusSetting;
use App\Models\PmbLocalApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PmbLocalApplication $application,
        public CampusSetting $campusSetting,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran PMB '.$this->campusSetting->campus_name.' Perlu Perbaikan',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.pmb.application-rejected',
        );
    }
}
