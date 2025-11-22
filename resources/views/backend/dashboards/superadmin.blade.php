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
                                    <!-- Tombol panggil modal -->
                                    <button class="btn btn-sm btn-light-primary" id="btnOpenUnitRanking">
                                        <i class="fas fa-eye"></i> Semua
                                    </button>
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

                                            <span
                                                class="badge {{ $u->skor_pengisian > 80 ? 'bg-success' : ($u->skor_pengisian > 50 ? 'bg-warning' : 'bg-danger') }} fs-6">
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
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-light-info btn-heatmap"
                                                data-kriteria-id="{{ $s['kriteria_id'] }}"
                                                title="Detail Prodi Bermasalah">
                                                {{ $s['total_not_met'] }}
                                            </button>

                                        </td>
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

    <div class="modal fade" id="modalHeatmap" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Heatmap Standar - Prodi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div id="heatmap_container" class="table-responsive">
                        <!-- Heatmap akan dimuat lewat JS -->
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="heatmap-cell hm-ok" style="width: 24px; height: 24px; font-size: 0.7rem;"></span>
                        <small class="text-muted"><strong>OK:</strong> Semua indikator selesai & memenuhi
                            threshold</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="heatmap-cell hm-warn"
                            style="width: 24px; height: 24px; font-size: 0.7rem;"></span>
                        <small class="text-muted"><strong>WARN:</strong> Ada indikator belum selesai
                            (pending)</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="heatmap-cell hm-fail"
                            style="width: 24px; height: 24px; font-size: 0.7rem;"></span>
                        <small class="text-muted"><strong>FAIL:</strong> Ada indikator skor rendah</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="heatmap-cell hm-none"
                            style="width: 24px; height: 24px; font-size: 0.7rem;"></span>
                        <small class="text-muted"><strong>NONE:</strong> Tidak ada data penilaian</small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailIndikator" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detail Indikator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="detail_indikator_container">
                    <!-- isi dari JS -->
                </div>

            </div>
        </div>
    </div>

    <!-- Modal ranking -->
    <div class="modal fade" id="modalUnitRanking" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ranking Unit — Pengisian Indikator (Terbaik → Terburuk)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="unitRanking_container" class="table-responsive">
                        <!-- akan diisi oleh JS -->
                        <div class="text-center py-5">Memuat data…</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">Urut berdasarkan persentase pengisian.</small>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>


    @prepend('css')
        {{-- Tambahan CSS khusus jika ada --}}

        <style>
            .heatmap-cell {
                width: 36px;
                height: 36px;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 0.80rem;
                font-weight: 700;
            }

            .hm-ok {
                background: #16a34a;
            }

            /* hijau */
            .hm-warn {
                background: #eab308;
                color: #0f172a;
            }

            /* kuning (text gelap) */
            .hm-fail {
                background: #dc2626;
            }

            /* merah */
            .hm-none {
                background: #d1d5db;
                color: #0f172a;
            }


            .badge-score {
                min-width: 72px;
                display: inline-block;
                text-align: center;
                font-weight: 600;
            }
        </style>
    @endprepend

    @prepend('js')
        <script>
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-heatmap');
                if (!btn) return;

                const kriteriaId = btn.dataset.kriteriaId;

                $('#modalHeatmap').modal('show');

                fetch(
                        `{{ url(config('master.app.url.backend') . '/' . $page->url . '/standar') }}/${kriteriaId}/detail`, {
                            method: "GET",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({})
                        })
                    .then(res => res.json())
                    .then(data => {

                        const rows = data.result.map(u => {

                            const statusLabel = u.status === 'ok' ? 'Terpenuhi' :
                                u.status === 'fail' ? 'Tidak Terpenuhi' :
                                u.status === 'warn' ? 'Belum Selesai' :
                                'Belum Ada Penilaian';

                            const tooltip = `
Prodi: ${u.unit}
Status: ${statusLabel}
Indikator Tidak Terpenuhi: ${u.not_met} dari ${u.total}
Selesai: ${u.count_selesai}
Pending: ${u.count_pending}
Final Gagal: ${u.count_fail}
`.trim();

                            return `
                <tr>
                    <td class="text-start">${u.unit}</td>
                    <td class="text-center">
                        <span class="btn heatmap-cell hm-${u.status}"
                        data-unit-id="${u.unit_id}"
                        data-kriteria-id="${data.kriteria.id}"
                        data-bs-toggle="tooltip"
                        title="${tooltip}">
                        ${u.not_met > 0 ? u.not_met : ''}
                    </span>

                    </td>
                </tr>`;
                        }).join('');

                        document.querySelector('#heatmap_container').innerHTML = `
                <h5 class="mb-4">
                    Detail Prodi Bermasalah – <strong>${data.kriteria.kode ?? ''}</strong><br>
                    <small>${data.kriteria.nama ?? ''}</small>
                </h5>

                <table class="table table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th class="text-start">Unit / Prodi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            `;

                        // re-init tooltip bootstrap
                        var tooltipTriggerList = [].slice.call(
                            document.querySelectorAll('#heatmap_container [data-bs-toggle="tooltip"]')
                        );
                        tooltipTriggerList.map(t => new bootstrap.Tooltip(t));
                    });
            });

            document.addEventListener('click', function(e) {
                const cell = e.target.closest('.heatmap-cell');
                if (!cell) return;

                const kriteriaId = cell.dataset.kriteriaId;
                const unitId = cell.dataset.unitId;

                // buka modal
                $('#modalDetailIndikator').modal('show');

                fetch(
                        `{{ url(config('master.app.url.backend') . '/' . $page->url . '/standar') }}/${kriteriaId}/${unitId}/indikator`, {
                            method: "GET",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            }
                        }
                    )
                    .then(res => res.json())
                    .then(res => {

                        const rows = res.indikators.map(i => {
                            return `
                    <tr>
                        <td class="text-start">${i.indikator}</td>
                        <td>${i.tipe}</td>
                        <td>${i.skor_final ?? '-'}</td>
                        <td><span class="badge bg-${i.class}">
                            ${i.class.toUpperCase()}
                        </span></td>
                    </tr>
                `;
                        }).join('');

                        document.querySelector('#detail_indikator_container').innerHTML = `
                <h5 class="mb-3">
                    Prodi: <strong>${res.unit.nama}</strong><br>
                    Standar: <strong>${res.kriteria.kode}</strong> – ${res.kriteria.nama}<br>
                    Threshold: <strong>${res.threshold}</strong>
                </h5>

                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Indikator</th>
                            <th>Tipe</th>
                            <th>Skor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            `;
                    });
            });

            document.getElementById('btnOpenUnitRanking').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('modalUnitRanking'));
                modal.show();

                const container = document.getElementById('unitRanking_container');
                container.innerHTML = '<div class="text-center py-5">Memuat data…</div>';

                fetch('{{ route('dashboard.unit.ranking') }}', {
                        method: "GET",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        const rows = res.data.map((u, idx) => {
                            // warna badge berdasarkan skor
                            const cls = u.skor_pengisian >= 90 ? 'bg-success' :
                                (u.skor_pengisian >= 70 ? 'bg-warning text-dark' : 'bg-danger');

                            return `
                    <tr>
                        <td class="text-center align-middle">${idx + 1}</td>
                        <td class="text-start align-middle">${u.nama}</td>
                        <td class="text-center align-middle">${u.total_indikator}</td>
                        <td class="text-center align-middle">${u.total_selesai}</td>
                        <td class="text-center align-middle">
                            <span class="badge badge-score ${cls}">
                                ${u.skor_pengisian}%
                            </span>
                        </td>
                    </tr>
                `;
                        }).join('');

                        container.innerHTML = `
                <table class="table table-hover table-sm align-middle">
                    <thead>
                        <tr>
                            <th style="width:48px">#</th>
                            <th>Unit / Prodi</th>
                            <th class="text-center">Total Indikator</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            `;
                    })
                    .catch(err => {
                        container.innerHTML =
                            `<div class="text-danger p-4">Gagal memuat data. Coba refresh halaman.</div>`;
                        console.error(err);
                    });
            });
        </script>


        {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
    @endprepend

</x-app-layout>
