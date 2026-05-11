<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;

class GenerateDesignDocs extends Command
{
    protected $signature = 'docs:generate-design';
    protected $description = 'Generate SI-AMI Design Documentation Word file (.docx)';

    public function handle()
    {
        $this->info('Membuat dokumen desain SI-AMI Polbeng...');

        $phpWord = new PhpWord();
        $phpWord->getSettings()->setUpdateFields(true);

        // === STYLES ===
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 18, 'color' => '1e3a5f'], ['spaceAfter' => 240]);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14, 'color' => '1e3a5f'], ['spaceBefore' => 240, 'spaceAfter' => 120]);
        $phpWord->addTitleStyle(3, ['bold' => true, 'size' => 12, 'color' => '2563eb'], ['spaceBefore' => 120, 'spaceAfter' => 80]);

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => 'aaaaaa',
            'cellMargin' => 80,
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
            'width' => 100 * 50,
        ];
        $headerCellStyle = ['bgColor' => '1e3a5f'];
        $headerFontStyle = ['bold' => true, 'color' => 'ffffff', 'size' => 10];
        $bodyFontStyle   = ['size' => 10];
        $bodyFontBold    = ['bold' => true, 'size' => 10];

        // =============================================
        // HALAMAN JUDUL
        // =============================================
        $section = $phpWord->addSection();
        $section->addText('', [], ['spaceAfter' => 1440]);

        $section->addText(
            'DOKUMEN DESAIN SISTEM',
            ['bold' => true, 'size' => 24, 'color' => '1e3a5f'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );
        $section->addText(
            'SI-AMI Polbeng',
            ['bold' => true, 'size' => 20, 'color' => '2563eb'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );
        $section->addText(
            'Sistem Informasi Audit Mutu Internal',
            ['italic' => true, 'size' => 14],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );
        $section->addText(
            'Politeknik Negeri Bengkalis',
            ['size' => 12],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 60]
        );
        $section->addText(
            'Versi 1.0 — ' . date('d F Y'),
            ['size' => 11, 'color' => '666666'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 60]
        );
        $section->addPageBreak();

        // =============================================
        // DAFTAR ISI
        // =============================================
        $section->addTitle('Daftar Isi', 1);
        $section->addText('1. Diagram Use Case', $bodyFontStyle, ['spaceAfter' => 60]);
        $section->addText('2. Narasi Use Case', $bodyFontStyle, ['spaceAfter' => 60]);
        $section->addText('3. Entity Relationship Diagram (ERD)', $bodyFontStyle, ['spaceAfter' => 60]);
        $section->addText('4. Kamus Data', $bodyFontStyle, ['spaceAfter' => 60]);
        $section->addText('5. Flowchart Alur Sistem AMI', $bodyFontStyle, ['spaceAfter' => 60]);
        $section->addPageBreak();

        // =============================================
        // BAB 1: USE CASE
        // =============================================
        $section->addTitle('1. Diagram Use Case', 1);
        $section->addText(
            'Diagram use case berikut menggambarkan interaksi antara 5 aktor sistem dengan seluruh use case yang tersedia di SI-AMI Polbeng.',
            $bodyFontStyle, ['spaceAfter' => 120]
        );

        $section->addTitle('Daftar Aktor', 2);
        $phpWord->addTableStyle('actorTable', $tableStyle);
        $tbl = $section->addTable('actorTable');

        $tbl->addRow();
        $tbl->addCell(2000, $headerCellStyle)->addText('Aktor', $headerFontStyle);
        $tbl->addCell(7000, $headerCellStyle)->addText('Deskripsi', $headerFontStyle);

        $actors = [
            ['Super Admin', 'Pengelola sistem penuh: pengguna, hak akses, pengaturan, dan backup'],
            ['Admin', 'Pengelola proses audit: unit, template, kriteria, periode, penugasan, laporan'],
            ['Auditor', 'Pemeriksa hasil audit: verifikasi dan permintaan revisi'],
            ['Auditee', 'Pengisi hasil audit: isian, simpan draf, submit, revisi'],
            ['Direktur', 'Pemangku kepentingan: melihat dashboard dan laporan eksekutif'],
        ];
        foreach ($actors as $a) {
            $tbl->addRow();
            $tbl->addCell(2000)->addText($a[0], $bodyFontBold);
            $tbl->addCell(7000)->addText($a[1], $bodyFontStyle);
        }

        $section->addText('', [], ['spaceAfter' => 120]);
        $section->addTitle('Daftar Use Case', 2);

        $phpWord->addTableStyle('ucTable', $tableStyle);
        $ucTbl = $section->addTable('ucTable');
        $ucTbl->addRow();
        $ucTbl->addCell(1200, $headerCellStyle)->addText('Kode', $headerFontStyle);
        $ucTbl->addCell(3800, $headerCellStyle)->addText('Nama Use Case', $headerFontStyle);
        $ucTbl->addCell(4000, $headerCellStyle)->addText('Aktor', $headerFontStyle);

        $usecases = [
            ['UC-01','Masuk Sistem (Login)','Semua Pengguna'],
            ['UC-02','Keluar Sistem (Logout)','Semua Pengguna'],
            ['UC-03','Kelola Pengguna','Super Admin'],
            ['UC-04','Kelola Menu & Hak Akses','Super Admin'],
            ['UC-05','Kelola Pengaturan Sistem','Super Admin'],
            ['UC-06','Buat Backup Database','Super Admin'],
            ['UC-07','Buat Backup File','Super Admin'],
            ['UC-08','Kelola Unit','Admin'],
            ['UC-09','Kelola Lembaga Akreditasi','Admin'],
            ['UC-10','Kelola Template Instrumen','Admin'],
            ['UC-11','Kelola Kriteria & Indikator','Admin'],
            ['UC-12','Kelola Rubrik Penilaian','Admin'],
            ['UC-13','Buat Periode Audit','Admin'],
            ['UC-14','Tugaskan Auditor','Admin'],
            ['UC-15','Kelola Berita/Pengumuman','Admin'],
            ['UC-16','Lihat Laporan Admin','Admin, Super Admin'],
            ['UC-17','Lihat Penugasan Audit','Auditor'],
            ['UC-18','Verifikasi Hasil Audit','Auditor'],
            ['UC-19','Minta Revisi Hasil Audit','Auditor'],
            ['UC-20','Lihat Progress Audit','Auditor'],
            ['UC-21','Lihat Periode Audit Aktif','Auditee'],
            ['UC-22','Isi Hasil Audit (Draft)','Auditee'],
            ['UC-23','Submit Hasil Audit','Auditee'],
            ['UC-24','Revisi Hasil Audit','Auditee'],
            ['UC-25','Unggah Dokumen Pendukung','Auditee'],
            ['UC-26','Lihat Status Audit','Auditee'],
            ['UC-27','Lihat Dashboard Eksekutif','Direktur'],
            ['UC-28','Lihat Ranking Unit','Direktur'],
            ['UC-29','Lihat Temuan Audit','Direktur'],
            ['UC-30','Unduh Laporan','Direktur, Admin, Super Admin'],
            ['UC-31','Buat Berita Acara Audit','Admin, Auditor'],
        ];
        foreach ($usecases as $uc) {
            $ucTbl->addRow();
            $ucTbl->addCell(1200)->addText($uc[0], $bodyFontBold);
            $ucTbl->addCell(3800)->addText($uc[1], $bodyFontStyle);
            $ucTbl->addCell(4000)->addText($uc[2], $bodyFontStyle);
        }

        $section->addText('', [], ['spaceAfter' => 60]);
        $section->addText(
            'Catatan: Source diagram Use Case (format Mermaid) tersedia di .planning/docs/design/diagrams/usecase.mmd',
            ['italic' => true, 'size' => 9, 'color' => '888888'],
            ['spaceAfter' => 120]
        );
        $section->addPageBreak();

        // =============================================
        // BAB 2: NARASI USE CASE (ringkasan)
        // =============================================
        $section->addTitle('2. Narasi Use Case', 1);
        $section->addText(
            'Berikut adalah narasi use case untuk alur-alur utama sistem. Narasi lengkap tersedia di .planning/docs/design/USE-CASE-NARRATIVES.md',
            $bodyFontStyle, ['spaceAfter' => 120]
        );

        $narratives = [
            [
                'kode'  => 'UC-13',
                'nama'  => 'Buat Periode Audit',
                'aktor' => 'Admin',
                'pre'   => 'Unit dan template instrumen aktif telah tersedia di sistem',
                'main'  => "1. Admin membuka menu Periode Audit\n2. Admin membuat periode baru (tahun akademik, pilih unit, pilih template)\n3. Sistem membuat record periode audit\n4. Admin dapat melihat daftar periode aktif",
                'alt'   => 'Satu unit hanya dapat memiliki satu periode aktif per tahun akademik',
                'post'  => 'Periode audit tersedia; Auditee dapat mulai mengisi hasil audit',
            ],
            [
                'kode'  => 'UC-22',
                'nama'  => 'Isi Hasil Audit',
                'aktor' => 'Auditee',
                'pre'   => 'Periode audit aktif tersedia untuk unit Auditee',
                'main'  => "1. Auditee membuka menu Hasil Audit\n2. Sistem menampilkan indikator yang harus diisi\n3. Auditee mengisi nilai untuk setiap variabel input\n4. Sistem menghitung skor berdasarkan formula\n5. Auditee menyimpan sebagai draf",
                'alt'   => 'Jika formula tidak dapat dievaluasi → sistem menampilkan pesan kesalahan',
                'post'  => 'Hasil audit tersimpan dengan status Draft; dapat diedit kembali',
            ],
            [
                'kode'  => 'UC-23',
                'nama'  => 'Submit Hasil Audit',
                'aktor' => 'Auditee',
                'pre'   => 'Hasil audit telah diisi dan disimpan sebagai draf',
                'main'  => "1. Auditee membuka hasil audit yang telah diisi\n2. Auditee menekan tombol Submit\n3. Sistem mengubah status menjadi Submitted\n4. Sistem mencatat aksi di log aktivitas\n5. Auditor dapat melihat hasil yang disubmit",
                'alt'   => 'Jika ada indikator yang belum terisi → sistem memperingatkan sebelum submit',
                'post'  => 'Hasil audit tidak dapat diubah Auditee; menunggu verifikasi Auditor',
            ],
            [
                'kode'  => 'UC-18',
                'nama'  => 'Verifikasi Hasil Audit',
                'aktor' => 'Auditor',
                'pre'   => 'Hasil audit telah disubmit; Auditor ditugaskan di periode tersebut',
                'main'  => "1. Auditor membuka menu Verifikasi\n2. Sistem menampilkan hasil audit menunggu verifikasi\n3. Auditor memeriksa setiap indikator\n4. Auditor menekan Verifikasi dan menambahkan catatan\n5. Sistem mengubah status menjadi Verified",
                'alt'   => '—',
                'post'  => 'Hasil audit terverifikasi; dapat masuk ke proses pelaporan',
            ],
            [
                'kode'  => 'UC-19',
                'nama'  => 'Minta Revisi Hasil Audit',
                'aktor' => 'Auditor',
                'pre'   => 'Hasil audit telah disubmit; Auditor menemukan ketidaksesuaian',
                'main'  => "1. Auditor membuka hasil audit\n2. Auditor menekan Minta Revisi dan mengisi catatan\n3. Sistem mengubah status menjadi Revision Requested\n4. Auditee dapat melihat catatan revisi",
                'alt'   => '—',
                'post'  => 'Hasil audit dikembalikan ke Auditee untuk diperbaiki',
            ],
        ];

        foreach ($narratives as $n) {
            $section->addTitle("{$n['kode']}: {$n['nama']}", 2);
            $phpWord->addTableStyle('ucNarTable', $tableStyle);
            $narTbl = $section->addTable('ucNarTable');

            $rows = [
                ['Kode Use Case', $n['kode']],
                ['Aktor', $n['aktor']],
                ['Precondition', $n['pre']],
                ['Alur Utama', $n['main']],
                ['Alur Alternatif', $n['alt']],
                ['Postcondition', $n['post']],
            ];
            foreach ($rows as $r) {
                $narTbl->addRow();
                $narTbl->addCell(2500)->addText($r[0], $bodyFontBold);
                $narTbl->addCell(6500)->addText($r[1], $bodyFontStyle);
            }
            $section->addText('', [], ['spaceAfter' => 80]);
        }
        $section->addPageBreak();

        // =============================================
        // BAB 3: ERD
        // =============================================
        $section->addTitle('3. Entity Relationship Diagram (ERD)', 1);
        $section->addText(
            'ERD berikut menggambarkan seluruh entitas domain SI-AMI Polbeng beserta hubungan antar entitasnya. ERD dibangun berdasarkan skema database aktual dari migration files.',
            $bodyFontStyle, ['spaceAfter' => 120]
        );

        $section->addTitle('Ringkasan Entitas', 2);
        $phpWord->addTableStyle('erdTable', $tableStyle);
        $erdTbl = $section->addTable('erdTable');
        $erdTbl->addRow();
        $erdTbl->addCell(1800, $headerCellStyle)->addText('Nama Tabel', $headerFontStyle);
        $erdTbl->addCell(3500, $headerCellStyle)->addText('Entitas (Indonesia)', $headerFontStyle);
        $erdTbl->addCell(3700, $headerCellStyle)->addText('Keterangan', $headerFontStyle);

        $entities = [
            ['users','Pengguna','Seluruh pengguna sistem dengan peran berbeda'],
            ['units','Unit','Unit organisasi objek audit (Prodi, Jurusan, dll.)'],
            ['lembaga_akreditasis','Lembaga Akreditasi','Badan pemberi akreditasi (BAN-PT, LAMEMBA, dll.)'],
            ['instrumen_templates','Template Instrumen','Kumpulan kriteria & indikator untuk satu standar akreditasi'],
            ['kriterias','Kriteria','Standar/butir penilaian utama dalam instrumen'],
            ['template_kriterias','Template-Kriteria (Pivot)','Relasi template dengan kriteria beserta bobot'],
            ['indikators','Indikator','Ukuran spesifik dari setiap kriteria'],
            ['template_indikators','Template-Indikator (Pivot)','Relasi template dengan indikator beserta bobot'],
            ['indikator_inputs','Input Indikator','Variabel input yang harus diisi Auditee'],
            ['rubrik_penilaians','Rubrik Penilaian','Aturan interpretasi nilai indikator'],
            ['audit_periodes','Periode Audit','Satu siklus AMI untuk satu unit satu tahun akademik'],
            ['penugasan_auditors','Penugasan Auditor','Auditor yang bertanggung jawab di suatu periode'],
            ['hasil_audits','Hasil Audit','Nilai dan status per indikator yang diisi Auditee'],
            ['data_audit_inputs','Data Input Audit','Nilai aktual variabel input yang diisikan'],
            ['log_aktivitas_audits','Log Aktivitas','Rekam jejak setiap aksi pada hasil audit'],
            ['berita_acaras','Berita Acara','Dokumen resmi penutup satu periode audit'],
            ['beritas','Berita/Pengumuman','Pengumuman yang dipublikasikan Admin'],
            ['files','Berkas','Lampiran dokumen untuk berbagai entitas (polymorphic)'],
        ];
        foreach ($entities as $e) {
            $erdTbl->addRow();
            $erdTbl->addCell(1800)->addText($e[0], ['size' => 9, 'color' => '1e3a5f']);
            $erdTbl->addCell(3500)->addText($e[1], $bodyFontBold);
            $erdTbl->addCell(3700)->addText($e[2], $bodyFontStyle);
        }

        $section->addText('', [], ['spaceAfter' => 60]);
        $section->addText(
            'Catatan: Source ERD lengkap (format Mermaid erDiagram) tersedia di .planning/docs/design/diagrams/erd.mmd — dapat dirender menggunakan Mermaid Live Editor (mermaid.live).',
            ['italic' => true, 'size' => 9, 'color' => '888888'],
            ['spaceAfter' => 120]
        );
        $section->addPageBreak();

        // =============================================
        // BAB 4: KAMUS DATA (ringkasan)
        // =============================================
        $section->addTitle('4. Kamus Data', 1);
        $section->addText(
            'Kamus data berikut menjelaskan field-field penting pada setiap entitas domain. Kamus data lengkap tersedia di .planning/docs/design/DATA-DICTIONARY.md',
            $bodyFontStyle, ['spaceAfter' => 120]
        );

        $dictEntities = [
            [
                'table' => 'hasil_audits (Hasil Audit)',
                'fields' => [
                    ['status_terkini','String','Opsional','Status terkini: draft / submitted / verified / revision_requested'],
                    ['catatan_final','Text','Opsional','Catatan akhir Auditor setelah verifikasi'],
                    ['audit_periode_id','UUID (FK)','Opsional','Referensi ke Periode Audit'],
                    ['indikator_id','UUID (FK)','Opsional','Indikator yang dinilai'],
                ],
            ],
            [
                'table' => 'audit_periodes (Periode Audit)',
                'fields' => [
                    ['tahun_akademik','String(50)','Opsional','Tahun akademik (misal: 2024/2025)'],
                    ['status','String(20)','Opsional','Status periode (aktif / selesai)'],
                    ['unit_id','UUID (FK)','Opsional','Unit yang diaudit'],
                    ['instrumen_template_id','UUID (FK)','Opsional','Template instrumen yang digunakan'],
                ],
            ],
            [
                'table' => 'indikators (Indikator)',
                'fields' => [
                    ['nama','Text','Opsional','Deskripsi lengkap indikator'],
                    ['tipe','String','Opsional','Tipe: kuantitatif / kualitatif / LKPS'],
                    ['formula_penilaian','Text','Opsional','Rumus otomatis penghitung nilai indikator'],
                    ['kriteria_id','UUID (FK)','Opsional','Kriteria pemilik indikator'],
                ],
            ],
        ];

        foreach ($dictEntities as $de) {
            $section->addTitle($de['table'], 2);
            $phpWord->addTableStyle('dictTable', $tableStyle);
            $dictTbl = $section->addTable('dictTable');
            $dictTbl->addRow();
            $dictTbl->addCell(2000, $headerCellStyle)->addText('Field', $headerFontStyle);
            $dictTbl->addCell(1500, $headerCellStyle)->addText('Tipe Data', $headerFontStyle);
            $dictTbl->addCell(1500, $headerCellStyle)->addText('Constraint', $headerFontStyle);
            $dictTbl->addCell(4000, $headerCellStyle)->addText('Keterangan', $headerFontStyle);
            foreach ($de['fields'] as $f) {
                $dictTbl->addRow();
                $dictTbl->addCell(2000)->addText($f[0], ['size' => 9, 'bold' => true]);
                $dictTbl->addCell(1500)->addText($f[1], ['size' => 9]);
                $dictTbl->addCell(1500)->addText($f[2], ['size' => 9]);
                $dictTbl->addCell(4000)->addText($f[3], ['size' => 9]);
            }
            $section->addText('', [], ['spaceAfter' => 60]);
        }
        $section->addPageBreak();

        // =============================================
        // BAB 5: FLOWCHART
        // =============================================
        $section->addTitle('5. Flowchart Alur Sistem AMI', 1);
        $section->addText(
            'Flowchart berikut menggambarkan alur lengkap proses Audit Mutu Internal (AMI) dari awal hingga akhir.',
            $bodyFontStyle, ['spaceAfter' => 120]
        );

        $phases = [
            ['FASE 1 — Setup Instrumen (Admin)', 'Admin', [
                'Admin membuat data Lembaga Akreditasi (misal: BAN-PT, LAMEMBA)',
                'Admin membuat Template Instrumen dan menautkannya ke lembaga',
                'Admin menambahkan Kriteria dan Indikator ke dalam template',
                'Admin menetapkan Rubrik Penilaian dan Formula untuk setiap indikator',
            ]],
            ['FASE 2 — Pembuatan Periode Audit (Admin)', 'Admin', [
                'Admin membuat Periode Audit baru untuk unit tertentu',
                'Admin memilih Template Instrumen yang akan digunakan',
                'Admin menugaskan Auditor ke periode audit',
            ]],
            ['FASE 3 — Pengisian Hasil Audit (Auditee)', 'Auditee', [
                'Auditee melihat daftar Indikator yang harus diisi',
                'Auditee mengisi nilai variabel input per indikator',
                'Sistem menghitung skor otomatis berdasarkan formula',
                'Auditee menyimpan sebagai Draft (dapat diedit) atau Submit',
            ]],
            ['FASE 4 — Verifikasi (Auditor)', 'Auditor', [
                'Auditor memeriksa hasil audit yang disubmit',
                'Jika sesuai → Auditor menekan Verifikasi (status: Verified)',
                'Jika tidak sesuai → Auditor meminta revisi (status: Revision Requested)',
                'Auditee memperbaiki dan meng-submit ulang',
            ]],
            ['FASE 5 — Pelaporan & Analitik', 'Admin, Direktur', [
                'Sistem menghitung skor dan nilai akhir per unit',
                'Admin membuat Berita Acara Audit',
                'Dashboard menampilkan ranking unit, temuan, dan standar bermasalah',
                'Direktur melihat dashboard eksekutif',
                'Laporan dapat diunduh dalam format Word/PDF',
            ]],
        ];

        $phpWord->addTableStyle('flowTable', $tableStyle);
        $flowTbl = $section->addTable('flowTable');
        $flowTbl->addRow();
        $flowTbl->addCell(500,  $headerCellStyle)->addText('No', $headerFontStyle);
        $flowTbl->addCell(3000, $headerCellStyle)->addText('Fase', $headerFontStyle);
        $flowTbl->addCell(1500, $headerCellStyle)->addText('Pelaksana', $headerFontStyle);
        $flowTbl->addCell(4000, $headerCellStyle)->addText('Aktivitas', $headerFontStyle);

        foreach ($phases as $i => $p) {
            $flowTbl->addRow();
            $flowTbl->addCell(500)->addText((string)($i + 1), $bodyFontBold);
            $flowTbl->addCell(3000)->addText($p[0], $bodyFontBold);
            $flowTbl->addCell(1500)->addText($p[1], $bodyFontStyle);
            $cell = $flowTbl->addCell(4000);
            foreach ($p[2] as $step) {
                $cell->addText('• ' . $step, ['size' => 9]);
            }
        }

        $section->addText('', [], ['spaceAfter' => 60]);
        $section->addText(
            'Catatan: Source Flowchart (format Mermaid) tersedia di .planning/docs/design/diagrams/flowchart.mmd',
            ['italic' => true, 'size' => 9, 'color' => '888888'],
            ['spaceAfter' => 120]
        );

        // =============================================
        // SIMPAN FILE
        // =============================================
        $outputDir  = base_path('.planning/docs/design');
        $outputFile = $outputDir . '/SI-AMI-Design-Docs.docx';

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputFile);

        $this->info("✅ Dokumen berhasil dibuat: {$outputFile}");
        return 0;
    }
}
