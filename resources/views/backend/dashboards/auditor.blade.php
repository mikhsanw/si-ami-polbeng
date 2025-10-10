<x-app-layout>
    <x-slot name="title">
        {{ __('Dashboard Auditor') }}
    </x-slot>

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar pt-6 pb-2">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex align-items-stretch">
                <div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
                    <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                        <h1
                            class="page-heading d-flex flex-column justify-content-center text-gray-900 fw-bold fs-3 m-0">
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

                {{-- Statistik Kunci Auditor --}}
                <div class="row g-5 g-xl-8 mb-5">
                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8 bg-info h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-verify fs-2hx text-white mb-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $menungguVerifikasi }}</div>
                                <div class="fw-semibold text-white fs-7">Indikator Menunggu Verifikasi Anda</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8 bg-danger h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-cross-circle fs-2hx text-white mb-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $indikatorPerluRevisi }}</div>
                                <div class="fw-semibold text-white fs-7">Indikator Perlu Direvisi Auditee</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8 bg-success h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-check-square fs-2hx text-white mb-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalIndikatorSelesai }}</div>
                                <div class="fw-semibold text-white fs-7">Total Indikator Telah Diverifikasi</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notifikasi/Pengumuman (Opsional, bisa juga dari admin) --}}
                @if ($pengumuman && count($pengumuman) > 0)
                    <div class="card mb-5 mb-xl-10">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <h3 class="fw-bold m-0">Pemberitahuan & Pengumuman</h3>
                            </div>
                        </div>
                        <div class="card-body py-4">
                            @foreach ($pengumuman as $item)
                                <div class="alert alert-{{ $item->type ?? 'info' }} d-flex align-items-center p-5 mb-3">
                                    <i
                                        class="ki-duotone ki-notification-on fs-2hx text-{{ $item->type ?? 'info' }} me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                    <div class="d-flex flex-column">
                                        <h5 class="mb-1 text-gray-900">{{ $item->judul }}</h5>
                                        <span>{{ $item->isi }}</span>
                                        <small class="text-muted mt-1">Diterbitkan:
                                            {{ $item->created_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Progres Audit Per Unit/Periode --}}
                <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">Progres Penugasan Audit Anda</h3>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('prosesaudits.index') }}" class="btn btn-sm btn-light-primary">
                                Lihat Semua Audit
                            </a>
                        </div>
                    </div>

                    <div class="card-body py-4">
                        <div class="row g-5 g-xl-8">
                            @forelse($auditperiodesDashboard as $periode)
                                {{-- Subset khusus untuk dashboard --}}
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 shadow-sm border-0 rounded-3">
                                        <div class="card-body p-4 d-flex flex-column">
                                            {{-- Header Kartu Progres --}}
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="fs-2 text-primary me-3">
                                                    <i class="ki-duotone ki-bank fs-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </span>
                                                <div>
                                                    <h5 class="card-title fw-bold text-gray-800 mb-0">
                                                        {{ $periode->nama_periode }}
                                                    </h5>
                                                    <small class="text-muted">{{ $periode->tahun_akademik }}</small>
                                                </div>
                                            </div>

                                            <p class="text-muted fs-6 mb-3">Unit:
                                                <strong>{{ $periode->unit->nama ?? 'N/A' }}</strong>
                                            </p>

                                            {{-- Status Badge Dinamis --}}
                                            <span
                                                class="badge rounded-pill fw-medium mb-4 align-self-start {{ $periode->statusClass }}">
                                                {{ $periode->statusText }}
                                            </span>

                                            {{-- Multiple Progress Bars (Stacked) --}}
                                            <div class="mb-2">
                                                <small class="text-muted d-block mb-1">
                                                    Progres Unit: <span
                                                        class="fw-bold">{{ $periode->overall_progress }}%</span>
                                                    dari Total {{ $periode->total_indikator }} Indikator
                                                </small>
                                            </div>
                                            <div class="progress" style="height: 25px;">
                                                @php
                                                    $total = $periode->total_indikator;
                                                    $p_selesai =
                                                        $total > 0
                                                            ? round(($periode->status_counts['selesai'] / $total) * 100)
                                                            : 0;
                                                    $p_diajukan =
                                                        $total > 0
                                                            ? round(
                                                                ($periode->status_counts['diajukan'] / $total) * 100,
                                                            )
                                                            : 0;
                                                    $p_draft_dikerjakan =
                                                        $total > 0
                                                            ? round(
                                                                ($periode->status_counts['draft_dikerjakan'] / $total) *
                                                                    100,
                                                            )
                                                            : 0;
                                                    $p_revisi =
                                                        $total > 0
                                                            ? round(($periode->status_counts['revisi'] / $total) * 100)
                                                            : 0;
                                                    $p_belum =
                                                        $total > 0
                                                            ? round(
                                                                ($periode->status_counts['belum_dikerjakan'] / $total) *
                                                                    100,
                                                            )
                                                            : 0;
                                                @endphp

                                                {{-- Urutan penting untuk stacked bars: status yang paling "actionable" untuk auditor --}}
                                                @if ($p_selesai > 0)
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $p_selesai }}%;"
                                                        aria-valuenow="{{ $p_selesai }}" aria-valuemin="0"
                                                        aria-valuemax="100" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="{{ $periode->status_counts['selesai'] }} Indikator Diterima">
                                                        <small>{{ $periode->status_counts['selesai'] }}
                                                            Diterima</small>
                                                    </div>
                                                @endif
                                                @if ($p_diajukan > 0)
                                                    <div class="progress-bar bg-info" role="progressbar"
                                                        style="width: {{ $p_diajukan }}%;"
                                                        aria-valuenow="{{ $p_diajukan }}" aria-valuemin="0"
                                                        aria-valuemax="100" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="{{ $periode->status_counts['diajukan'] }} Indikator Menunggu Verifikasi">
                                                        <small>{{ $periode->status_counts['diajukan'] }}
                                                            Menunggu</small>
                                                    </div>
                                                @endif
                                                @if ($p_draft_dikerjakan > 0)
                                                    <div class="progress-bar bg-warning" role="progressbar"
                                                        style="width: {{ $p_draft_dikerjakan }}%;"
                                                        aria-valuenow="{{ $p_draft_dikerjakan }}" aria-valuemin="0"
                                                        aria-valuemax="100" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="{{ $periode->status_counts['draft_dikerjakan'] }} Indikator Draft Unit">
                                                        <small>{{ $periode->status_counts['draft_dikerjakan'] }} Draft
                                                            Unit</small>
                                                    </div>
                                                @endif
                                                @if ($p_revisi > 0)
                                                    <div class="progress-bar bg-danger" role="progressbar"
                                                        style="width: {{ $p_revisi }}%;"
                                                        aria-valuenow="{{ $p_revisi }}" aria-valuemin="0"
                                                        aria-valuemax="100" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="{{ $periode->status_counts['revisi'] }} Indikator Revisi Unit">
                                                        <small>{{ $periode->status_counts['revisi'] }} Revisi
                                                            Unit</small>
                                                    </div>
                                                @endif
                                                @if ($p_selesai + $p_diajukan + $p_draft_dikerjakan + $p_revisi < 100 && $periode->total_indikator > 0)
                                                    <div class="progress-bar bg-secondary" role="progressbar"
                                                        style="width: {{ 100 - ($p_selesai + $p_diajukan + $p_draft_dikerjakan + $p_revisi) }}%;"
                                                        aria-valuenow="{{ 100 - ($p_selesai + $p_diajukan + $p_draft_dikerjakan + $p_revisi) }}"
                                                        aria-valuemin="0" aria-valuemax="100"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="{{ $periode->status_counts['belum_dikerjakan'] }} Indikator Belum Dikerjakan">
                                                        <small>{{ $periode->status_counts['belum_dikerjakan'] }}
                                                            Belum</small>
                                                    </div>
                                                @endif
                                            </div>
                                            <small class="text-muted mt-2 d-block text-end">Indikator Terisi oleh Unit:
                                                {{ $periode->status_counts['total_terisi'] }}</small>

                                            {{-- Tombol Aksi --}}
                                            <a href="{{ route('penugasanaudits.audit-kriteria', $periode->id) }}"
                                                class="btn btn-primary w-100 fw-semibold mt-auto mt-5">
                                                {{ $periode->status_counts['diajukan'] > 0 ? 'Verifikasi Pengajuan' : 'Lihat Progres Unit' }}
                                                <i class="ki-duotone ki-arrow-right fs-4 ms-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-warning text-center">
                                        Tidak ada penugasan audit aktif untuk Anda saat ini.
                                        <a href="#" class="alert-link">Hubungi administrator untuk
                                            penugasan.</a>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Bagian Opsional: Grafik Ringkasan Global --}}
                {{-- Anda bisa menambahkan grafik menggunakan Chart.js atau sejenisnya di sini --}}
                {{-- <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">Ringkasan Global Status Audit</h3>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <canvas id="globalAuditStatusChart"></canvas>
                    </div>
                </div> --}}

            </div>
        </div>
    </div>

    @prepend('css')
        {{-- Tambahan CSS khusus jika ada --}}
    @endprepend

    @prepend('js')
        <script>
            $(document).ready(function() {
                // Inisialisasi Tooltip Bootstrap
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })

                // Contoh untuk Chart.js (jika Anda ingin menambahkan grafik)
                // if ($('#globalAuditStatusChart').length) {
                //     const ctx = document.getElementById('globalAuditStatusChart').getContext('2d');
                //     new Chart(ctx, {
                //         type: 'pie', // atau 'bar'
                //         data: {
                //             labels: ['Menunggu Verifikasi', 'Perlu Revisi', 'Selesai', 'Sedang Dikerjakan'],
                //             datasets: [{
                //                 data: [{{ $menungguVerifikasi }}, {{ $indikatorPerluRevisi }}, {{ $totalIndikatorSelesai }}, {{ $totalIndikatorSedangDikerjakan }}], // Sesuaikan dengan data dari controller
                //                 backgroundColor: ['#007bff', '#dc3545', '#28a745', '#ffc107']
                //             }]
                //         },
                //         options: {
                //             responsive: true,
                //             plugins: {
                //                 legend: {
                //                     position: 'top',
                //                 },
                //                 tooltip: {
                //                     callbacks: {
                //                         label: function(context) {
                //                             let label = context.label || '';
                //                             if (label) {
                //                                 label += ': ';
                //                             }
                //                             if (context.parsed !== null) {
                //                                 label += context.parsed;
                //                             }
                //                             return label;
                //                         }
                //                     }
                //                 }
                //             }
                //         }
                //     });
                // }
            });
        </script>
        {{-- Jika menggunakan Chart.js, tambahkan link CDN-nya --}}
        {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
    @endprepend

</x-app-layout>
