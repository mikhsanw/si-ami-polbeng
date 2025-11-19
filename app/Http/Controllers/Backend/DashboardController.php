<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditPeriode;
use App\Models\HasilAudit;
use App\Models\InstrumenTemplate;
use App\Models\Unit;
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
        } elseif ($user->hasRole('Admin')) {
            return $this->admin();
        } elseif ($user->hasRole(['Super Admin', 'Direktur'])) {
            return $this->superAdmin();
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

            $allhasilAudits = $periode->hasilAudits;

            foreach ($allhasilAudits as $hasilAudit) {
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

            $allhasilAudits = $periode->hasilAudits;

            foreach ($allhasilAudits as $hasilAudit) {
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
        $totalSiklusAudit = AuditPeriode::where('status', true)->count();
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

            $allhasilAudits = $periode->hasilAudits;

            foreach ($allhasilAudits as $hasilAudit) {
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

    private function superAdmin()
    {
        // --- Statistik Global Sistem ---
        $totalSiklusAudit = AuditPeriode::where('status', true)->count();
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

            $allhasilAudits = $periode->hasilAudits;

            foreach ($allhasilAudits as $hasilAudit) {
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

        $units = Unit::with([
            'auditPeriodes.instrumenTemplate.templateIndikators',
            'auditPeriodes.hasilAudits' => function ($q) {
                $q->where('status_terkini', 'Selesai');
            },
        ])->get();

        $unitSudahDiaudit = $units->filter(function ($unit) {

            foreach ($unit->auditPeriodes as $periode) {

                // Total indikator dalam template
                $totalIndikator = $periode->instrumenTemplate->templateIndikators->count();

                // Total hasil audit selesai
                $selesai = $periode->hasilAudits->count();

                // Jika satu periode saja sudah selesai semua indikator → unit dianggap sudah diaudit
                if ($totalIndikator > 0 && $totalIndikator == $selesai) {
                    return true;
                }
            }

            return false;

        })->count();

        $unitTerbaik = Unit::with([
            'auditPeriodes.instrumenTemplate.templateIndikators',
            'auditPeriodes.hasilAudits',
        ])
            ->get()
            ->map(function ($unit) {

                $totalIndikator = 0;
                $totalSelesai = 0;

                foreach ($unit->auditPeriodes as $periode) {

                    // Hitung total indikator dalam template
                    $totalIndikator += $periode->instrumenTemplate
                        ? $periode->instrumenTemplate->templateIndikators->count()
                        : 0;

                    // Hitung indikator yang selesai
                    $totalSelesai += $periode->hasilAudits
                        ->where('status_terkini', 'Selesai')
                        ->count();
                }

                // Hindari pembagian nol
                $skor = ($totalIndikator > 0)
                    ? round(($totalSelesai / $totalIndikator) * 100, 2)
                    : 0;

                return (object) [
                    'unit_id' => $unit->id,
                    'nama_unit' => $unit->nama,
                    'total_indikator' => $totalIndikator,
                    'total_selesai' => $totalSelesai,
                    'skor_pengisian' => $skor, // persentase
                ];
            })

            ->filter(fn ($u) => $u->total_indikator > 0)
            ->sortByDesc('skor_pengisian')
            ->take(5)
            ->values();

        // ============================
        // 4) Top 5 temuan terbanyak
        // ============================
        $unitTemuan = Unit::with([
            'auditPeriodes.hasilAudits' => function ($q) {
                $q->where('status_terkini', 'Selesai')
                    ->whereNotNull('skor_final');
            },
            'auditPeriodes.instrumenTemplate.lembagaAkreditasi',
        ])
            ->get(['id', 'nama'])
            ->map(function ($unit) {

                $total = 0;

                foreach ($unit->auditPeriodes as $periode) {

                    foreach ($periode->hasilAudits as $hasil) {

                        $skor = floatval($hasil->skor_final);

                        $lembaga = $periode->instrumenTemplate
                                           ->lembagaAkreditasi
                                           ->singkatan ?? null;

                        // aturan final:
                        // LAMEMBA  : hitung jika skor < 1
                        // lainnya  : hitung jika skor < 4
                        $threshold = ($lembaga === 'LAMEMBA') ? 1.0 : 4.0;

                        if ($skor < $threshold) {
                            $total++;
                        }
                    }
                }

                return (object) [
                    'id' => $unit->id,
                    'nama' => $unit->nama,
                    'total_temuan' => $total,
                ];
            })
            ->sortByDesc('total_temuan')
            ->take(5)
            ->values();

        // ============================
        // 5) Standar SPMI Bermasalah
        // ============================

        $all = HasilAudit::with([
            'indikator:id,kriteria_id',
            'indikator.kriteria:id,kode,nama',
            'auditPeriode.instrumenTemplate.lembagaAkreditasi',
        ])
            ->where('status_terkini', '!=', 'Draft')
            ->get();

        $standarBermasalah = $all
            ->filter(function ($ha) {
                // pastikan data lengkap
                return $ha->indikator && $ha->indikator->kriteria;
            })
            ->groupBy(function ($ha) {
                return $ha->indikator->kriteria->id;
            })
            ->map(function ($items) {

                $kriteria = $items->first()->indikator->kriteria;

                $notMet = $items->filter(function ($hasil) {

                    $lembaga = optional(
                        $hasil->auditPeriode->instrumenTemplate->lembagaAkreditasi
                    )->singkatan;

                    $threshold = ($lembaga === 'LAMEMBA') ? 1.0 : 3.0;

                    $skor = floatval($hasil->skor_final);

                    return
                        // Belum selesai → tidak terpenuhi
                        (is_null($hasil->skor_final) && $hasil->status_terkini !== 'Selesai')
                        ||
                        // Skor final di bawah threshold → tidak terpenuhi
                        (! is_null($hasil->skor_final) && $skor < $threshold);
                })->count();

                return [
                    'kriteria_id' => $kriteria->id,
                    'kode' => $kriteria->kode,
                    'nama_kriteria' => $kriteria->nama,
                    'total_not_met' => $notMet,
                    'total_dinilai' => $items->count(),
                ];
            })
            ->values()
            ->sortByDesc('total_not_met')
            ->take(8);

        // ============================
        // 6) Summary kartu
        // ============================
        $hasilFinal = HasilAudit::with('auditPeriode.instrumenTemplate.lembagaAkreditasi')
            ->where('status_terkini', 'Selesai')
            ->whereNotNull('skor_final')
            ->get(['id', 'skor_final', 'audit_periode_id']);

        $totalTemuan = $hasilFinal->count();

        $temuanMayor = $hasilFinal->filter(function ($item) {
            $skor = floatval($item->skor_final);
            $lembaga = $item->auditPeriode->instrumenTemplate->lembagaAkreditasi->singkatan ?? null;

            if ($lembaga === 'LAMEMBA') {
                return $skor < 1;        // Mayor LAMEMBA
            }

            return $skor < 3;            // Mayor non-LAMEMBA
        })->count();

        $temuanMinor = $hasilFinal->filter(function ($item) {
            $skor = floatval($item->skor_final);
            $lembaga = $item->auditPeriode->instrumenTemplate->lembagaAkreditasi->singkatan ?? null;

            if ($lembaga === 'LAMEMBA') {
                return false;            // LAMEMBA tidak punya minor
            }

            return $skor >= 3 && $skor < 4;   // Minor non-LAMEMBA
        })->count();

        $skorSangatBaik = $hasilFinal->filter(function ($item) {
            $skor = floatval($item->skor_final);
            $lembaga = $item->auditPeriode->instrumenTemplate->lembagaAkreditasi->singkatan ?? null;

            if ($lembaga === 'LAMEMBA') {
                return $skor >= 1;       // Sangat Baik LAMEMBA
            }

            return $skor >= 4;           // Sangat Baik non-LAMEMBA
        })->count();

        $totalSemuaTemuan = HasilAudit::where('status_terkini', '!=', 'Draft')->count();
        $temuanSelesai = HasilAudit::where('status_terkini', 'Selesai')->count();

        $progressTL = $totalSemuaTemuan > 0
            ? round(($temuanSelesai / $totalSemuaTemuan) * 100)
            : 0;

        // ============================
        // RETURN FINAL (HANYA SATU)
        // ============================
        return view('backend.dashboards.superadmin', compact(
            'totalSiklusAudit',
            'totalAuditor',
            'totalAuditee',
            'totalInstrumen',
            'pengumuman',
            'chartData',
            'unitTerbaik',
            'unitTemuan',
            'standarBermasalah',
            'totalTemuan',
            'temuanMayor',
            'temuanMinor',
            'skorSangatBaik',
            'progressTL',
            'unitSudahDiaudit',
        ));
    }
}
