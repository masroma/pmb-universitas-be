# Onboarding Kampus

Panduan ini dipakai untuk menyiapkan satu instalasi PMB AI untuk satu kampus/universitas dengan beberapa lokasi/cabang.

## 1. Identitas Kampus

1. Login sebagai `super_admin`.
2. Buka `Setting Kampus`.
3. Isi nama kampus, alamat utama, website, telepon, logo, hero image, dan media sosial.
4. Pastikan informasi ini sudah tampil di landing page.

## 2. Master Lokasi

1. Buka `Master PMB -> Lokasi Kampus`.
2. Tambahkan lokasi/cabang seperti kampus utama, kampus kota, atau kampus kerja sama.
3. Isi kode, nama, kota, provinsi, alamat, dan maps URL.
4. Tandai satu lokasi sebagai lokasi utama.
5. Buka `Master PMB -> Kontak Lokasi` untuk menambahkan WhatsApp, email, telepon, dan website per lokasi.

## 3. Akademik dan Program

1. Buka `Master PMB -> Fakultas`.
2. Tambahkan fakultas/sekolah.
3. Buka `Master PMB -> Program Studi`.
4. Tambahkan prodi, jenjang, gelar, akreditasi, dan deskripsi.
5. Buka `Master PMB -> Prodi per Lokasi`.
6. Hubungkan prodi dengan lokasi yang membuka prodi tersebut.

## 4. Kelas, Periode, Jalur, dan Opsi

1. Buka `Master PMB -> Kelas/Waktu Kuliah`.
2. Tambahkan kelas reguler, malam, Sabtu, hybrid, atau kelas lain.
3. Buka `Master PMB -> Periode PMB`.
4. Buat periode PMB aktif dan isi tahun ajar serta tanggal buka/tutup.
5. Buka `Master PMB -> Gelombang PMB`.
6. Tambahkan gelombang di dalam periode.
7. Buka `Master PMB -> Jalur Pendaftaran`.
8. Tambahkan jalur reguler, prestasi, beasiswa, RPL, pascasarjana, atau jalur lain.
9. Buka `Master PMB -> Opsi Pendaftaran`.
10. Kombinasikan periode, gelombang, lokasi-prodi, jalur, dan kelas yang benar-benar dibuka.

## 5. Biaya, Beasiswa, Konten, dan FAQ

1. Buka `Master PMB -> Biaya Kuliah`.
2. Isi biaya pendaftaran, jumlah angsuran, nominal angsuran, biaya semester, dan catatan biaya per opsi pendaftaran.
3. Buka `Master PMB -> Beasiswa`.
4. Tambahkan beasiswa dan syaratnya.
5. Buka `Master PMB -> Konten PMB`.
6. Isi konten kategori `keunggulan`, `syarat`, `alur-pendaftaran`, `kurikulum`, `kontak`, dan konten lain.
7. Buka `Master PMB -> FAQ`.
8. Tambahkan pertanyaan yang sering muncul dari calon mahasiswa.

## 6. Checklist Go-Live

- Landing page menampilkan identitas kampus yang benar.
- AI chat menjawab prodi, biaya, lokasi, kelas, dan syarat dari API.
- Portal pendaftaran bisa memilih periode, gelombang, lokasi, prodi, jalur, kelas, dan biaya.
- Calon mahasiswa bisa menyimpan draft, upload dokumen, dan submit.
- Admin bisa verifikasi pendaftaran.
- CRM AI bisa follow-up lead.
- Dashboard menampilkan funnel.
- `php artisan pmb:production-check` berhasil tanpa error.
