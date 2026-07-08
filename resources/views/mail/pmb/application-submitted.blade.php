<x-mail::message>
# Pendaftaran Berhasil Dikirim

Halo {{ $application->name }},

Pendaftaran PMB **{{ $campusSetting->campus_name }}** Anda sudah kami terima dan sedang menunggu verifikasi admin.

**Ringkasan pendaftaran:**
- Program studi: {{ $application->study_program_name ?: '-' }}
- Kampus: {{ $application->campus_name ?: '-' }}
- Jalur: {{ $application->registration_path_name ?: '-' }}
- Kelas: {{ $application->study_system_name ?: '-' }}

**Pembayaran biaya pendaftaran** dilakukan secara manual. Tim PMB akan menghubungi Anda melalui email atau WhatsApp untuk instruksi pembayaran selanjutnya.

<x-mail::button :url="config('app.frontend_url').'/portal-mahasiswa'">
Lihat Status Pendaftaran
</x-mail::button>

Salam,<br>
Tim PMB {{ $campusSetting->campus_name }}
</x-mail::message>
