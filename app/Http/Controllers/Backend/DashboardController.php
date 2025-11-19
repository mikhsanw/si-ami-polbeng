<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditPeriode;
use App\Models\InstrumenTemplate;
use App\Models\User;
use Illuminate\Http\Request;

// use App\Models\Pengumuman; --- IGNORE ---
// Untuk menghitung total indikator

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->hasRole('Auditee')) {
            return $this->auditee();
        } elseif ($user->hasRole('Auditor')) {
            return $this->auditor();
        } elseif ($user->hasRole(['Admin', 'Super Admin', 'Direktur'])) {
            return $this->admin();
        }

        return view($this->view);
    }

    private function auditor()
    {
        $query = AuditPeriode::where(function ($q) {
            // Hanya tampilkan periode audit yang memiliki penugasan auditor
            $q->whereHas('penugasanAuditors', function ($subQ) {
                $subQ->where('user_id', auth()->id());
            });
        });

        $auditperiodes = $query->with([
            'unit',
            'instrumenTemplate.templateIndikators',
            'hasilAudits',
        ])
            ->get();

        // --- Inisialisasi Statistik Dashboard Auditor ---
        $menungguVerifikasi = 0;
        $indikatorPerluRevisi = 0;
        $totalIndikatorSelesai = 0;
        $totalIndikatorSedangDikerjakan = 0; // Untuk grafik opsional

        foreach ($auditperiodes as $periode) {
            $template = $periode->instrumenTemplate;
            $periode->total_indikator = 0;
            $statusCounts = [
                'belum_dikerjakan' => 0,
                'draft_dikerjakan' => 0,
                'diajukan' => 0,
                'revisi' => 0,
                'selesai' => 0,
                'total_terisi' => 0,
            ];
            $periode->overall_progress = 0;
            $periode->statusText = 'Belum Ada Pengajuan'; // Default untuk auditor
            $periode->statusClass = 'text-bg-secondary';

            if (! $template || $template->templateIndikators->isEmpty()) {
                $periode->statusText = 'Instrumen Tidak Ditemukan';
                $periode->statusClass = 'text-bg-secondary';
                $periode->status_counts = $statusCounts;

                continue;
            }

            $totalIndikatorDalamTemplate = $template->templateIndikators->count();
            $periode->total_indikator = $totalIndikatorDalamTemplate;

            $allHasilAudits = $periode->hasilAudits;

            foreach ($allHasilAudits as $hasilAudit) {
                switch ($hasilAudit->status_terkini) {
                    case 'Draft':
                        $statusCounts['draft_dikerjakan']++;
                        break;
                    case 'Diajukan':
                        $statusCounts['diajukan']++;
                        break;
                    case 'Revisi':
                        $statusCounts['revisi']++;
                        break;
                    case 'Selesai':
                        $statusCounts['selesai']++;
                        break;
                    default:
                        $statusCounts['draft_dikerjakan']++; // Default jika status tidak dikenal
                        break;
                }
            }

            $statusCounts['total_terisi'] =
                $statusCounts['draft_dikerjakan'] +
                $statusCounts['diajukan'] +
                $statusCounts['revisi'] +
                $statusCounts['selesai'];

            $statusCounts['belum_dikerjakan'] =
                $totalIndikatorDalamTemplate - $statusCounts['total_terisi'];

            $periode->status_counts = $statusCounts;

            // Update Statistik Dashboard Auditor
            $menungguVerifikasi += $statusCounts['diajukan'];
            $indikatorPerluRevisi += $statusCounts['revisi'];
            $totalIndikatorSelesai += $statusCounts['selesai'];
            $totalIndikatorSedangDikerjakan += ($statusCounts['draft_dikerjakan'] + $statusCounts['belum_dikerjakan']); // Untuk grafik

            $periode->overall_progress = ($totalIndikatorDalamTemplate > 0)
                                        ? round(($periode->status_counts['total_terisi'] / $totalIndikatorDalamTemplate) * 100)
                                        : 0;

            // --- Logika Penentuan Status UTAMA untuk AUDITOR (di dashboard) ---
            if ($periode->status_counts['revisi'] > 0) {
                $periode->statusText = 'Ada Revisi untuk Unit';
                $periode->statusClass = 'text-white bg-danger';
            } elseif ($periode->status_counts['diajukan'] > 0) {
                $periode->statusText = 'Menunggu Verifikasi Anda';
                $periode->statusClass = 'text-white bg-info';
            } elseif ($periode->status_counts['selesai'] == $totalIndikatorDalamTemplate && $totalIndikatorDalamTemplate > 0) {
                $periode->statusText = 'Audit Selesai & Diterima';
                $periode->statusClass = 'text-white bg-success';
            } elseif ($periode->overall_progress > 0) {
                $periode->statusText = 'Unit Sedang Bekerja';
                $periode->statusClass = 'text-white bg-warning';
            } else {
                $periode->statusText = 'Belum Ada Progres';
                $periode->statusClass = 'text-bg-secondary';
            }
        }

        // --- Filter auditperiodes untuk tampilan dashboard (opsional) ---
        // Contoh: Tampilkan periode yang perlu tindakan auditor (diajukan/revisi) atau yang terbaru
        $auditperiodesDashboard = $auditperiodes->filter(function ($periode) {
            return $periode->status_counts['diajukan'] > 0 || $periode->status_counts['revisi'] > 0 || $periode->overall_progress < 100;
        })->sortByDesc('status_counts.diajukan') // Prioritaskan yang banyak diajukan
            ->take(6); // Ambil hanya beberapa untuk dashboard

        // --- Ambil Pengumuman (jika ada) ---
        $pengumuman = \App\Models\Berita::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('backend.dashboards.auditor', compact(
            'auditperiodesDashboard',
            'menungguVerifikasi',
            'indikatorPerluRevisi',
            'totalIndikatorSelesai',
            'totalIndikatorSedangDikerjakan',
            'pengumuman'
        ));
    }

    private function auditee()
    {
        $userUnitId = optional(auth()->user()->unit)->id;

        // --- Ambil data Siklus Audit untuk Auditee ---
        $auditperiodes = AuditPeriode::with([
            'unit',
            'instrumenTemplate.templateIndikators',
            'hasilAudits',
        ])
            ->where('unit_id', $userUnitId) // Hanya yang relevan dengan unit auditee
            ->get();

        // --- Inisialisasi Statistik ---
        $totalAuditAktif = $auditperiodes->count();
        $indikatorPerluAksi = 0; // Draft atau Revisi
        $indikatorTelahSelesai = 0; // Selesai

        foreach ($auditperiodes as $periode) {
            $template = $periode->instrumenTemplate;
            $periode->total_indikator = 0;
            $statusCounts = [
                'belum_dikerjakan' => 0,
                'draft_dikerjakan' => 0,
                'diajukan' => 0,
                'revisi' => 0,
                'selesai' => 0,
                'total_terisi' => 0,
            ];
            $periode->overall_progress = 0;
            $periode->statusText = 'Belum Dikerjakan';
            $periode->statusClass = 'text-white bg-danger';

            if (! $template || $template->templateIndikators->isEmpty()) {
                $periode->statusText = 'Instrumen Tidak Ditemukan';
                $periode->statusClass = 'text-bg-secondary';
                $periode->status_counts = $statusCounts;

                continue;
            }

            $totalIndikatorDalamTemplate = $template->templateIndikators->count();
            $periode->total_indikator = $totalIndikatorDalamTemplate;

            $allHasilAudits = $periode->hasilAudits;

            foreach ($allHasilAudits as $hasilAudit) {
                switch ($hasilAudit->status_terkini) {
                    case 'Draft':
                        $statusCounts['draft_dikerjakan']++;
                        break;
                    case 'Diajukan':
                        $statusCounts['diajukan']++;
                        break;
                    case 'Revisi':
                        $statusCounts['revisi']++;
                        break;
                    case 'Selesai':
                        $statusCounts['selesai']++;
                        break;
                    default:
                        $statusCounts['draft_dikerjakan']++; // Default ke draft jika status lain
                        break;
                }
            }

            $statusCounts['total_terisi'] =
                $statusCounts['draft_dikerjakan'] +
                $statusCounts['diajukan'] +
                $statusCounts['revisi'] +
                $statusCounts['selesai'];

            $statusCounts['belum_dikerjakan'] =
                $totalIndikatorDalamTemplate - $statusCounts['total_terisi'];

            $periode->status_counts = $statusCounts;

            // Update Statistik Dashboard
            $indikatorPerluAksi += ($statusCounts['draft_dikerjakan'] + $statusCounts['revisi']);
            $indikatorTelahSelesai += $statusCounts['selesai'];

            $periode->overall_progress = ($totalIndikatorDalamTemplate > 0)
                                        ? round(($periode->status_counts['total_terisi'] / $totalIndikatorDalamTemplate) * 100)
                                        : 0;

            // Logika Penentuan Status Utama (untuk auditee)
            if ($periode->overall_progress == 0) {
                $periode->statusText = 'Belum Dikerjakan';
                $periode->statusClass = 'text-white bg-danger';
            } elseif ($periode->status_counts['selesai'] == $totalIndikatorDalamTemplate) {
                $periode->statusText = 'Selesai & Diterima';
                $periode->statusClass = 'text-white bg-success';
            } elseif ($periode->status_counts['diajukan'] == $totalIndikatorDalamTemplate) {
                $periode->statusText = 'Menunggu Verifikasi (100% Diajukan)';
                $periode->statusClass = 'text-white bg-info';
            } elseif ($periode->status_counts['total_terisi'] > 0) {
                $periode->statusText = 'Sedang Berlangsung';
                $periode->statusClass = 'text-white bg-warning';
            } else {
                $periode->statusText = 'Tidak Diketahui';
                $periode->statusClass = 'text-bg-secondary';
            }
        }

        // --- Filter auditperiodes untuk tampilan dashboard (opsional) ---
        // Misalnya, hanya tampilkan yang statusnya bukan 'Selesai & Diterima'
        $auditperiodesDashboard = $auditperiodes->filter(function ($periode) {
            return $periode->statusText !== 'Selesai & Diterima';
        })->take(6); // Ambil hanya 6 untuk dashboard agar tidak terlalu panjang

        // --- Ambil Pengumuman ---
        // Asumsi model Pengumuman memiliki kolom 'type', 'title', 'content', 'created_at'
        $pengumuman = \App\Models\Berita::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('backend.dashboards.auditee', compact(
            'auditperiodesDashboard',
            'totalAuditAktif',
            'indikatorPerluAksi',
            'indikatorTelahSelesai',
            'pengumuman'
        ));
    }

    private function admin()
    {
        // --- Statistik Global Sistem ---
        $totalSiklusAudit = AuditPeriode::count();
        $totalAuditor = User::role('auditor')->count(); // Asumsi Anda menggunakan Spatie Permission
        $totalAuditee = User::role('auditee')->count(); // Asumsi Anda menggunakan Spatie Permission
        $totalInstrumen = InstrumenTemplate::count();

        // --- Ambil Pengumuman Terbaru ---
        $pengumuman = \App\Models\Berita::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // --- Ambil data Siklus Audit untuk Progres Global ---
        // Admin melihat semua periode audit aktif dari semua unit
        $auditperiodes = AuditPeriode::with([
            'unit',
            'instrumenTemplate.templateIndikators',
            'hasilAudits',
        ])
            ->get();

        // --- Inisialisasi Statistik Global Indikator (untuk grafik opsional) ---
        $chartData = [
            'selesai' => 0,
            'diajukan' => 0,
            'draft' => 0,
            'revisi' => 0,
            'belum_dikerjakan' => 0,
        ];

        foreach ($auditperiodes as $periode) {
            $template = $periode->instrumenTemplate;
            $periode->total_indikator = 0;
            $statusCounts = [
                'belum_dikerjakan' => 0,
                'draft_dikerjakan' => 0,
                'diajukan' => 0,
                'revisi' => 0,
                'selesai' => 0,
                'total_terisi' => 0,
            ];
            $periode->overall_progress = 0;
            $periode->statusText = 'Belum Ada Progres'; // Default untuk admin
            $periode->statusClass = 'text-bg-secondary';

            if (! $template || $template->templateIndikators->isEmpty()) {
                $periode->statusText = 'Instrumen Tidak Ditemukan';
                $periode->statusClass = 'text-bg-secondary';
                $periode->status_counts = $statusCounts;
                $periode->total_indikator_template = 0; // Tambahkan ini agar tidak error di blade

                continue;
            }

            $totalIndikatorDalamTemplate = $template->templateIndikators->count();
            $periode->total_indikator = $totalIndikatorDalamTemplate;

            $allHasilAudits = $periode->hasilAudits;

            foreach ($allHasilAudits as $hasilAudit) {
                switch ($hasilAudit->status_terkini) {
                    case 'Draft':
                        $statusCounts['draft_dikerjakan']++;
                        break;
                    case 'Diajukan':
                        $statusCounts['diajukan']++;
                        break;
                    case 'Revisi':
                        $statusCounts['revisi']++;
                        break;
                    case 'Selesai':
                        $statusCounts['selesai']++;
                        break;
                    default:
                        $statusCounts['draft_dikerjakan']++;
                        break;
                }
            }

            $statusCounts['total_terisi'] =
                $statusCounts['draft_dikerjakan'] +
                $statusCounts['diajukan'] +
                $statusCounts['revisi'] +
                $statusCounts['selesai'];

            $statusCounts['belum_dikerjakan'] =
                $totalIndikatorDalamTemplate - $statusCounts['total_terisi'];

            $periode->status_counts = $statusCounts;

            // Update Statistik Global Indikator untuk Grafik
            $chartData['selesai'] += $statusCounts['selesai'];
            $chartData['diajukan'] += $statusCounts['diajukan'];
            $chartData['draft'] += $statusCounts['draft_dikerjakan'];
            $chartData['revisi'] += $statusCounts['revisi'];
            $chartData['belum_dikerjakan'] += $statusCounts['belum_dikerjakan'];

            $periode->overall_progress = ($totalIndikatorDalamTemplate > 0)
                                        ? round(($periode->status_counts['total_terisi'] / $totalIndikatorDalamTemplate) * 100)
                                        : 0;

            // Logika Penentuan Status Utama (untuk admin, bisa sama dengan auditor)
            if ($periode->status_counts['revisi'] > 0) {
                $periode->statusText = 'Ada Revisi untuk Unit';
                $periode->statusClass = 'text-white bg-danger';
            } elseif ($periode->status_counts['diajukan'] > 0) {
                $periode->statusText = 'Menunggu Verifikasi Auditor';
                $periode->statusClass = 'text-white bg-info';
            } elseif ($periode->status_counts['selesai'] == $totalIndikatorDalamTemplate && $totalIndikatorDalamTemplate > 0) {
                $periode->statusText = 'Audit Selesai & Diterima';
                $periode->statusClass = 'text-white bg-success';
            } elseif ($periode->overall_progress > 0) {
                $periode->statusText = 'Unit Sedang Bekerja';
                $periode->statusClass = 'text-white bg-warning';
            } else {
                $periode->statusText = 'Belum Ada Progres';
                $periode->statusClass = 'text-bg-secondary';
            }
        }

        // --- Filter auditperiodes untuk tampilan dashboard (opsional) ---
        // Contoh: Tampilkan periode yang masih aktif dan perlu perhatian
        $auditperiodesGlobal = $auditperiodes->filter(function ($periode) {
            return $periode->overall_progress < 100 || $periode->status_counts['revisi'] > 0 || $periode->status_counts['diajukan'] > 0;
        })->take(6); // Ambil hanya beberapa untuk dashboard

        return view('backend.dashboards.admin', compact(
            'totalSiklusAudit',
            'totalAuditor',
            'totalAuditee',
            'totalInstrumen',
            'pengumuman',
            'auditperiodesGlobal',
            'chartData' // Untuk grafik opsional
        ));
    }
}
