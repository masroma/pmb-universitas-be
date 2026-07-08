<x-mail::message>
# Pendaftaran Perlu Perbaikan

Halo {{ $application->name }},

Pendaftaran PMB **{{ $campusSetting->campus_name }}** Anda belum dapat kami setujui dan perlu diperbaiki.

@if ($application->review_note)
**Catatan admin:** {{ $application->review_note }}
@endif

Silakan masuk ke portal mahasiswa, perbaiki data atau dokumen yang diminta, lalu kirim ulang pendaftaran Anda.

<x-mail::button :url="config('app.frontend_url').'/portal-mahasiswa'">
Perbaiki Pendaftaran
</x-mail::button>

Salam,<br>
Tim PMB {{ $campusSetting->campus_name }}
</x-mail::message>
