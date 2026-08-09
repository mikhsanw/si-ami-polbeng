<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditPeriode;
use App\Models\HasilAudit;
use App\Models\InstrumenTemplate;
use App\Models\Kriteria;
use App\Models\LogAktivitasAudit;
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
            return $this->superAdmin();
        } elseif ($user->hasRole(['Super Admin', 'Direktur'])) {
            return $this->superAdmin();
        }

        return view($this->view);
    }

    private function auditor()
    {
        $query = AuditPeriode::where('status', true)->where(function ($q) {
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
        $auditperiodesDashboard = $auditperiodes->filter(function ($periode) {
            return $periode->status_counts['diajukan'] > 0 || $periode->status_counts['revisi'] > 0 || $periode->overall_progress < 100;
        })->sortByDesc('status_counts.diajukan')
            ->take(6);

        // --- Agregat status untuk donut chart ---
        $agregat = ['belum' => 0, 'draft' => 0, 'diajukan' => 0, 'revisi' => 0, 'selesai' => 0];
        $totalPeriodeAktif = $auditperiodes->count();
        foreach ($auditperiodes as $p) {
            if (! isset($p->status_counts)) {
                continue;
            }
            $agregat['belum'] += $p->status_counts['belum_dikerjakan'];
            $agregat['draft'] += $p->status_counts['draft_dikerjakan'];
            $agregat['diajukan'] += $p->status_counts['diajukan'];
            $agregat['revisi'] += $p->status_counts['revisi'];
            $agregat['selesai'] += $p->status_counts['selesai'];
        }

        // --- Aktivitas terbaru di semua periode yang ditugaskan ---
        $allHasilIds = $auditperiodes->flatMap(fn ($p) => $p->hasilAudits->pluck('id'));
        $recentActivitas = LogAktivitasAudit::with(['user', 'hasilAudit.auditPeriode.unit'])
            ->whereIn('hasil_audit_id', $allHasilIds)
            ->latest()
            ->take(6)
            ->get();

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
            'agregat',
            'totalPeriodeAktif',
            'recentActivitas',
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
            ->where('status', true)
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

        // --- Filter auditperiodes untuk tampilan dashboard ---
        $auditperiodesDashboard = $auditperiodes->filter(function ($periode) {
            return $periode->statusText !== 'Selesai & Diterima';
        })->take(6);

        // --- Agregat untuk donut chart + progress ring ---
        $agregat = ['belum' => 0, 'draft' => 0, 'diajukan' => 0, 'revisi' => 0, 'selesai' => 0];
        $totalIndikatorAll = 0;
        $totalSelesaiAll = 0;
        foreach ($auditperiodes as $p) {
            if (! isset($p->status_counts)) {
                continue;
            }
            $agregat['belum'] += $p->status_counts['belum_dikerjakan'];
            $agregat['draft'] += $p->status_counts['draft_dikerjakan'];
            $agregat['diajukan'] += $p->status_counts['diajukan'];
            $agregat['revisi'] += $p->status_counts['revisi'];
            $agregat['selesai'] += $p->status_counts['selesai'];
            $totalIndikatorAll += $p->total_indikator;
            $totalSelesaiAll += $p->status_counts['selesai'];
        }
        $overallProgress = $totalIndikatorAll > 0
            ? round($totalSelesaiAll / $totalIndikatorAll * 100)
            : 0;

        // --- Indikator yang perlu direvisi (beserta catatan auditor) ---
        $periodeIds = $auditperiodes->pluck('id');
        $revisiItems = HasilAudit::with([
            'indikator',
            'auditPeriode.unit',
            'logAktivitasAudit' => fn ($q) => $q->where('tipe_aksi', 'MINTA_REVISI')->latest(),
        ])
            ->whereIn('audit_periode_id', $periodeIds)
            ->where('status_terkini', 'Revisi')
            ->get();

        // --- Aktivitas terbaru ---
        $allHasilIds = $auditperiodes->flatMap(fn ($p) => $p->hasilAudits->pluck('id'));
        $recentActivitas = LogAktivitasAudit::with(['user', 'hasilAudit.auditPeriode.unit'])
            ->whereIn('hasil_audit_id', $allHasilIds)
            ->latest()
            ->take(6)
            ->get();

        // --- Ranking Prodi (semua unit, dibandingkan dalam tahun_akademik yang sama) ---
        $tahunAktif = $auditperiodes->pluck('tahun_akademik')->unique()->first()
            ?? AuditPeriode::where('status', true)->orderByDesc('created_at')->value('tahun_akademik');

        $rankingPeriodes = AuditPeriode::with([
            'unit:id,nama',
            'instrumenTemplate.templateIndikators:id,instrumen_template_id',
            'hasilAudits:id,audit_periode_id,status_terkini',
        ])
            ->where('status', true)
            ->when($tahunAktif, fn ($q) => $q->where('tahun_akademik', $tahunAktif))
            ->get();

        $unitStats = $rankingPeriodes->groupBy('unit_id')->map(function ($periodes) {
            $totalIndikator = 0;
            $totalSelesai = 0;
            $selesaiIds = collect();
            foreach ($periodes as $periode) {
                $tpl = $periode->instrumenTemplate;
                if (! $tpl) {
                    continue;
                }
                $totalIndikator += $tpl->templateIndikators->count();
                foreach ($periode->hasilAudits as $ha) {
                    if ($ha->status_terkini === 'Selesai') {
                        $totalSelesai++;
                        $selesaiIds->push($ha->id);
                    }
                }
            }

            return (object) [
                'unit_id' => $periodes->first()->unit_id,
                'unit_nama' => $periodes->first()->unit?->nama ?? '-',
                'total_indikator' => $totalIndikator,
                'total_selesai' => $totalSelesai,
                'completion_pct' => $totalIndikator > 0 ? round($totalSelesai / $totalIndikator * 100, 1) : 0.0,
                'selesai_ids' => $selesaiIds,
            ];
        })->filter(fn ($u) => $u->total_indikator > 0)->values();

        $allSelesaiIds = $unitStats->flatMap(fn ($u) => $u->selesai_ids);

        if ($allSelesaiIds->isNotEmpty()) {
            $withRevisionSet = LogAktivitasAudit::whereIn('hasil_audit_id', $allSelesaiIds)
                ->where('tipe_aksi', 'MINTA_REVISI')
                ->distinct('hasil_audit_id')
                ->pluck('hasil_audit_id')
                ->flip();

            $hasilToUnit = [];
            foreach ($rankingPeriodes as $rp) {
                foreach ($rp->hasilAudits->where('status_terkini', 'Selesai') as $ha) {
                    $hasilToUnit[$ha->id] = $rp->unit_id;
                }
            }

            $submitLogs = \Illuminate\Support\Facades\DB::table('log_aktivitas_audits')
                ->whereIn('hasil_audit_id', $allSelesaiIds->toArray())
                ->where('tipe_aksi', 'SUBMIT_AWAL')
                ->whereNull('deleted_at')
                ->selectRaw('hasil_audit_id, MIN(created_at) as t')
                ->groupBy('hasil_audit_id')
                ->pluck('t', 'hasil_audit_id');

            $validateLogs = \Illuminate\Support\Facades\DB::table('log_aktivitas_audits')
                ->whereIn('hasil_audit_id', $allSelesaiIds->toArray())
                ->whereIn('tipe_aksi', ['VALIDASI', 'FINALISASI_SKOR'])
                ->whereNull('deleted_at')
                ->selectRaw('hasil_audit_id, MIN(created_at) as t')
                ->groupBy('hasil_audit_id')
                ->pluck('t', 'hasil_audit_id');

            $unitDaysMap = [];
            foreach ($allSelesaiIds as $hId) {
                if (! isset($submitLogs[$hId]) || ! isset($validateLogs[$hId])) {
                    continue;
                }
                $uId = $hasilToUnit[$hId] ?? null;
                if (! $uId) {
                    continue;
                }
                $days = \Carbon\Carbon::parse($submitLogs[$hId])
                    ->diffInHours(\Carbon\Carbon::parse($validateLogs[$hId])) / 24;
                if ($days >= 0) {
                    $unitDaysMap[$uId][] = $days;
                }
            }
        } else {
            $withRevisionSet = collect()->flip();
            $unitDaysMap = [];
        }

        $unitStats = $unitStats->map(function ($u) use ($withRevisionSet, $unitDaysMap) {
            $noRevisi = $u->selesai_ids->filter(fn ($id) => ! isset($withRevisionSet[$id]))->count();
            $u->accuracy_pct = $u->total_selesai > 0 ? round($noRevisi / $u->total_selesai * 100, 1) : 0.0;
            $days = $unitDaysMap[$u->unit_id] ?? [];
            $u->avg_hari = count($days) > 0 ? round(array_sum($days) / count($days), 1) : null;

            return $u;
        });

        $topLengkap = $unitStats->sortByDesc('completion_pct')->take(5)->values();
        $topTepat = $unitStats->filter(fn ($u) => $u->total_selesai > 0)->sortByDesc('accuracy_pct')->take(5)->values();
        $topCepat = $unitStats->filter(fn ($u) => $u->avg_hari !== null)->sortBy('avg_hari')->take(5)->values();

        // --- Ambil Pengumuman ---
        $pengumuman = \App\Models\Berita::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // --- Ambil Berita Acara Selesai untuk Unit Auditee ---
        $beritaAcaraSelesai = \App\Models\BeritaAcara::with(['auditPeriode.unit', 'file'])
            ->where(function ($query) use ($user, $userUnitId) {
                $query->whereHas('auditPeriode.unit', fn ($q) => $q->where('user_id', $user->id));
                if ($userUnitId) {
                    $query->orWhereHas('auditPeriode', fn ($q) => $q->where('unit_id', $userUnitId));
                }
            })
            ->latest()
            ->take(5)
            ->get();

        return view('backend.dashboards.auditee', compact(
            'auditperiodesDashboard',
            'totalAuditAktif',
            'indikatorPerluAksi',
            'indikatorTelahSelesai',
            'agregat',
            'overallProgress',
            'totalIndikatorAll',
            'totalSelesaiAll',
            'revisiItems',
            'recentActivitas',
            'topLengkap',
            'topTepat',
            'topCepat',
            'tahunAktif',
            'userUnitId',
            'pengumuman',
            'beritaAcaraSelesai'
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

        // ============================
        // 3) Top 5 unit dengan skor pengisian terbaik
        // ============================
        $unitTerbaik = Unit::with([
            'auditPeriodes.instrumenTemplate.templateIndikators',
            'auditPeriodes.hasilAudits' => function ($q) {
                $q->where('status_terkini', 'Selesai');
            },
        ])->get()->map(function ($unit) {

            $totalIndikator = 0;
            $totalSelesai = 0;

            foreach ($unit->auditPeriodes as $periode) {

                // jika tidak ada template, skip
                if (! $periode->instrumenTemplate) {
                    continue;
                }

                // Hitung total indikator dalam template (jika templateIndikators mungkin null -> 0)
                $indCount = $periode->instrumenTemplate->templateIndikators
                    ? $periode->instrumenTemplate->templateIndikators->count()
                    : 0;

                $totalIndikator += $indCount;

                // Karena kita sudah eager-load hasilAudits hanya yg 'Selesai', cukup count()
                $selesaiCount = $periode->hasilAudits->count();
                $totalSelesai += $selesaiCount;
            }

            $skor = ($totalIndikator > 0)
                ? round(($totalSelesai / $totalIndikator) * 100, 2)
                : 0;

            return (object) [
                'unit_id' => $unit->id,
                'nama_unit' => $unit->nama,
                'total_indikator' => $totalIndikator,
                'total_selesai' => $totalSelesai,
                'skor_pengisian' => $skor,
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
                    'lembaga_akreditasi' => optional(
                        $items->first()->auditPeriode->instrumenTemplate->lembagaAkreditasi
                    )->singkatan,
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

    public function detailStandar($kriteriaId)
    {
        $hasil = HasilAudit::with([
            'auditPeriode.unit:id,nama',
            'auditPeriode.instrumenTemplate.lembagaAkreditasi',
            'indikator:id,kriteria_id',
        ])
            ->whereHas('indikator', fn ($q) => $q->where('kriteria_id', $kriteriaId))
            ->where('status_terkini', '!=', 'Draft') // konsisten dg filter global
            ->get();

        $kriteria = Kriteria::find($kriteriaId);

        // kumpulkan semua unit yang pernah dinilai untuk kriteria ini
        $units = $hasil->map(fn ($h) => $h->auditPeriode->unit)
            ->unique('id')
            ->values();

        $data = $units->map(function ($u) use ($hasil) {

            // semua hasil untuk unit ini & kriteria ini
            $rows = $hasil->filter(fn ($h) => $h->auditPeriode->unit->id === $u->id);

            if ($rows->isEmpty()) {
                return [
                    'unit' => $u->nama,
                    'status' => 'none',
                    'not_met' => 0,
                    'total' => 0,
                ];
            }

            // pilih sample untuk ambil lembaga (anggap sama per periode/template)
            $sample = $rows->first();
            $lembaga = optional($sample->auditPeriode->instrumenTemplate->lembagaAkreditasi)->singkatan;
            $threshold = ($lembaga === 'LAMEMBA') ? 1.0 : 3.0;

            // hitung kategori per indikator
            $count_total = $rows->count();
            $count_selesai = $rows->where('status_terkini', 'Selesai')->count();
            $count_fail = $rows->filter(function ($h) use ($threshold) {
                return $h->status_terkini === 'Selesai'
                    && ! is_null($h->skor_final)
                    && floatval($h->skor_final) < $threshold;
            })->count();
            $count_pending = $rows->filter(fn ($h) => $h->status_terkini !== 'Selesai')->count();

            // not_met: indikator yang belum terpenuhi (final gagal) + indikator belum selesai
            $not_met = $count_fail + $count_pending;

            // Tentukan status dengan prioritas
            if ($count_total === 0) {
                $status = 'none';
            } elseif ($count_fail > 0) {
                $status = 'fail';
            } elseif ($count_pending > 0) {
                // tidak ada fail, tapi masih ada indikator belum selesai
                $status = 'warn';
            } elseif ($count_selesai === $count_total && $count_fail === 0) {
                $status = 'ok';
            } else {
                // fallback
                $status = 'warn';
            }

            return [
                'unit_id' => $u->id,
                'unit' => $u->nama,
                'status' => $status,
                'not_met' => $not_met,
                'total' => $count_total,
                'count_selesai' => $count_selesai,
                'count_fail' => $count_fail,
                'count_pending' => $count_pending,
            ];
        });

        return response()->json([
            'kriteria' => $kriteria,
            'result' => $data->values(),
        ]);
    }

    public function detailIndikator($kriteriaId, $unitId)
    {
        $hasil = HasilAudit::with([
            'indikator:id,kriteria_id,nama,tipe',
            'auditPeriode:id,unit_id,instrumen_template_id',
            'auditPeriode.instrumenTemplate.lembagaAkreditasi',
            'auditPeriode.instrumenTemplate.templateIndikators:bobot,id',
        ])
            ->whereHas('indikator', fn ($q) => $q->where('kriteria_id', $kriteriaId))
            ->whereHas('auditPeriode', fn ($q) => $q->where('unit_id', $unitId))
            ->where('status_terkini', '!=', 'Draft')
            ->get();

        $unit = Unit::find($unitId);
        $kriteria = Kriteria::find($kriteriaId);

        // Tentukan threshold lembaga (ambil dari salah satu hasil)
        $sample = $hasil->first();
        $lembaga = optional($sample->auditPeriode->instrumenTemplate->lembagaAkreditasi)->singkatan;
        $threshold = ($lembaga === 'LAMEMBA') ? 1.0 : 3.0;

        $data = $hasil->map(function ($h) use ($threshold) {

            $skor = floatval($h->skor_final);
            $status = $h->status_terkini;

            if ($status === 'Selesai' && $skor >= $threshold) {
                $class = 'ok';
            } elseif ($status === 'Selesai' && $skor < $threshold) {
                $class = 'fail';
            } else {
                $class = 'warn';
            }

            return [
                'indikator' => $h->indikator->nama,
                'tipe' => $h->indikator->tipe,
                'bobot' => $h->auditPeriode->instrumenTemplate->templateIndikators->firstWhere('id', $h->indikator->id)->bobot ?? null,
                'skor_final' => $h->skor_final,
                'status' => $status,
                'class' => $class,
            ];
        });

        return response()->json([
            'kriteria' => $kriteria,
            'unit' => $unit,
            'threshold' => $threshold,
            'indikators' => $data,
        ]);
    }

    public function unitRanking()
    {
        $units = \App\Models\Unit::with([
            'auditPeriodes.instrumenTemplate.templateIndikators',
            'auditPeriodes.hasilAudits' => function ($q) {
                $q->where('status_terkini', 'Selesai');
            },
        ])->get(['id', 'nama']);

        $rank = $units->map(function ($unit) {

            $totalIndikator = 0;
            $totalSelesai = 0;

            foreach ($unit->auditPeriodes as $periode) {

                if (! $periode->instrumenTemplate) {
                    continue;
                }

                // total indikator template per-periode
                $indCount = $periode->instrumenTemplate->templateIndikators
                    ? $periode->instrumenTemplate->templateIndikators->count()
                    : 0;

                $totalIndikator += $indCount;

                // selesai sudah difilter hanya status = 'Selesai'
                $selesaiCount = $periode->hasilAudits->count();

                $totalSelesai += $selesaiCount;
            }

            $skor = ($totalIndikator > 0)
                ? round(($totalSelesai / $totalIndikator) * 100, 2)
                : 0;

            return [
                'unit_id' => $unit->id,
                'nama' => $unit->nama,
                'total_indikator' => $totalIndikator,
                'total_selesai' => $totalSelesai,
                'skor_pengisian' => $skor,
            ];
        })
            ->filter(fn ($u) => $u['total_indikator'] > 0)
            ->sortByDesc('skor_pengisian')
            ->values();

        return response()->json(['data' => $rank]);
    }

    public function unitTemuanDetail()
    {
        $units = Unit::with([
            'auditPeriodes.hasilAudits' => function ($q) {
                $q->where('status_terkini', 'Selesai')
                    ->whereNotNull('skor_final');
            },
            'auditPeriodes.instrumenTemplate.lembagaAkreditasi',
        ])->get(['id', 'nama']);

        $data = $units->map(function ($unit) {

            $total = 0;

            foreach ($unit->auditPeriodes as $periode) {

                $lembaga = $periode->instrumenTemplate
                                   ->lembagaAkreditasi
                                   ->singkatan ?? null;

                // threshold per lembaga
                $threshold = ($lembaga === 'LAMEMBA') ? 1.0 : 4.0;

                foreach ($periode->hasilAudits as $hasil) {

                    $skor = floatval($hasil->skor_final);

                    if ($skor < $threshold) {
                        $total++;
                    }
                }
            }

            return [
                'unit_id' => $unit->id,
                'nama' => $unit->nama,
                'total_temuan' => $total,
            ];
        })
            ->sortByDesc('total_temuan') // urutkan besar → kecil
            ->values();

        return response()->json([
            'data' => $data,
        ]);
    }
}
