# Kamus Data (Data Dictionary) — SI-AMI Polbeng

**Dokumen:** Kamus Data Sistem Informasi Audit Mutu Internal  
**Institusi:** Politeknik Negeri Bengkalis  
**Versi:** 1.0  
**Tanggal:** 2026-05-12  

---

## 1. Pengguna (`users`)

Menyimpan data seluruh pengguna yang dapat masuk ke sistem.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik pengguna |
| `name` | String | Wajib | Nama lengkap pengguna |
| `email` | String | Wajib, Unik | Alamat email (digunakan sebagai username login) |
| `password` | String | Wajib | Kata sandi terenkripsi |
| `status` | String | Opsional | Status akun pengguna (aktif/nonaktif) |
| `last_login_at` | DateTime | Opsional | Waktu terakhir pengguna masuk ke sistem |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

**Peran yang tersedia:** Super Admin, Admin, Auditor, Auditee, Direktur

---

## 2. Unit (`units`)

Menyimpan data unit organisasi yang menjadi objek audit (Program Studi, Jurusan, dll.).

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik unit |
| `nama` | String | Opsional | Nama resmi unit (misal: "Program Studi Teknik Informatika") |
| `tipe` | String(20) | Opsional | Jenis unit (misal: "prodi", "jurusan", "lembaga") |
| `parent_id` | UUID | Opsional, FK | Referensi ke unit induk (untuk struktur hierarki) |
| `user_id` | UUID | Opsional, FK | Pengelola/penanggung jawab unit |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 3. Lembaga Akreditasi (`lembaga_akreditasis`)

Menyimpan data badan/lembaga yang memberikan akreditasi (misal: BAN-PT, LAMEMBA).

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik lembaga |
| `nama` | String | Opsional | Nama lengkap lembaga (misal: "Lembaga Akreditasi Mandiri Ekonomi Manajemen Bisnis dan Akuntansi") |
| `singkatan` | String(30) | Opsional | Singkatan nama lembaga (misal: "LAMEMBA") |
| `kategori` | String(20) | Opsional | Kategori lembaga akreditasi |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 4. Template Instrumen (`instrumen_templates`)

Menyimpan template instrumen audit yang berisi kumpulan kriteria dan indikator penilaian.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik template |
| `nama` | String | Opsional | Nama template instrumen |
| `deskripsi` | Text | Opsional | Penjelasan singkat template |
| `is_active` | Boolean | Default: true | Status aktif/nonaktif template |
| `lembaga_akreditasi_id` | UUID | Opsional, FK | Lembaga akreditasi yang template ini ikuti standarnya |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 5. Kriteria (`kriterias`)

Menyimpan kriteria penilaian dalam standar akreditasi (misal: Tata Pamong, Mahasiswa, dll.).

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik kriteria |
| `kode` | String | Opsional | Kode/nomor kriteria (misal: "K1", "C1") |
| `nama` | Text | Opsional | Nama/deskripsi kriteria |
| `parent_id` | UUID | Opsional | Kriteria induk (untuk sub-kriteria bertingkat) |
| `lembaga_akreditasi_id` | UUID | Opsional, FK | Lembaga akreditasi pemilik kriteria |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 6. Indikator (`indikators`)

Menyimpan indikator spesifik yang diukur dalam setiap kriteria.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik indikator |
| `nama` | Text | Opsional | Deskripsi lengkap indikator |
| `tipe` | String | Opsional | Tipe indikator (misal: kuantitatif, kualitatif, LKPS) |
| `formula_penilaian` | Text | Opsional | Rumus/formula untuk menghitung nilai indikator secara otomatis |
| `kriteria_id` | UUID | Opsional, FK | Kriteria yang memiliki indikator ini |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 7. Input Indikator (`indikator_inputs`)

Menyimpan variabel input yang harus diisi Auditee untuk menghitung nilai setiap indikator.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik input |
| `nama_variable` | String | Opsional | Nama variabel dalam formula (misal: `jumlah_mahasiswa`) |
| `label_input` | String | Opsional | Label yang ditampilkan ke Auditee di form isian |
| `tipe_data` | String(20) | Opsional | Tipe data input (misal: integer, decimal, date, string) |
| `indikator_id` | UUID | Opsional, FK | Indikator yang memiliki input ini |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 8. Rubrik Penilaian (`rubrik_penilaians`)

Menyimpan aturan/rubrik untuk menginterpretasikan nilai indikator menjadi predikat.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik rubrik |
| `deskripsi` | Text | Opsional | Deskripsi predikat rubrik (misal: "Sangat Baik", "Baik", "Cukup") |
| `formula_kondisi` | Text | Opsional | Ekspresi kondisi untuk menentukan predikat (misal: `nilai >= 3.5`) |
| `indikator_id` | UUID | Opsional, FK | Indikator yang menggunakan rubrik ini |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 9. Periode Audit (`audit_periodes`)

Menyimpan data satu siklus/putaran audit mutu internal untuk satu unit pada satu tahun akademik.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik periode audit |
| `tahun_akademik` | String(50) | Opsional | Tahun akademik periode audit (misal: "2024/2025") |
| `status` | String(20) | Opsional | Status periode (misal: aktif, selesai) |
| `unit_id` | UUID | Opsional, FK | Unit yang diaudit pada periode ini |
| `instrumen_template_id` | UUID | Opsional, FK | Template instrumen yang digunakan |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |
| `deleted_at` | DateTime | Opsional | Penanda penghapusan sementara (soft delete) |

---

## 10. Penugasan Auditor (`penugasan_auditors`)

Menyimpan penugasan Auditor ke Periode Audit tertentu.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik penugasan |
| `user_id` | UUID | Opsional, FK | Pengguna dengan peran Auditor yang ditugaskan |
| `audit_periode_id` | UUID | Opsional, FK | Periode audit yang menjadi tanggung jawab auditor |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 11. Hasil Audit (`hasil_audits`)

Menyimpan hasil pengisian Auditee untuk setiap indikator dalam satu periode audit.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik hasil audit |
| `catatan_final` | Text | Opsional | Catatan akhir dari Auditor setelah verifikasi |
| `status_terkini` | String | Opsional | Status terkini hasil (draft, submitted, verified, revision_requested) |
| `audit_periode_id` | UUID | Opsional, FK | Periode audit di mana hasil ini dicatat |
| `indikator_id` | UUID | Opsional, FK | Indikator yang dinilai |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

**Nilai `status_terkini`:**
- `draft` — Sedang diisi, belum disubmit
- `submitted` — Telah disubmit, menunggu verifikasi
- `verified` — Telah diverifikasi oleh Auditor
- `revision_requested` — Dikembalikan untuk direvisi

---

## 12. Data Input Audit (`data_audit_inputs`)

Menyimpan nilai aktual yang dimasukkan Auditee untuk setiap variabel input indikator.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik data input |
| `nilai_variable` | String | Opsional | Nilai yang diisi Auditee (disimpan sebagai teks) |
| `hasil_audit_id` | UUID | Opsional, FK | Hasil audit yang berisi data ini |
| `indikator_input_id` | UUID | Opsional, FK | Variabel input yang diisi |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 13. Log Aktivitas Audit (`log_aktivitas_audits`)

Menyimpan rekam jejak setiap aksi yang dilakukan pada hasil audit (submit, verifikasi, revisi, dll.).

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik log |
| `tipe_aksi` | String | Opsional | Jenis aksi (misal: submit, verify, request_revision) |
| `catatan_aksi` | Text | Opsional | Keterangan tambahan mengenai aksi yang dilakukan |
| `hasil_audit_id` | UUID | Opsional, FK | Hasil audit yang menjadi subjek aksi |
| `user_id` | UUID | Opsional, FK | Pengguna yang melakukan aksi |
| `created_at` | DateTime | Otomatis | Waktu aksi terjadi |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 14. Berita Acara Audit (`berita_acaras`)

Menyimpan berita acara resmi yang dibuat setelah proses audit satu periode selesai.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik berita acara |
| `catatan` | Text | Opsional | Isi/catatan berita acara audit |
| `audit_periode_id` | UUID | Opsional, FK | Periode audit yang berita acaranya dibuat |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |
| `deleted_at` | DateTime | Opsional | Penanda penghapusan sementara |

---

## 15. Berita/Pengumuman (`beritas`)

Menyimpan pengumuman atau berita yang dipublikasikan oleh Admin kepada seluruh pengguna.

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik berita |
| `judul` | String | Opsional | Judul berita/pengumuman |
| `isi` | Text | Opsional | Konten lengkap berita |
| `tanggal` | DateTime | Opsional | Tanggal publikasi |
| `view` | Integer | Opsional | Jumlah kunjungan/tayangan |
| `slug` | String | Unik | URL-friendly identifier berita |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

---

## 16. Berkas/File (`files`)

Menyimpan metadata berkas yang dilampirkan ke berbagai entitas dalam sistem (polymorphic).

| Nama Field | Tipe Data | Constraint | Keterangan |
|------------|-----------|-----------|------------|
| `id` | UUID | Primary Key | Identitas unik berkas |
| `fileable_type` | String | Wajib | Tipe entitas pemilik berkas (misal: "App\Models\HasilAudit") |
| `fileable_id` | UUID | Wajib | ID entitas pemilik berkas |
| `alias` | String | Opsional | Nama atau label berkas yang ditampilkan ke pengguna |
| `created_at` | DateTime | Otomatis | Waktu data dibuat |
| `updated_at` | DateTime | Otomatis | Waktu data terakhir diubah |

**Catatan:** Berkas dapat dilampirkan ke berbagai entitas: HasilAudit, BeritaAcara, dll.

---

*Kamus data ini dihasilkan dari analisis migration database dan model Eloquent SI-AMI Polbeng v1.*  
*Seluruh tabel menggunakan UUID sebagai Primary Key dan mendukung timestamps otomatis.*
