<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h1 class="text-muted">Data {{ $page->title }}</h1>
                <div id="kt_datatable_example_1_export" class="d-none"></div>
            </div>
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 me-3">
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
            <div class="accordion" id="accordion_kriteria_utama">
                @forelse ($kriterias as $kriteria)
                    @include($backend.'.kriterias._kriteria_item', [
                        'item' => $kriteria, 
                        'parent_id' => '#accordion_kriteria_utama'
                    ])
                @empty
                    <p class="text-muted">Belum ada data kriteria.</p>
                @endforelse
            </div>
        </div>
    </div>

        @prepend('js')
            <script src="{{ asset('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
            <script src="{{ asset('js/jquery-crud.js') }}"></script>
            <script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
        @endprepend

</x-app-layout>