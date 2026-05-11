# Narasi Use Case — SI-AMI Polbeng

**Dokumen:** Narasi Use Case Sistem Informasi Audit Mutu Internal  
**Institusi:** Politeknik Negeri Bengkalis  
**Versi:** 1.0  
**Tanggal:** 2026-05-12  

---

## Daftar Aktor

| Aktor | Deskripsi |
|-------|-----------|
| **Super Admin** | Pengelola sistem penuh: pengguna, hak akses, pengaturan, dan backup |
| **Admin** | Pengelola proses audit: unit, template, kriteria, periode, penugasan, laporan |
| **Auditor** | Pemeriksa hasil audit: verifikasi, permintaan revisi |
| **Auditee** | Pengisi hasil audit: isian, simpan draf, submit, revisi |
| **Direktur** | Pemangku kepentingan: melihat dashboard dan laporan eksekutif |

---

## UC-01: Masuk Sistem (Login)

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Masuk Sistem |
| **Kode** | UC-01 |
| **Aktor** | Semua Pengguna (Super Admin, Admin, Auditor, Auditee, Direktur) |
| **Precondition** | Pengguna memiliki akun yang terdaftar di sistem |
| **Alur Utama** | 1. Pengguna membuka halaman login sistem<br>2. Pengguna memasukkan email dan password<br>3. Sistem memvalidasi kredensial<br>4. Sistem mengarahkan pengguna ke dashboard sesuai peran |
| **Alur Alternatif** | A1. Jika email atau password salah → sistem menampilkan pesan kesalahan dan meminta pengisian ulang |
| **Postcondition** | Pengguna berhasil masuk dan dapat mengakses menu sesuai haknya |

---

## UC-02: Keluar Sistem (Logout)

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Keluar Sistem |
| **Kode** | UC-02 |
| **Aktor** | Semua Pengguna |
| **Precondition** | Pengguna telah masuk ke sistem |
| **Alur Utama** | 1. Pengguna menekan tombol "Keluar"<br>2. Sistem menghapus sesi pengguna<br>3. Sistem mengarahkan ke halaman login |
| **Alur Alternatif** | — |
| **Postcondition** | Sesi pengguna berakhir; halaman login ditampilkan |

---

## UC-03: Kelola Pengguna

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Kelola Pengguna |
| **Kode** | UC-03 |
| **Aktor** | Super Admin |
| **Precondition** | Super Admin telah masuk ke sistem |
| **Alur Utama** | 1. Super Admin membuka menu Pengguna<br>2. Sistem menampilkan daftar pengguna<br>3. Super Admin dapat menambah pengguna baru (isi nama, email, password, peran)<br>4. Super Admin dapat mengubah data pengguna yang ada<br>5. Super Admin dapat menonaktifkan atau menghapus pengguna<br>6. Sistem menyimpan perubahan |
| **Alur Alternatif** | A1. Jika email sudah terdaftar → sistem menolak dan menampilkan pesan galat |
| **Postcondition** | Data pengguna tersimpan; pengguna baru dapat masuk ke sistem |

---

## UC-08: Kelola Unit

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Kelola Unit |
| **Kode** | UC-08 |
| **Aktor** | Admin |
| **Precondition** | Admin telah masuk ke sistem |
| **Alur Utama** | 1. Admin membuka menu Unit<br>2. Sistem menampilkan daftar unit (Program Studi, Jurusan, dll.)<br>3. Admin dapat menambah unit baru (isi nama, tipe, unit induk, pengelola)<br>4. Admin dapat mengubah atau menghapus unit<br>5. Sistem menyimpan perubahan |
| **Alur Alternatif** | A1. Unit yang sedang digunakan di periode audit aktif tidak dapat dihapus |
| **Postcondition** | Data unit tersimpan dan dapat dipilih saat membuat periode audit |

---

## UC-09: Kelola Lembaga Akreditasi

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Kelola Lembaga Akreditasi |
| **Kode** | UC-09 |
| **Aktor** | Admin |
| **Precondition** | Admin telah masuk ke sistem |
| **Alur Utama** | 1. Admin membuka menu Lembaga Akreditasi<br>2. Sistem menampilkan daftar lembaga (misal: BAN-PT, LAMEMBA)<br>3. Admin menambah atau mengubah data lembaga (nama, singkatan, kategori)<br>4. Sistem menyimpan perubahan |
| **Alur Alternatif** | — |
| **Postcondition** | Lembaga akreditasi tersedia untuk digunakan dalam template instrumen |

---

## UC-10: Kelola Template Instrumen

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Kelola Template Instrumen |
| **Kode** | UC-10 |
| **Aktor** | Admin |
| **Precondition** | Lembaga akreditasi telah tersedia di sistem |
| **Alur Utama** | 1. Admin membuka menu Template Instrumen<br>2. Admin membuat template baru (nama, deskripsi, pilih lembaga akreditasi)<br>3. Admin menambahkan kriteria dan indikator ke template (dengan bobot)<br>4. Admin mengaktifkan template<br>5. Sistem menyimpan template |
| **Alur Alternatif** | A1. Template yang sudah digunakan di periode audit aktif tidak dapat diubah strukturnya |
| **Postcondition** | Template tersedia untuk dipilih saat membuat periode audit |

---

## UC-11: Kelola Kriteria & Indikator

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Kelola Kriteria & Indikator |
| **Kode** | UC-11 |
| **Aktor** | Admin |
| **Precondition** | Lembaga akreditasi telah tersedia |
| **Alur Utama** | 1. Admin membuka menu Kriteria<br>2. Admin menambahkan kriteria (kode, nama, lembaga, kriteria induk jika ada)<br>3. Admin membuka menu Indikator dan menambahkan indikator per kriteria<br>4. Admin menetapkan tipe indikator dan formula penilaian<br>5. Admin menambahkan input variabel untuk setiap indikator<br>6. Sistem menyimpan data |
| **Alur Alternatif** | — |
| **Postcondition** | Kriteria dan indikator tersedia untuk disertakan dalam template instrumen |

---

## UC-13: Buat Periode Audit

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Buat Periode Audit |
| **Kode** | UC-13 |
| **Aktor** | Admin |
| **Precondition** | Unit dan template instrumen aktif telah tersedia |
| **Alur Utama** | 1. Admin membuka menu Periode Audit<br>2. Admin membuat periode baru (tahun akademik, pilih unit, pilih template)<br>3. Sistem membuat record periode audit dengan status awal<br>4. Admin dapat melihat daftar periode aktif |
| **Alur Alternatif** | A1. Satu unit hanya dapat memiliki satu periode aktif per tahun akademik |
| **Postcondition** | Periode audit tersedia; Auditee dapat mulai mengisi hasil audit |

---

## UC-14: Tugaskan Auditor

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Tugaskan Auditor |
| **Kode** | UC-14 |
| **Aktor** | Admin |
| **Precondition** | Periode audit telah dibuat; pengguna dengan peran Auditor tersedia |
| **Alur Utama** | 1. Admin membuka menu Penugasan Audit<br>2. Admin memilih periode audit<br>3. Admin memilih Auditor dari daftar pengguna berperan Auditor<br>4. Sistem menyimpan penugasan<br>5. Auditor menerima akses ke periode audit tersebut |
| **Alur Alternatif** | A1. Satu auditor dapat ditugaskan ke beberapa periode |
| **Postcondition** | Auditor terhubung ke periode audit dan dapat mulai memverifikasi |

---

## UC-22: Isi Hasil Audit (Draft)

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Isi Hasil Audit |
| **Kode** | UC-22 |
| **Aktor** | Auditee |
| **Precondition** | Periode audit aktif tersedia untuk unit Auditee; Auditee telah masuk |
| **Alur Utama** | 1. Auditee membuka menu Hasil Audit<br>2. Sistem menampilkan daftar indikator yang harus diisi<br>3. Auditee mengisi nilai/data untuk setiap input variabel indikator<br>4. Sistem secara otomatis menghitung skor berdasarkan formula<br>5. Auditee menyimpan sebagai draf |
| **Alur Alternatif** | A1. Jika formula tidak dapat dievaluasi → sistem menampilkan pesan kesalahan pada indikator terkait |
| **Postcondition** | Hasil audit tersimpan dengan status "Draft"; dapat diedit kembali |

---

## UC-23: Submit Hasil Audit

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Submit Hasil Audit |
| **Kode** | UC-23 |
| **Aktor** | Auditee |
| **Precondition** | Hasil audit telah diisi dan disimpan sebagai draf |
| **Alur Utama** | 1. Auditee membuka hasil audit yang telah diisi<br>2. Auditee menekan tombol "Submit"<br>3. Sistem mengubah status hasil audit menjadi "Submitted"<br>4. Sistem mencatat aksi di log aktivitas audit<br>5. Auditor yang bertugas dapat melihat hasil audit yang disubmit |
| **Alur Alternatif** | A1. Jika ada indikator yang belum terisi → sistem memperingatkan sebelum submit |
| **Postcondition** | Hasil audit tidak dapat diubah Auditee; menunggu verifikasi Auditor |

---

## UC-18: Verifikasi Hasil Audit

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Verifikasi Hasil Audit |
| **Kode** | UC-18 |
| **Aktor** | Auditor |
| **Precondition** | Hasil audit telah disubmit oleh Auditee; Auditor ditugaskan di periode tersebut |
| **Alur Utama** | 1. Auditor membuka menu Verifikasi<br>2. Sistem menampilkan daftar hasil audit yang menunggu verifikasi<br>3. Auditor memeriksa setiap indikator dan nilainya<br>4. Auditor menekan "Verifikasi" dan dapat menambahkan catatan final<br>5. Sistem mengubah status menjadi "Verified"<br>6. Sistem mencatat aksi di log aktivitas |
| **Alur Alternatif** | — |
| **Postcondition** | Hasil audit terverifikasi; dapat masuk ke proses pelaporan |

---

## UC-19: Minta Revisi Hasil Audit

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Minta Revisi |
| **Kode** | UC-19 |
| **Aktor** | Auditor |
| **Precondition** | Hasil audit telah disubmit; Auditor menemukan ketidaksesuaian |
| **Alur Utama** | 1. Auditor membuka hasil audit yang akan direvisi<br>2. Auditor menekan "Minta Revisi" dan mengisi catatan revisi<br>3. Sistem mengubah status menjadi "Revision Requested"<br>4. Auditee dapat melihat catatan revisi dan mengisi ulang |
| **Alur Alternatif** | — |
| **Postcondition** | Hasil audit dikembalikan ke Auditee untuk diperbaiki |

---

## UC-24: Revisi Hasil Audit

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Revisi Hasil Audit |
| **Kode** | UC-24 |
| **Aktor** | Auditee |
| **Precondition** | Hasil audit berstatus "Revision Requested" |
| **Alur Utama** | 1. Auditee membuka notifikasi atau menu Hasil Audit<br>2. Sistem menampilkan catatan revisi dari Auditor<br>3. Auditee mengubah nilai/data yang diminta<br>4. Auditee menyimpan dan meng-submit kembali |
| **Alur Alternatif** | — |
| **Postcondition** | Hasil audit kembali berstatus "Submitted"; Auditor dapat memeriksa ulang |

---

## UC-27: Lihat Dashboard Eksekutif

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Lihat Dashboard Eksekutif |
| **Kode** | UC-27 |
| **Aktor** | Direktur |
| **Precondition** | Direktur telah masuk; terdapat data audit yang sudah terverifikasi |
| **Alur Utama** | 1. Direktur membuka halaman utama<br>2. Sistem menampilkan ringkasan: jumlah unit selesai audit, rata-rata skor, temuan terbanyak<br>3. Direktur dapat memilih periode audit tertentu untuk dianalisis |
| **Alur Alternatif** | A1. Jika belum ada data audit → sistem menampilkan dashboard kosong dengan pesan informatif |
| **Postcondition** | Direktur mendapatkan gambaran keseluruhan kinerja audit mutu |

---

## UC-30: Unduh Laporan

| Elemen | Keterangan |
|--------|-----------|
| **Nama Use Case** | Unduh Laporan |
| **Kode** | UC-30 |
| **Aktor** | Direktur, Admin, Super Admin |
| **Precondition** | Terdapat data audit yang telah terverifikasi |
| **Alur Utama** | 1. Pengguna membuka menu Laporan<br>2. Sistem menampilkan pilihan jenis laporan (ringkasan temuan, ranking unit, dll.)<br>3. Pengguna memilih periode dan format unduhan (Word/PDF)<br>4. Sistem menghasilkan dan mengunduh file laporan |
| **Alur Alternatif** | A1. Jika tidak ada data untuk periode yang dipilih → sistem memberikan notifikasi |
| **Postcondition** | File laporan berhasil diunduh ke perangkat pengguna |

---

*Dokumen ini mencakup 15 use case utama dari total 31 use case dalam sistem SI-AMI Polbeng.*  
*Sumber: analisis kode v1 — controllers, models, dan routes.*
