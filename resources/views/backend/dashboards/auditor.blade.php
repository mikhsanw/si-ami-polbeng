<x-app-layout>
    <x-slot name="title">{{ __('Dashboard Auditor') }}</x-slot>

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar pt-6 pb-2">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex align-items-stretch">
                <div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
                    <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                        <h1 class="page-heading d-flex flex-column justify-content-center text-gray-900 fw-bold fs-3 m-0">
                            Selamat Datang, Auditor {{ auth()->user()->name }}!
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
                            <li class="breadcrumb-item text-muted">Dashboard Anda</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                {{-- ── Stat Cards ────────────────────────────────────── --}}
                <div class="row g-5 g-xl-8 mb-5">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-xl-stretch mb-xl-8 bg-info h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-verify fs-2hx text-white mb-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $menungguVerifikasi }}</div>
                                <div class="fw-semibold text-white fs-7">Menunggu Verifikasi</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-xl-stretch mb-xl-8 bg-danger h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-cross-circle fs-2hx text-white mb-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $indikatorPerluRevisi }}</div>
                                <div class="fw-semibold text-white fs-7">Perlu Direvisi Auditee</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-xl-stretch mb-xl-8 bg-success h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-check-square fs-2hx text-white mb-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalIndikatorSelesai }}</div>
                                <div class="fw-semibold text-white fs-7">Telah Diverifikasi</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-xl-stretch mb-xl-8 bg-primary h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-briefcase fs-2hx text-white mb-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalPeriodeAktif }}</div>
                                <div class="fw-semibold text-white fs-7">Periode Ditugaskan</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Donut Chart + Pesan Motivasi ──────────────────── --}}
                <div class="row g-5 mb-5">
                    <div class="col-xl-5">
                        <div class="card h-100">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title fw-bold">Distribusi Status Indikator</h3>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center pb-5">
                                <canvas id="auditorStatusChart" style="max-width:260px;max-height:260px"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-center p-7">
                                {{-- Pesan motivasi kontekstual --}}
                                @if($menungguVerifikasi > 0)
                                    <div class="alert alert-info d-flex align-items-center gap-3 mb-5">
                                        <i class="ki-duotone ki-notification-bing fs-2x text-info flex-shrink-0">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        <div>
                                            <div class="fw-bold">Ada tugas menunggu!</div>
                                            <div class="fs-7"><strong>{{ $menungguVerifikasi }} indikator</strong> menunggu verifikasi Anda. Yuk segera ditangani!</div>
                                        </div>
                                    </div>
                                @elseif($indikatorPerluRevisi > 0)
                                    <div class="alert alert-warning d-flex align-items-center gap-3 mb-5">
                                        <i class="ki-duotone ki-time fs-2x text-warning flex-shrink-0">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        <div>
                                            <div class="fw-bold">Revisi sedang diproses</div>
                                            <div class="fs-7"><strong>{{ $indikatorPerluRevisi }} indikator</strong> sedang dalam proses revisi oleh auditee.</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-success d-flex align-items-center gap-3 mb-5">
                                        <i class="ki-duotone ki-award fs-2x text-success flex-shrink-0">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        <div>
                                            <div class="fw-bold">Semua tertangani!</div>
                                            <div class="fs-7">Tidak ada pengajuan yang menunggu. Kerja bagus!</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Mini stats per status --}}
                                <div class="row g-3">
                                    @php
                                        $statusItems = [
                                            ['label' => 'Belum Dikerjakan', 'val' => $agregat['belum'],    'color' => 'secondary', 'icon' => 'ki-minus-circle'],
                                            ['label' => 'Draft/Proses',     'val' => $agregat['draft'],    'color' => 'warning',   'icon' => 'ki-pencil'],
                                            ['label' => 'Menunggu Verif.',  'val' => $agregat['diajukan'], 'color' => 'info',      'icon' => 'ki-send'],
                                            ['label' => 'Revisi',           'val' => $agregat['revisi'],   'color' => 'danger',    'icon' => 'ki-cross-circle'],
                                            ['label' => 'Selesai',          'val' => $agregat['selesai'],  'color' => 'success',   'icon' => 'ki-check-circle'],
                                        ];
                                    @endphp
                                    @foreach($statusItems as $si)
                                        <div class="col-6 col-xl-4">
                                            <div class="bg-light-{{ $si['color'] }} rounded p-3 d-flex align-items-center gap-2">
                                                <i class="ki-duotone {{ $si['icon'] }} fs-3 text-{{ $si['color'] }}">
                                                    <span class="path1"></span><span class="path2"></span>
                                                </i>
                                                <div>
                                                    <div class="fw-bold text-{{ $si['color'] }} fs-5">{{ $si['val'] }}</div>
                                                    <div class="text-muted fs-8">{{ $si['label'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Pengumuman ─────────────────────────────────────── --}}
                @if($pengumuman && count($pengumuman) > 0)
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title"><h3 class="fw-bold m-0">Pemberitahuan & Pengumuman</h3></div>
                        </div>
                        <div class="card-body py-4">
                            @foreach($pengumuman as $item)
                                <div class="alert alert-{{ $item->type ?? 'info' }} d-flex align-items-center p-5 mb-3">
                                    <i class="ki-duotone ki-notification-on fs-2hx text-{{ $item->type ?? 'info' }} me-4">
                                        <span class="path1"></span><span class="path2"></span>
                                        <span class="path3"></span><span class="path4"></span>
                                    </i>
                                    <div class="d-flex flex-column">
                                        <h5 class="mb-1 text-gray-900">{{ $item->judul }}</h5>
                                        <span>{{ $item->isi }}</span>
                                        <small class="text-muted mt-1">Diterbitkan: {{ $item->created_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ── Progres Penugasan Audit ─────────────────────────── --}}
                <div class="card mb-5">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3 class="fw-bold m-0">Progres Penugasan Audit Anda</h3></div>
                        <div class="card-toolbar">
                            <a href="{{ route('prosesaudits.index') }}" class="btn btn-sm btn-light-primary">
                                Lihat Semua Audit
                            </a>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <div class="row g-5 g-xl-8">
                            @forelse($auditperiodesDashboard as $periode)
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 shadow-sm border-0 rounded-3">
                                        <div class="card-body p-4 d-flex flex-column">
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="fs-2 text-primary me-3">
                                                    <i class="ki-duotone ki-bank fs-2">
                                                        <span class="path1"></span><span class="path2"></span>
                                                    </i>
                                                </span>
                                                <div>
                                                    <h5 class="card-title fw-bold text-gray-800 mb-0">{{ $periode->nama_periode }}</h5>
                                                    <small class="text-muted">{{ $periode->tahun_akademik }}</small>
                                                </div>
                                            </div>
                                            <p class="text-muted fs-6 mb-3">Unit: <strong>{{ $periode->unit->nama ?? 'N/A' }}</strong></p>
                                            <span class="badge rounded-pill fw-medium mb-4 align-self-start {{ $periode->statusClass }}">
                                                {{ $periode->statusText }}
                                            </span>
                                            <div class="mb-2">
                                                <small class="text-muted d-block mb-1">
                                                    Progres Unit: <span class="fw-bold">{{ $periode->overall_progress }}%</span>
                                                    dari Total {{ $periode->total_indikator }} Indikator
                                                </small>
                                            </div>
                                            <div class="progress" style="height: 25px;">
                                                @php
                                                    $total = $periode->total_indikator;
                                                    $p_selesai  = $total > 0 ? round(($periode->status_counts['selesai']          / $total) * 100) : 0;
                                                    $p_diajukan = $total > 0 ? round(($periode->status_counts['diajukan']         / $total) * 100) : 0;
                                                    $p_draft    = $total > 0 ? round(($periode->status_counts['draft_dikerjakan'] / $total) * 100) : 0;
                                                    $p_revisi   = $total > 0 ? round(($periode->status_counts['revisi']           / $total) * 100) : 0;
                                                @endphp
                                                @if($p_selesai > 0)
                                                    <div class="progress-bar bg-success" style="width:{{ $p_selesai }}%"
                                                         data-bs-toggle="tooltip" title="{{ $periode->status_counts['selesai'] }} Diterima">
                                                        <small>{{ $periode->status_counts['selesai'] }} Diterima</small>
                                                    </div>
                                                @endif
                                                @if($p_diajukan > 0)
                                                    <div class="progress-bar bg-info" style="width:{{ $p_diajukan }}%"
                                                         data-bs-toggle="tooltip" title="{{ $periode->status_counts['diajukan'] }} Menunggu">
                                                        <small>{{ $periode->status_counts['diajukan'] }} Menunggu</small>
                                                    </div>
                                                @endif
                                                @if($p_draft > 0)
                                                    <div class="progress-bar bg-warning" style="width:{{ $p_draft }}%"
                                                         data-bs-toggle="tooltip" title="{{ $periode->status_counts['draft_dikerjakan'] }} Draft">
                                                        <small>{{ $periode->status_counts['draft_dikerjakan'] }} Draft</small>
                                                    </div>
                                                @endif
                                                @if($p_revisi > 0)
                                                    <div class="progress-bar bg-danger" style="width:{{ $p_revisi }}%"
                                                         data-bs-toggle="tooltip" title="{{ $periode->status_counts['revisi'] }} Revisi">
                                                        <small>{{ $periode->status_counts['revisi'] }} Revisi</small>
                                                    </div>
                                                @endif
                                                @if($p_selesai + $p_diajukan + $p_draft + $p_revisi < 100 && $total > 0)
                                                    <div class="progress-bar bg-secondary"
                                                         style="width:{{ 100 - ($p_selesai + $p_diajukan + $p_draft + $p_revisi) }}%"
                                                         data-bs-toggle="tooltip" title="{{ $periode->status_counts['belum_dikerjakan'] }} Belum">
                                                        <small>{{ $periode->status_counts['belum_dikerjakan'] }} Belum</small>
                                                    </div>
                                                @endif
                                            </div>
                                            <small class="text-muted mt-2 d-block text-end">Terisi: {{ $periode->status_counts['total_terisi'] }}</small>
                                            <a href="{{ route('penugasanaudits.audit-kriteria', $periode->id) }}"
                                               class="btn btn-primary w-100 fw-semibold mt-auto mt-5">
                                                {{ $periode->status_counts['diajukan'] > 0 ? 'Verifikasi Pengajuan' : 'Lihat Progres Unit' }}
                                                <i class="ki-duotone ki-arrow-right fs-4 ms-2">
                                                    <span class="path1"></span><span class="path2"></span>
                                                </i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-warning text-center">
                                        Tidak ada penugasan audit aktif untuk Anda saat ini.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ── Aktivitas Terbaru ───────────────────────────────── --}}
                @if($recentActivitas->isNotEmpty())
                    @php
                        $aksiMeta = [
                            'SUBMIT_AWAL'     => ['label' => 'Menyerahkan awal',    'icon' => 'ki-send',            'color' => 'primary'],
                            'SUBMIT_REVISI'   => ['label' => 'Menyerahkan revisi',  'icon' => 'ki-send',            'color' => 'warning'],
                            'MINTA_REVISI'    => ['label' => 'Meminta revisi',       'icon' => 'ki-arrows-circle',   'color' => 'danger'],
                            'VALIDASI'        => ['label' => 'Memvalidasi',          'icon' => 'ki-verify',          'color' => 'success'],
                            'FINALISASI_SKOR' => ['label' => 'Finalisasi skor',      'icon' => 'ki-check-circle',    'color' => 'success'],
                        ];
                    @endphp
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title"><h3 class="fw-bold m-0">Aktivitas Terbaru</h3></div>
                        </div>
                        <div class="card-body py-4">
                            <div class="row g-4">
                                @foreach($recentActivitas as $log)
                                    @php
                                        $meta  = $aksiMeta[$log->tipe_aksi] ?? ['label' => $log->tipe_aksi, 'icon' => 'ki-information', 'color' => 'secondary'];
                                        $unit  = optional(optional(optional($log->hasilAudit)->auditPeriode)->unit)->nama ?? '-';
                                        $tahun = optional(optional($log->hasilAudit)->auditPeriode)->tahun_akademik ?? '';
                                    @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="d-flex align-items-start gap-3 p-3 bg-light-{{ $meta['color'] }} rounded">
                                            <div class="symbol symbol-40px flex-shrink-0">
                                                <span class="symbol-label bg-{{ $meta['color'] }}">
                                                    <i class="ki-duotone {{ $meta['icon'] }} fs-4 text-white">
                                                        <span class="path1"></span><span class="path2"></span>
                                                    </i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-semibold text-gray-800 fs-7">{{ $log->user->name ?? 'Sistem' }}</div>
                                                <div class="text-muted fs-8">{{ $meta['label'] }} — {{ $unit }} {{ $tahun }}</div>
                                                <div class="text-muted fs-8 mt-1">{{ $log->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @prepend('js')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            $(document).ready(function () {
                // Tooltip Bootstrap
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

                // Donut chart distribusi status
                const ctx = document.getElementById('auditorStatusChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Belum Dikerjakan', 'Draft/Proses', 'Menunggu Verif.', 'Revisi', 'Selesai'],
                            datasets: [{
                                data: [
                                    {{ $agregat['belum'] }},
                                    {{ $agregat['draft'] }},
                                    {{ $agregat['diajukan'] }},
                                    {{ $agregat['revisi'] }},
                                    {{ $agregat['selesai'] }}
                                ],
                                backgroundColor: ['#B5B5C3', '#FFC700', '#009EF7', '#F1416C', '#50CD89'],
                                borderWidth: 2,
                                borderColor: '#fff',
                            }]
                        },
                        options: {
                            cutout: '70%',
                            plugins: {
                                legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ` ${ctx.label}: ${ctx.parsed} indikator`
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endprepend

</x-app-layout>
