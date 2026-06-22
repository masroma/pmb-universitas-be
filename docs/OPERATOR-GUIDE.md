# Operator Guide

Panduan ini untuk Admin PMB dan Operator CRM.

## Role

- `super_admin`: mengelola semua menu, konfigurasi, dan master data.
- `admin_pmb`: mengelola master PMB, pendaftaran, konten, dan laporan.
- `operator_crm`: mengelola lead dari AI dan follow-up.

## Aktivitas Admin PMB

1. Cek dashboard setiap hari.
2. Pantau jumlah lead AI, pendaftar draft, submitted, verified, dan rejected.
3. Buka `Pendaftaran Lokal`.
4. Filter status `Menunggu Review`.
5. Buka detail pendaftar.
6. Cek biodata, pilihan PMB, biaya, dan dokumen.
7. Ubah status menjadi `Terverifikasi` atau `Ditolak/Revisi`.
8. Isi catatan review jika ada revisi.

## Aktivitas Operator CRM

1. Buka `CRM AI`.
2. Filter lead `Hot` atau `Minta Dihubungi`.
3. Hubungi calon mahasiswa via WhatsApp.
4. Update follow-up:
   - `Sudah Dihubungi`
   - `Tertarik`
   - `Sudah Daftar`
   - `Tidak Tertarik`
5. Isi catatan follow-up.

## Mengubah Master PMB

Urutan aman saat mengubah data:

1. Lokasi kampus.
2. Fakultas.
3. Program studi.
4. Prodi per lokasi.
5. Kelas/waktu kuliah.
6. Periode PMB.
7. Gelombang.
8. Jalur pendaftaran.
9. Opsi pendaftaran.
10. Biaya kuliah.
11. Konten PMB, beasiswa, dan FAQ.

Setelah mengubah master, cek:

- `/api/ai/opsi-pendaftaran`
- `/api/ai/biaya`
- Portal mahasiswa pilihan PMB.
- Chat AI untuk pertanyaan terkait data yang diubah.

## Checklist Demo End-to-End

1. Buka landing page.
2. Tanyakan ke AI: “Saya kerja, mau kuliah malam jurusan IT, ada rekomendasi?”
3. Pastikan AI memberi rekomendasi prodi, lokasi, kelas, jalur, dan biaya.
4. Buka portal mahasiswa.
5. Daftar akun calon mahasiswa.
6. Pilih periode, gelombang, lokasi, prodi, jalur, dan kelas.
7. Simpan draft.
8. Upload KTP, ijazah/surat lulus, dan pas foto.
9. Submit pendaftaran.
10. Login admin.
11. Buka pendaftaran lokal dan verifikasi.
12. Buka dashboard dan tunjukkan funnel.
13. Buka CRM AI dan update follow-up lead.

## Troubleshooting

- Jika pilihan prodi tidak muncul, cek `Prodi per Lokasi` dan `Opsi Pendaftaran`.
- Jika biaya tidak muncul, cek `Biaya Kuliah` untuk opsi pendaftaran tersebut.
- Jika AI tidak menjawab data terbaru, cek backend `/api/health`, AI `/health`, dan cache recommendation service.
- Jika dokumen gagal upload, cek storage permission dan `php artisan storage:link`.
