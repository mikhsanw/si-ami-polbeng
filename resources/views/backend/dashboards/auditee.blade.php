<x-app-layout>
    <x-slot name="title">{{ __('Dashboard Auditee') }}</x-slot>

    {{-- ── Welcome Banner dengan SVG Progress Ring ───────────────── --}}
    <div class="card mb-6" style="background: linear-gradient(135deg, #1C325E 0%, #2B4A8C 100%); overflow:hidden; position:relative;">
        <div class="card-body d-flex align-items-center gap-7 py-8 px-8" style="min-height:120px">
            {{-- Progress Ring SVG --}}
            <div class="flex-shrink-0 position-relative" style="width:100px;height:100px">
                <svg width="100" height="100" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="10"/>
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#50CD89" stroke-width="10"
                            stroke-dasharray="{{ round($overallProgress * 2.513) }} 251.3"
                            stroke-linecap="round" transform="rotate(-90 50 50)"/>
                </svg>
                <div class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="fw-bolder lh-1 text-white" style="font-size:1.4rem">{{ $overallProgress }}%</div>
                    <div class="text-white opacity-75" style="font-size:.7rem">selesai</div>
                </div>
            </div>
            {{-- Teks motivasi --}}
            <div class="text-white flex-grow-1">
                <h2 class="fw-bold mb-2 text-white">Selamat datang, {{ auth()->user()->name }}!</h2>
                <p class="mb-1 opacity-75 fs-6">
                    <strong class="text-white">{{ $totalSelesaiAll }}</strong> dari <strong class="text-white">{{ $totalIndikatorAll }}</strong> indikator sudah diverifikasi.
                </p>
                <p class="mb-0 fs-7 opacity-75">
                    @if($overallProgress >= 80)
                        Hampir selesai! Pertahankan semangat ini!
                    @elseif($overallProgress >= 50)
                        Lebih dari separuh sudah beres. Terus semangat!
                    @elseif($overallProgress > 0)
                        Progres bagus! Ayo teruskan pengisian indikator.
                    @else
                        Ayo mulai isi indikator — setiap langkah kecil berarti!
                    @endif
                </p>
            </div>
            {{-- Ilustrasi dekoratif --}}
            <div class="d-none d-xl-block position-absolute end-0 bottom-0 opacity-10">
                <i class="ki-duotone ki-chart-pie-simple" style="font-size:180px; color:white">
                    <span class="path1"></span><span class="path2"></span>
                </i>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                {{-- ── Stat Cards ──────────────────────────────────────── --}}
                <div class="row g-5 g-xl-8 mb-5">
                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8 bg-primary h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-briefcase fs-2hx text-white mb-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalAuditAktif }}</div>
                                <div class="fw-semibold text-white fs-7">Siklus Audit Aktif</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8 bg-warning h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-questionnaire-tablet fs-2hx text-white mb-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $indikatorPerluAksi }}</div>
                                <div class="fw-semibold text-white fs-7">Indikator Perlu Tindakan</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8 bg-success h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-award fs-2hx text-white mb-2">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $indikatorTelahSelesai }}</div>
                                <div class="fw-semibold text-white fs-7">Selesai Diverifikasi</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Tindakan Diperlukan (hanya muncul jika ada revisi) ── --}}
                @if($revisiItems->isNotEmpty())
                    <div class="card mb-5" style="border-left: 4px solid #F1416C">
                        <div class="card-header border-0 pt-5 pb-0">
                            <div class="card-title">
                                <i class="ki-duotone ki-cross-circle fs-2 text-danger me-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <h3 class="fw-bold m-0 text-danger">
                                    {{ $revisiItems->count() }} Indikator Perlu Direvisi — Segera Perbaiki!
                                </h3>
                            </div>
                        </div>
                        <div class="card-body pt-4">
                            @foreach($revisiItems as $item)
                                @php $catatanRevisi = $item->logAktivitasAudit->first()?->catatan_aksi; @endphp
                                <div class="d-flex align-items-start gap-4 mb-4 pb-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="symbol symbol-40px flex-shrink-0">
                                        <span class="symbol-label bg-light-danger">
                                            <i class="ki-duotone ki-arrows-circle fs-3 text-danger">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-gray-800">{{ $item->indikator->nama ?? 'Indikator' }}</div>
                                        <div class="text-muted fs-7">
                                            {{ optional($item->auditPeriode->unit)->nama ?? '-' }} — {{ $item->auditPeriode->tahun_akademik ?? '-' }}
                                        </div>
                                        @if($catatanRevisi)
                                            <div class="bg-light-danger rounded p-3 mt-2 fs-7">
                                                <span class="fw-semibold text-danger">Catatan auditor:</span>
                                                <span class="text-gray-700">{{ $catatanRevisi }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('hasilaudits.edit', $item->id) }}"
                                       class="btn btn-sm btn-light-danger flex-shrink-0">
                                        <i class="ki-duotone ki-pencil fs-5 me-1">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        Perbaiki
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ── Donut Chart + Distribusi ─────────────────────────── --}}
                <div class="row g-5 mb-5">
                    <div class="col-xl-5">
                        <div class="card h-100">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title fw-bold">Distribusi Status Indikator</h3>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center pb-5">
                                <canvas id="auditeeStatusChart" style="max-width:260px;max-height:260px"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-center p-7">
                                <h5 class="fw-bold mb-5 text-gray-700">Rincian Status Semua Indikator</h5>
                                @php
                                    $statusItems = [
                                        ['label' => 'Belum Dikerjakan', 'val' => $agregat['belum'],    'color' => 'secondary'],
                                        ['label' => 'Draft/Proses',     'val' => $agregat['draft'],    'color' => 'warning'],
                                        ['label' => 'Menunggu Verif.',  'val' => $agregat['diajukan'], 'color' => 'info'],
                                        ['label' => 'Perlu Revisi',     'val' => $agregat['revisi'],   'color' => 'danger'],
                                        ['label' => 'Selesai',          'val' => $agregat['selesai'],  'color' => 'success'],
                                    ];
                                    $totalAll = array_sum($agregat);
                                @endphp
                                @foreach($statusItems as $si)
                                    @php $pct = $totalAll > 0 ? round($si['val'] / $totalAll * 100) : 0; @endphp
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold fs-7 text-gray-700">{{ $si['label'] }}</span>
                                            <span class="fw-bold fs-7 text-{{ $si['color'] }}">{{ $si['val'] }} ({{ $pct }}%)</span>
                                        </div>
                                        <div class="progress" style="height:8px">
                                            <div class="progress-bar bg-{{ $si['color'] }}" style="width:{{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Pengumuman ─────────────────────────────────────── --}}
                @if($pengumuman && count($pengumuman) > 0)
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title"><h3 class="fw-bold m-0">Pemberitahuan Penting</h3></div>
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
                                        <span>{{ $item->content ?? $item->isi ?? '' }}</span>
                                        <small class="text-muted mt-1">Diterbitkan: {{ $item->created_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ── Progres Siklus Audit ─────────────────────────────── --}}
                <div class="card mb-5">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3 class="fw-bold m-0">Progres Siklus Audit Anda</h3></div>
                        <div class="card-toolbar">
                            <a href="{{ route('prosesaudits.index') }}" class="btn btn-sm btn-light-primary">
                                Lihat Semua Progres
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
                                                    <i class="ki-duotone ki-chart-pie-simple">
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
                                                    Progres Evaluasi: <span class="fw-bold">{{ $periode->overall_progress }}%</span>
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
                                                         data-bs-toggle="tooltip" title="{{ $periode->status_counts['selesai'] }} Selesai">
                                                        <small>{{ $periode->status_counts['selesai'] }} Selesai</small>
                                                    </div>
                                                @endif
                                                @if($p_diajukan > 0)
                                                    <div class="progress-bar bg-info" style="width:{{ $p_diajukan }}%"
                                                         data-bs-toggle="tooltip" title="{{ $periode->status_counts['diajukan'] }} Diajukan">
                                                        <small>{{ $periode->status_counts['diajukan'] }} Diajukan</small>
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
                                            <a href="{{ route('hasilaudits.index') }}"
                                               class="btn btn-primary w-100 fw-semibold mt-auto mt-5">
                                                {{ $periode->overall_progress > 0 ? 'Lanjutkan Evaluasi' : 'Mulai Evaluasi' }}
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
                                        Tidak ada siklus audit yang aktif untuk unit Anda saat ini.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ── Aktivitas Terbaru ─────────────────────────────────── --}}
                @if($recentActivitas->isNotEmpty())
                    @php
                        $aksiMeta = [
                            'SUBMIT_AWAL'     => ['label' => 'Menyerahkan awal',    'icon' => 'ki-send',            'color' => 'primary'],
                            'SUBMIT_REVISI'   => ['label' => 'Menyerahkan revisi',  'icon' => 'ki-send',            'color' => 'warning'],
                            'MINTA_REVISI'    => ['label' => 'Diminta revisi',       'icon' => 'ki-arrows-circle',   'color' => 'danger'],
                            'VALIDASI'        => ['label' => 'Divalidasi auditor',   'icon' => 'ki-verify',          'color' => 'success'],
                            'FINALISASI_SKOR' => ['label' => 'Skor difinalisasi',    'icon' => 'ki-check-circle',    'color' => 'success'],
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

                {{-- ── Peringkat Prodi ──────────────────────────────────────── --}}
                @if($topLengkap->isNotEmpty() || $topTepat->isNotEmpty() || $topCepat->isNotEmpty())
                <div class="card mb-5">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title d-flex align-items-center gap-3">
                            <i class="ki-duotone ki-trophy fs-2 text-warning">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <h3 class="fw-bold m-0">Peringkat Prodi — TA {{ $tahunAktif ?? '-' }}</h3>
                        </div>
                        <div class="card-toolbar">
                            <span class="badge badge-light-primary fs-8">Bandingkan & jadikan motivasi!</span>
                        </div>
                    </div>
                    <div class="card-body pt-2 pb-6">
                        <div class="row g-5">

                            {{-- Kolom: Paling Lengkap --}}
                            <div class="col-xl-4">
                                <div class="d-flex align-items-center gap-2 mb-4">
                                    <i class="ki-duotone ki-verify fs-2 text-success">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <h5 class="fw-bold m-0 text-success">Paling Lengkap</h5>
                                </div>
                                <p class="text-muted fs-8 mb-4">% indikator yang sudah Selesai diverifikasi</p>
                                @foreach($topLengkap as $i => $u)
                                    @php
                                        $medals  = ['🥇','🥈','🥉'];
                                        $isMe    = $u->unit_id === $userUnitId;
                                        $bgClass = $isMe ? 'bg-light-primary border border-primary' : 'bg-light';
                                    @endphp
                                    <div class="d-flex align-items-center gap-3 rounded p-3 mb-2 {{ $bgClass }}">
                                        <div class="fw-bolder fs-4 w-30px text-center">
                                            {{ $medals[$i] ?? ($i + 1) }}
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold text-gray-800 fs-7 text-truncate"
                                                 title="{{ $u->unit_nama }}">
                                                {{ $u->unit_nama }}
                                                @if($isMe) <span class="badge badge-light-primary fs-9 ms-1">Prodi Anda</span> @endif
                                            </div>
                                            <div class="progress mt-1" style="height:6px">
                                                <div class="progress-bar bg-success" style="width:{{ $u->completion_pct }}%"></div>
                                            </div>
                                        </div>
                                        <div class="fw-bolder text-success fs-6 flex-shrink-0">{{ $u->completion_pct }}%</div>
                                    </div>
                                @endforeach
                                @if($topLengkap->isEmpty())
                                    <p class="text-muted fs-8 text-center py-3">Belum ada data</p>
                                @endif
                            </div>

                            {{-- Kolom: Paling Tepat --}}
                            <div class="col-xl-4">
                                <div class="d-flex align-items-center gap-2 mb-4">
                                    <i class="ki-duotone ki-check-circle fs-2 text-primary">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <h5 class="fw-bold m-0 text-primary">Paling Tepat</h5>
                                </div>
                                <p class="text-muted fs-8 mb-4">% indikator Selesai tanpa perlu revisi</p>
                                @forelse($topTepat as $i => $u)
                                    @php
                                        $isMe    = $u->unit_id === $userUnitId;
                                        $bgClass = $isMe ? 'bg-light-primary border border-primary' : 'bg-light';
                                    @endphp
                                    <div class="d-flex align-items-center gap-3 rounded p-3 mb-2 {{ $bgClass }}">
                                        <div class="fw-bolder fs-4 w-30px text-center">
                                            {{ $medals[$i] ?? ($i + 1) }}
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold text-gray-800 fs-7 text-truncate"
                                                 title="{{ $u->unit_nama }}">
                                                {{ $u->unit_nama }}
                                                @if($isMe) <span class="badge badge-light-primary fs-9 ms-1">Prodi Anda</span> @endif
                                            </div>
                                            <div class="progress mt-1" style="height:6px">
                                                <div class="progress-bar bg-primary" style="width:{{ $u->accuracy_pct }}%"></div>
                                            </div>
                                        </div>
                                        <div class="fw-bolder text-primary fs-6 flex-shrink-0">{{ $u->accuracy_pct }}%</div>
                                    </div>
                                @empty
                                    <p class="text-muted fs-8 text-center py-3">Belum ada data</p>
                                @endforelse
                            </div>

                            {{-- Kolom: Tercepat --}}
                            <div class="col-xl-4">
                                <div class="d-flex align-items-center gap-2 mb-4">
                                    <i class="ki-duotone ki-time fs-2 text-warning">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <h5 class="fw-bold m-0 text-warning">Tercepat</h5>
                                </div>
                                <p class="text-muted fs-8 mb-4">Rata-rata hari dari pengajuan ke verifikasi</p>
                                @forelse($topCepat as $i => $u)
                                    @php
                                        $isMe    = $u->unit_id === $userUnitId;
                                        $bgClass = $isMe ? 'bg-light-primary border border-primary' : 'bg-light';
                                    @endphp
                                    <div class="d-flex align-items-center gap-3 rounded p-3 mb-2 {{ $bgClass }}">
                                        <div class="fw-bolder fs-4 w-30px text-center">
                                            {{ $medals[$i] ?? ($i + 1) }}
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold text-gray-800 fs-7 text-truncate"
                                                 title="{{ $u->unit_nama }}">
                                                {{ $u->unit_nama }}
                                                @if($isMe) <span class="badge badge-light-primary fs-9 ms-1">Prodi Anda</span> @endif
                                            </div>
                                            <div class="text-muted fs-8">{{ $u->total_selesai }} indikator selesai</div>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <div class="fw-bolder text-warning fs-6">{{ $u->avg_hari }}</div>
                                            <div class="text-muted fs-9">hari</div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted fs-8 text-center py-3">Belum ada data</p>
                                @endforelse
                            </div>

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
                const ctx = document.getElementById('auditeeStatusChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Belum Dikerjakan', 'Draft/Proses', 'Menunggu Verif.', 'Perlu Revisi', 'Selesai'],
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
