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
                            Selamat Datang, Admin {{ auth()->user()->name }}!
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
                                <a href="{{ route('auditperiodes.index') }}"
                                    class="text-white fs-7 fw-bold opacity-75-hover text-hover-white">Kelola <i
                                        class="ki-duotone ki-arrow-right fs-4 text-white"></i></a>
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
                                <a href="{{ route('users.index', ['role' => 'auditor']) }}"
                                    class="text-white fs-7 fw-bold opacity-75-hover text-hover-white">Kelola <i
                                        class="ki-duotone ki-arrow-right fs-4 text-white"></i></a>
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
                                <a href="{{ route('users.index', ['role' => 'auditee']) }}"
                                    class="text-white fs-7 fw-bold opacity-75-hover text-hover-white">Kelola <i
                                        class="ki-duotone ki-arrow-right fs-4 text-white"></i></a>
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
                                <a href="{{ route('instrumentemplates.index') }}"
                                    class="text-white fs-7 fw-bold opacity-75-hover text-hover-white">Kelola <i
                                        class="ki-duotone ki-arrow-right fs-4 text-white"></i></a>
                            </div>
                        </div>
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


                {{-- Bagian Opsional: Grafik Ringkasan Global Status Indikator --}}
                {{-- <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">Distribusi Status Indikator (Semua Audit)</h3>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <canvas id="globalIndicatorStatusChart"></canvas>
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
                // if ($('#globalIndicatorStatusChart').length) {
                //     const ctx = document.getElementById('globalIndicatorStatusChart').getContext('2d');
                //     new Chart(ctx, {
                //         type: 'pie', // atau 'bar'
                //         data: {
                //             labels: ['Selesai', 'Diajukan', 'Draft', 'Revisi', 'Belum Dikerjakan'],
                //             datasets: [{
                //                 data: [{{ $chartData['selesai'] }}, {{ $chartData['diajukan'] }}, {{ $chartData['draft'] }}, {{ $chartData['revisi'] }}, {{ $chartData['belum_dikerjakan'] }}],
                //                 backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d']
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
