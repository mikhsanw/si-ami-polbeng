# Phase 0: System Design Documentation — Specification

**Created:** 2026-05-12  
**Ambiguity score:** 0.14 (gate: ≤ 0.20) ✓  
**Requirements:** 6 locked  

## Goal

Menghasilkan satu dokumen Word (`.docx`) yang berisi use case diagram, narasi use case, ERD, data dictionary, dan flowchart lifecycle AMI — ditulis dalam Bahasa Indonesia, ditujukan untuk dosen sebagai pembaca non-teknis, mencerminkan perilaku sistem SI-AMI Polbeng v1 yang sesungguhnya.

## Background

Sistem SI-AMI Polbeng v1 telah selesai dibangun (114 commit, Jul 2025 – Apr 2026) dan berjalan di produksi. Namun **tidak ada satupun dokumen desain** yang pernah dibuat — tidak ada use case, ERD, flowchart, maupun narasi bisnis. Tim membutuhkan dokumentasi internal yang dapat dibagikan kepada dosen (non-teknis) untuk keperluan review dan onboarding.

Domain model sudah terpetakan dari kode:
- **5 aktor:** Super Admin, Admin, Auditor, Auditee, Direktur
- **10+ entitas domain:** `AuditPeriode`, `HasilAudit`, `Kriteria`, `Indikator`, `InstrumenTemplate`, `Unit`, `User`, `File`, `BeritaAcara`, `LembagaAkreditasi`
- **Alur utama AMI:** setup instrument → buat periode → tugaskan auditor → isi hasil audit → verifikasi → pelaporan

## Requirements

1. **Use Case Diagram**: Diagram yang menampilkan semua aktor dan use case mereka.
   - Current: Tidak ada use case diagram di repository maupun folder manapun
   - Target: Diagram yang menampilkan semua 5 aktor dengan asosiasi ke minimum 20 use case, dibuat dalam format Mermaid (dapat dirender ke PNG untuk disisipkan ke Word)
   - Acceptance: Semua 5 aktor muncul di diagram; setiap aktor memiliki minimal 2 use case terhubung; diagram dapat dirender tanpa error

2. **Use Case Narratives**: Narasi terstruktur dalam format tabel untuk setiap use case utama.
   - Current: Tidak ada narasi use case dalam bentuk apapun
   - Target: Tabel per use case dengan kolom: Nama UC, Aktor, Precondition, Main Flow (langkah bernomor), Alternative Flow, Postcondition — minimum 15 use case dicakup
   - Acceptance: Setiap tabel memiliki semua kolom terisi; alur yang didokumentasikan sesuai dengan perilaku aktual controller di codebase

3. **ERD (Entity Relationship Diagram)**: Diagram relasi entitas domain.
   - Current: Skema hanya ada di migration files, tidak ada diagram ERD
   - Target: ERD yang menampilkan semua entitas domain utama beserta kardinalitas relasi (1:1, 1:N, N:M)
   - Acceptance: Semua entitas dari migration yang relevan muncul di ERD; semua foreign key relationship ditampilkan; kardinalitas diberi label

4. **Data Dictionary**: Definisi semua entitas dan field kunci.
   - Current: Tidak ada data dictionary; deskripsi field hanya bisa dibaca dari migration code
   - Target: Tabel per entitas berisi: nama field, tipe data, constraint (nullable/required/unique), dan deskripsi singkat dalam Bahasa Indonesia
   - Acceptance: Setiap entitas di ERD memiliki entri data dictionary yang sesuai; deskripsi ditulis dalam Bahasa Indonesia yang dapat dipahami non-teknis

5. **System Flowchart (AMI Lifecycle)**: Flowchart end-to-end proses audit mutu internal.
   - Current: Tidak ada flowchart; alur hanya dapat ditelusuri dari controller code
   - Target: Flowchart dari "Periode Dibuat" hingga "Laporan Dihasilkan", mencakup semua decision point dan transisi status (draft → submitted → verified → revised)
   - Acceptance: Semua transisi status `HasilAudit` terwakili di flowchart; setiap role yang terlibat di tiap langkah ditampilkan; flowchart dapat dibaca tanpa penjelasan kode

6. **Word Document Output**: Semua artefak dikompilasi dalam satu file `.docx`.
   - Current: Tidak ada file dokumentasi desain sistem
   - Target: File `SI-AMI-Design-Docs.docx` di `.planning/docs/design/` dengan halaman judul, daftar isi, dan 5 bagian (Use Case Diagram, Narasi Use Case, ERD, Data Dictionary, Flowchart)
   - Acceptance: File dapat dibuka di Microsoft Word / LibreOffice tanpa error; semua 5 bagian hadir; bahasa seluruh dokumen adalah Bahasa Indonesia

## Boundaries

**In scope:**
- Use case diagram (Mermaid → PNG → Word)
- Use case narratives — tabel format, minimum 15 use case utama, Bahasa Indonesia
- ERD — semua entitas domain v1 (`AuditPeriode`, `HasilAudit`, `Kriteria`, `Indikator`, `InstrumenTemplate`, `Unit`, `User`, `File`, `BeritaAcara`, `LembagaAkreditasi`, dan relasi pivot)
- Data dictionary — semua entitas di ERD dengan deskripsi Bahasa Indonesia
- System flowchart — lifecycle AMI end-to-end (periode → penugasan → pengisian → verifikasi → laporan)
- Satu file Word (`.docx`) sebagai output final di `.planning/docs/design/`

**Out of scope:**
- Sequence diagram — terlalu teknis untuk audiens dosen; ditunda ke fase dokumentasi lanjutan
- Role–Permission Matrix — lebih relevan untuk developer; ditunda ke fase terpisah
- API documentation — tidak relevan untuk dosen
- Deployment diagram — infrastruktur tidak diketahui; ditunda
- Wireframe / mockup UI — sudah ada UI live; tidak perlu dokumentasi ulang
- Dokumentasi kode (PHPDoc, JSDoc) — ini domain development, bukan dokumentasi sistem

## Constraints

- **Bahasa:** Seluruh konten dokumen Word dalam Bahasa Indonesia; nama entitas/field boleh tetap Inggris (karena merupakan nama teknis di database)
- **Audiens:** Non-teknis (dosen) — hindari istilah pemrograman; gunakan istilah domain bisnis
- **Akurasi:** Semua diagram dan narasi harus diverifikasi terhadap kode yang ada (controller, model, migration) — tidak boleh berdasarkan asumsi
- **Format diagram:** Mermaid sebagai sumber (versionable, dapat dirender); output final disisipkan ke Word sebagai gambar
- **Tool output:** Gunakan `phpoffice/phpword` (sudah tersedia di project) atau generate Markdown yang dapat dikonversi ke Word
- **Penyimpanan:** Semua sumber diagram disimpan di `.planning/docs/design/diagrams/`; dokumen final di `.planning/docs/design/`

## Acceptance Criteria

- [ ] Use case diagram merender tanpa error dan menampilkan semua 5 aktor (Super Admin, Admin, Auditor, Auditee, Direktur)
- [ ] Setiap aktor memiliki minimal 2 use case terhubung di diagram
- [ ] Minimum 15 narasi use case dalam format tabel (kolom: Nama UC, Aktor, Precondition, Main Flow, Alternative Flow, Postcondition)
- [ ] ERD menampilkan semua 10+ entitas domain dengan kardinalitas relasi berlabel
- [ ] Data dictionary berisi entri untuk setiap entitas di ERD, dengan deskripsi Bahasa Indonesia
- [ ] Flowchart mencakup semua transisi status HasilAudit (draft → submitted → verified → revised)
- [ ] File `SI-AMI-Design-Docs.docx` tersimpan di `.planning/docs/design/` dan dapat dibuka tanpa error
- [ ] Seluruh isi dokumen Word dalam Bahasa Indonesia
- [ ] Source diagram Mermaid tersimpan di `.planning/docs/design/diagrams/`

## Ambiguity Report

| Dimension           | Score | Min   | Status | Notes                                             |
|---------------------|-------|-------|--------|---------------------------------------------------|
| Goal Clarity        | 0.90  | 0.75  | ✓      | Audiens dosen, format Word, Bahasa Indonesia      |
| Boundary Clarity    | 0.90  | 0.70  | ✓      | Sequence diagram & permission matrix di-exclude   |
| Constraint Clarity  | 0.80  | 0.65  | ✓      | Mermaid → PNG → Word, phpword tersedia            |
| Acceptance Criteria | 0.80  | 0.70  | ✓      | 9 kriteria pass/fail                              |
| **Ambiguity**       | 0.14  | ≤0.20 | ✓      |                                                   |

## Interview Log

| Round | Perspective      | Pertanyaan                                 | Keputusan dikunci                                              |
|-------|------------------|--------------------------------------------|----------------------------------------------------------------|
| 1     | Researcher       | Siapa pembaca dokumen ini?                 | Dosen (non-teknis)                                             |
| 1     | Researcher       | Artefak mana yang paling prioritas?        | Use case diagram                                               |
| 1     | Researcher       | Format output apa yang dibutuhkan?         | Word (.docx)                                                   |
| 2     | Researcher       | Semua aktor atau subset?                   | [auto] Semua 5 aktor — gambaran sistem lengkap                 |
| 2     | Simplifier       | Diagram saja atau juga narasi tabel?       | [auto] Keduanya — standar dokumentasi akademik Indonesia       |
| 3     | Boundary Keeper  | Apa yang TIDAK masuk scope fase ini?       | [auto] Sequence diagram, permission matrix, API docs di-exclude|
| 3     | Boundary Keeper  | Apa definisi "selesai"?                    | [auto] 1 file .docx dengan 5 bagian, tersimpan di planning/docs|

---

*Phase: 00-system-design-docs*  
*Spec created: 2026-05-12*  
*Next step: /gsd-discuss-phase 0 — keputusan implementasi (cara membuat diagram, struktur Word, urutan pengerjaan)*
