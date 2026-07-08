<x-mail::message>
# Pendaftaran Terverifikasi

Halo {{ $application->name }},

Selamat! Pendaftaran PMB **{{ $campusSetting->campus_name }}** Anda telah **diverifikasi** oleh tim admin.

**Program studi:** {{ $application->study_program_name ?: '-' }}

@if ($application->review_note)
**Catatan admin:** {{ $application->review_note }}
@endif

Tim PMB akan menghubungi Anda untuk langkah pembayaran dan administrasi berikutnya.

<x-mail::button :url="config('app.frontend_url').'/portal-mahasiswa'">
Buka Portal PMB
</x-mail::button>

Salam,<br>
Tim PMB {{ $campusSetting->campus_name }}
</x-mail::message>
