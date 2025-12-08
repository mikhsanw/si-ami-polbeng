<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-setting-4 fs-1 position-absolute ms-6"><span class="path1"></span>
                        <span class="path2"></span></i>
                    <select id="filter_kriteria" class="form-control form-control-solid w-400px ps-15">
                        @foreach ($filterOptions as $key => $label)
                            <option {{ $key == $id ? 'selected' : '' }} value="{{ $key }}">{{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="kt_datatable_example_1_export" class="d-none"></div>
            </div>
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 me-3">

                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                    Export
                </button>
                <div id="kt_datatable_example_export_menu"
                    class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4"
                    data-kt-menu="true">
                    <div class="menu-item px-3">
                        <a class="menu-link px-3" data-kt-export="copy">
                            Copy to clipboard
                        </a>
                    </div>
                    <div class="menu-item px-3">
                        <a class="menu-link px-3" data-kt-export="excel">
                            Export as Excel
                        </a>
                    </div>
                    <div class="menu-item px-3">
                        <a class="menu-link px-3" data-kt-export="csv">
                            Export as CSV
                        </a>
                    </div>
                    <div class="menu-item px-3">
                        <a class="menu-link px-3" data-kt-export="pdf">
                            Export as PDF
                        </a>
                    </div>
                </div>

                <div id="kt_datatable_example_buttons" class="d-none"></div>
            </div>
        </div>

        <div class="card-body py-4">
            <table id="datatable" data-id="{{ $id }}" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-0">No</th>
                        <th class="text-center">Kode</th>
                        <th class="text-center">Nama Kriteria</th>
                        <th class="text-center">Jumlah Tidak Terpenuhi</th>
                        <th class="text-center max-w-100px">Action</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">

                </tbody>
            </table>

        </div>
    </div>
    <div class="modal fade" id="modalHeatmap" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                        <span class="heatmap-cell hm-warn" style="width: 24px; height: 24px; font-size: 0.7rem;"></span>
                        <small class="text-muted"><strong>WARN:</strong> Ada indikator belum selesai
                            (pending)</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="heatmap-cell hm-fail" style="width: 24px; height: 24px; font-size: 0.7rem;"></span>
                        <small class="text-muted"><strong>FAIL:</strong> Ada indikator skor rendah</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="heatmap-cell hm-none" style="width: 24px; height: 24px; font-size: 0.7rem;"></span>
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

    @prepend('css')
        <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
            type="text/css" />
        <style>
            .modal-lg {
                max-width: 900px !important;
            }

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
        <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
        <script src="{{ asset('js/' . $backend . '/' . $page->code . '/datatables.js') }}"></script>
        <script src="{{ asset('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
        <script src="{{ asset('js/jquery-crud.js') }}"></script>
        <script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('#filter_kriteria').on('change', function() {
                    let filterValue = $(this).val();
                    console.log('{{ url($page->url) }}' + '?id=' + filterValue);
                    window.location.href = "{{ url(config('master.app.url.backend') . '/' . $page->url) }}" +
                        '?id=' + encodeURIComponent(filterValue); // reload dengan query string
                });
            });

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-heatmap');
                if (!btn) return;

                const kriteriaId = btn.dataset.kriteriaId;

                $('#modalHeatmap').modal('show');

                fetch(
                        `{{ url(config('master.app.url.backend') . '/' . $page->url) }}/${kriteriaId}/show`,
                    )
                    .then(res => res.json())
                    .then(data => {

                        const rows = data.result.map(u => {

                            const statusLabel = u.status === 'ok' ? 'Terpenuhi' :
                                u.status === 'fail' ? 'Tidak Terpenuhi' :
                                u.status === 'warn' ? 'Belum Selesai' :
                                'Belum Ada Penilaian';

                            const tooltip = `
Prodi: ${u.unit},
Status: ${statusLabel},
Indikator Tidak Terpenuhi: ${u.not_met} dari ${u.total},
Selesai: ${u.count_selesai},
Pending: ${u.count_pending},
Final Gagal: ${u.count_fail},
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
                        `{{ url(config('master.app.url.backend') . '/' . $page->url . '/standar') }}/${kriteriaId}/${unitId}/indikator`
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

                fetch('{{ route('dashboard.unit.ranking') }}')
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

            // buka modal temuan
            document.getElementById('btnOpenTemuanTerbanyak').addEventListener('click', function() {

                const modal = new bootstrap.Modal(document.getElementById('modalUnitTemuan'));
                modal.show();

                const container = document.getElementById('unitTemuan_container');
                container.innerHTML = '<div class="text-center py-5">Memuat data…</div>';

                fetch('{{ route('dashboard.unit.temuan-semua') }}')
                    .then(res => res.json())
                    .then(res => {

                        const rows = res.data.map((u, idx) => {

                            const cls = u.total_temuan >= 10 ? 'bg-danger' :
                                u.total_temuan >= 5 ? 'bg-warning text-dark' :
                                'bg-success';

                            return `
                    <tr>
                        <td class="text-center align-middle">${idx + 1}</td>
                        <td class="text-start align-middle">${u.nama}</td>
                        <td class="text-center align-middle">
                            <span class="badge badge-score ${cls}">
                                ${u.total_temuan}
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
                            <th class="text-center">Total Temuan</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
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
    @endprepend

</x-app-layout>
