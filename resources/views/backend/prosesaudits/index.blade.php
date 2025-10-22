<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span
                            class="path2"></span></i>
                    <input type="text" id="search" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15"
                        placeholder="Search {{ $page->title }}" />
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
            <table id="datatable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-0">No</th>
                        <th class="text-center">Nama Indikator</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Skor Auditee</th>
                        <th class="text-center">Status</th>

                        <th class="text-center max-w-100px">Action</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">

                </tbody>
            </table>

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
    @endprepend

</x-app-layout>
