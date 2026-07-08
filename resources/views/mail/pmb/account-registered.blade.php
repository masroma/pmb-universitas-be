<x-mail::message>
# Akun PMB Berhasil Dibuat

Halo {{ $user->name }},

Akun pendaftaran PMB **{{ $campusSetting->campus_name }}** Anda sudah aktif.

Anda dapat melanjutkan pengisian formulir pendaftaran melalui portal mahasiswa.

<x-mail::button :url="config('app.frontend_url').'/login'">
Masuk ke Portal PMB
</x-mail::button>

Jika Anda tidak merasa mendaftar, abaikan email ini.

Salam,<br>
Tim PMB {{ $campusSetting->campus_name }}
</x-mail::message>
