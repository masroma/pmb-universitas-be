# Deployment Production

Panduan ini menjelaskan deployment minimum untuk backend Laravel, frontend Nuxt, dan AI service FastAPI.

## Backend Laravel

1. Set environment:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://domain-kampus`
   - `DB_*` sesuai database production
   - `AI_PMB_URL=https://ai-domain/chat`
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

## Frontend Nuxt

1. Set `NUXT_PUBLIC_API_BASE=https://domain-backend/api`.
2. Install dependency:
   - `npm ci`
3. Build:
   - `npm run build`
4. Jalankan dengan process manager seperti PM2 atau layanan Node production.

## AI Service FastAPI

1. Set environment:
   - `PMB_API_URL=https://domain-backend/api`
   - `OPENAI_API_KEY=...`
   - `OPENAI_MODEL=gpt-4o-mini` atau model yang dipakai.
   - `CORS_ORIGINS=https://domain-frontend`
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
