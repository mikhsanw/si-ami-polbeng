<x-app-layout>
    <x-slot name="title">
        {{ __('Dashboard Administrator') }}
    </x-slot>

    <div class="card border-transparent" data-bs-theme="light" style="background-color: #1C325E;">
        <!--begin::Body-->
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <div class="card-body d-flex ps-xl-15">
            <!--begin::Wrapper-->
            <div class="m-0">
                <!--begin::Title-->
                <div class="position-relative fs-2x z-index-2 fw-bold text-white mb-7">
                    Welcome to AMI POLBENG</div>
                <!--end::Title-->

            </div>
            <!--begin::Wrapper-->
            <!--begin::Illustration-->
            <img src="assets/media/illustrations/sigma-1/17-dark.png"
                class="position-absolute me-3 bottom-0 end-0 h-200px" alt="">
            <!--end::Illustration-->
        </div>
        <!--end::Body-->
    </div>

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar pt-6 pb-2">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex align-items-stretch">
                <div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
                    <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                        <h1
                            class="page-heading d-flex flex-column justify-content-center text-gray-900 fw-bold fs-3 m-0">
                            Selamat Datang, {{ auth()->user()->name }}!
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
                            <li class="breadcrumb-item text-muted">Ringkasan Sistem Audit</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                {{-- Statistik Global Sistem --}}
                <div class="row g-5 g-xl-8 mb-5">
                    <div class="col-xl-3">
                        <div class="card card-xl-stretch mb-xl-8 bg-primary h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-briefcase fs-2hx text-white mb-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalSiklusAudit }}</div>
                                <div class="fw-semibold text-white fs-7">Siklus Audit Aktif</div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-xl-stretch mb-xl-8 bg-info h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-user-square fs-2hx text-white mb-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalAuditor }}</div>
                                <div class="fw-semibold text-white fs-7">Auditor Aktif</div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-xl-stretch mb-xl-8 bg-warning h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-people fs-2hx text-white mb-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalAuditee }}</div>
                                <div class="fw-semibold text-white fs-7">Auditee Aktif</div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-xl-stretch mb-xl-8 bg-success h-100">
                            <div class="card-body p-5">
                                <i class="ki-duotone ki-file-chart fs-2hx text-white mb-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalInstrumen }}</div>
                                <div class="fw-semibold text-white fs-7">Instrumen Audit Tersedia</div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-4">

                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Unit Sudah Selesai Diaudit</h6>
                                <h3 class="mb-0">{{ $unitSudahDiaudit }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Indikator Diaudit</h6>
                                <h3 class="mb-0">{{ $totalTemuan }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Skor Sangat Baik</h6>
                                <h3 class="mb-0">{{ $skorSangatBaik }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Temuan Mayor</h6>
                                <h3 class="mb-0">{{ $temuanMayor }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Temuan Minor</h6>
                                <h3 class="mb-0">{{ $temuanMinor }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Progress Validasi Auditor</h6>
                                <h3 class="mb-0">{{ $progressTL }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    {{-- Top 5 unit dengan pengisian terbaik --}}
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header">
                                <div class="card-title">
                                    <h6 class="mb-0">Top 5 Unit Dengan Pengisian Terbaik</h6>
                                </div>
                            </div>

                            <div class="card-body">
                                <ul class="list-group">

                                    @forelse ($unitTerbaik as $u)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">

                                            <div class="flex-column">
                                                <strong>{{ $u->nama_unit }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    Selesai: {{ $u->total_selesai }} / {{ $u->total_indikator }}
                                                </small>
                                            </div>

                                            <span class="badge bg-success fs-6">
                                                {{ $u->skor_pengisian }}%
                                            </span>

                                        </li>
                                    @empty
                                        <li class="list-group-item">
                                            Belum ada data pengisian indikator.
                                        </li>
                                    @endforelse

                                </ul>
                            </div>
                        </div>
                    </div>


                    {{-- Top 5 temuan --}}
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="card-title">
                                    <h6 class="mb-0">Top 5 Unit Dengan Temuan Terbanyak</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    @foreach ($unitTemuan as $u)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $u->nama }}
                                            <span class="badge bg-primary">{{ $u->total_temuan }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Standar bermasalah --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h6 class="mb-0">Standar SPMI yang Sering Tidak Terpenuhi</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Standar (Kode)</th>
                                    <th>Nama Kriteria</th>
                                    <th class="text-end">Jumlah Tidak Terpenuhi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($standarBermasalah as $s)
                                    <tr>
                                        <td>{{ $s['kode'] }}</td>
                                        <td>{{ $s['nama_kriteria'] }}</td>
                                        <td class="text-end">{{ $s['total_not_met'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Notifikasi & Pengumuman Sistem --}}
                @if ($pengumuman && $pengumuman->count() > 0)
                    <div class="card mb-5 mb-xl-10">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <h3 class="fw-bold m-0">Pemberitahuan Sistem</h3>
                            </div>
                            <div class="card-toolbar">
                                <a href="{{ route('berita.index') }}" class="btn btn-sm btn-light-info">
                                    Lihat Semua Pengumuman
                                </a>
                            </div>
                        </div>
                        <div class="card-body py-4">
                            @foreach ($pengumuman as $item)
                                <div
                                    class="alert alert-{{ $item->type ?? 'info' }} d-flex align-items-center p-5 mb-3">
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
                                            {{ $item->created_at->format('d M Y H:i') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

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
            });
        </script>


        {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
    @endprepend

</x-app-layout>
