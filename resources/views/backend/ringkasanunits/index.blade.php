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
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    @can($page->code . ' create')
                        <a href="#" class="btn btn-primary btn-action" data-title="Tambah" data-action="create"
                            data-url="{{ config('master.app.url.backend') . '/' . $page->url }}">
                            <i class="ki-duotone ki-plus fs-2"></i>Tambah</a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <table id="datatable" data-id="{{ $id }}" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-0">No</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Auditor</th>
                        <th class="text-center">Status Audit</th>
                        <th class="text-center">Indikator Terisi</th>
                        <th class="text-center">Persentase Terisi</th>
                        <th class="text-center max-w-100px">Action</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">

                </tbody>
            </table>

        </div>
    </div>

    <div class="modal fade" id="modalChart" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content p-5">
                <h3 class="mb-3">Ringkasan Status Indikator</h3>
                <div id="pie_chart" style="height: 350px"></div>
            </div>
        </div>
    </div>

    @prepend('css')
        <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
            type="text/css" />
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
        </script>
        <script>
            document.addEventListener("click", function(e) {
                const btn = e.target.closest('.btn-chart');
                if (!btn) return;

                const selesai = parseInt(btn.dataset.selesai);
                const diajukan = parseInt(btn.dataset.diajukan);
                const revisi = parseInt(btn.dataset.revisi);

                const data = [selesai, diajukan, revisi];
                const labels = ['Selesai', 'Diajukan', 'Revisi'];

                $('#modalChart').modal('show');

                setTimeout(() => {
                    const element = document.querySelector('#pie_chart');
                    element.innerHTML = ""; // reset chart sebelumnya

                    const options = {
                        series: data,
                        labels: labels,
                        colors: [
                            '#16a34a', '#eab308', '#f97316'
                        ],
                        chart: {
                            type: 'donut',
                        },
                        stroke: {
                            show: true,
                            width: 2,
                            colors: 'var(--color-background)',
                        },
                        dataLabels: {
                            enabled: false,
                        },
                        legend: {
                            position: 'bottom',
                        },
                    };

                    const chart = new ApexCharts(element, options);
                    chart.render();
                }, 200);
            });
        </script>
    @endprepend

</x-app-layout>
