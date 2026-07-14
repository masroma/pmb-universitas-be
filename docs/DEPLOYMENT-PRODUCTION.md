# Deployment Production

Panduan ini menjelaskan deployment minimum untuk backend Laravel, frontend Nuxt, dan AI service FastAPI.

## Backend Laravel

1. Set environment:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://api-pmb.kucingganteng.my.id`
   - `FRONTEND_URL=https://pmb-universitas-fe.vercel.app`
   - `DB_*` sesuai database production
   - `AI_PMB_URL=https://ai-pmb.kucingganteng.my.id/chat`
   - `AI_INTERNAL_API_KEY=` (sama dengan `PMB_INTERNAL_API_KEY` di AI service)
2. Install dependency:
   - `composer install --no-dev --optimize-autoloader`
3. Jalankan setup:
   - `php artisan key:generate`
   - `php artisan migrate --force`
   - `php artisan db:seed --class=StandalonePmbSeeder --force` hanya untuk instalasi awal/demo.
   - `php artisan storage:link`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
4. Validasi:
   - `php artisan pmb:production-check`
   - akses `/api/health`

### Nginx + PHP (wajib untuk admin upload)

Jika admin muncul **413 Request Entity Too Large** saat simpan data/upload file, naikkan limit di nginx **dan** PHP.

Contoh site config ada di `deploy/nginx/pmb-universitas.conf`. Minimal tambahkan di blok `server` nginx:

```nginx
client_max_body_size 25M;
```

Lalu set PHP-FPM (`/etc/php/8.3/fpm/php.ini` atau pool site):

```ini
upload_max_filesize = 20M
post_max_size = 25M
```

Reload service:

```bash
sudo nginx -t
sudo systemctl reload nginx
sudo systemctl reload php8.3-fpm
```

Catatan:

- Error **413 dari nginx** berarti request diblokir sebelum sampai Laravel.
- File `public/.user.ini` ikut disertakan, tetapi di production tetap atur `php.ini`/PHP-FPM.
- Upload admin: logo max 2 MB, hero max 4 MB, brosur max 10 MB.

## Frontend Nuxt

Domain: `https://pmb-universitas-fe.vercel.app`

1. Set di Vercel (Production env):
   - `NUXT_PUBLIC_API_BASE=https://api-pmb.kucingganteng.my.id/api`
2. Install dependency:
   - `npm ci`
3. Build:
   - `npm run build`
4. Deploy ke Vercel, atau jalankan dengan process manager seperti PM2.

## AI Service FastAPI

Domain: `https://ai-pmb.kucingganteng.my.id`

1. Set environment:
   - `PMB_API_URL=https://api-pmb.kucingganteng.my.id/api`
   - `PMB_INTERNAL_API_KEY=` (sama dengan `AI_INTERNAL_API_KEY` di backend)
   - `OPENAI_API_KEY=...`
   - `OPENAI_MODEL=gpt-4o-mini` atau model yang dipakai.
   - `CORS_ORIGINS=https://pmb-universitas-fe.vercel.app`
2. Install dependency:
   - `pip install -r requirements.txt`
3. Jalankan:
   - `uvicorn main:app --host 0.0.0.0 --port 8001`
4. Validasi:
   - akses `/health`

## Backup

Backup harian minimum:

1. Database MySQL:
   - `mysqldump -u USER -p DATABASE > backup-pmb-$(date +%F).sql`
2. Storage upload:
   - backup folder `storage/app/public`
3. Simpan backup minimal 14-30 hari.
4. Uji restore berkala di server staging.

## Monitoring

Monitor endpoint berikut:

- Backend: `/api/health`
- AI service: `/health`
- Frontend: halaman utama dan portal mahasiswa.

Alert minimum:

- Backend health gagal.
- AI health gagal.
- Disk server hampir penuh.
- Database tidak bisa diakses.
- Error 5xx meningkat.

## Security Checklist

- `APP_DEBUG=false`.
- HTTPS aktif.
- Password admin diganti setelah instalasi.
- Role admin dicek: `super_admin`, `admin_pmb`, `operator_crm`.
- Folder `.env` tidak bisa diakses publik.
- Backup tidak disimpan di public web root.
- File upload hanya dokumen yang diizinkan.

## Email Notifikasi

Wajib dikonfigurasi sebelum go-live. Sistem mengirim email pada:

- Registrasi akun calon mahasiswa
- Submit pendaftaran
- Verifikasi pendaftaran oleh admin
- Penolakan/perbaikan pendaftaran oleh admin

Set environment:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.provider.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=pmb@domain-kampus
MAIL_FROM_NAME="PMB Kampus"
FRONTEND_URL=https://domain-frontend
```

Pembayaran biaya pendaftaran dilakukan **manual** (belum terintegrasi payment gateway). Instruksi pembayaran dikomunikasikan admin melalui email/WhatsApp.
