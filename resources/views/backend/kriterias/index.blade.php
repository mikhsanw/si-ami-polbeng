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
                        <select id="filter_kriteria" class="form-control form-control-solid w-250px ps-15">
                            @foreach($filterOptions as $key => $label)
                                <option {{ $key == $id ? 'selected' : '' }} value="{{ $key }}">{{$label }}</option>
                            @endforeach
                        </select>
                </div>
                <div id="kt_datatable_example_1_export" class="d-none"></div>
            </div>
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 me-3">
                
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
                    @if(!$id)
                        <p class="text-muted">Lembaga Akreditasi belum dipilih.</p>
                    @else
                        <p class="text-muted">Belum ada data kriteria.</p>
                    @endif
                @endforelse
            </div>
        </div>
    </div>

        @prepend('js')
            <script src="{{ asset('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
            <script src="{{ asset('js/jquery-crud.js') }}"></script>
            <script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
            <script>
                $(document).ready(function () {
                    $('#filter_kriteria').on('change', function () {
                        let filterValue = $(this).val();
                        console.log('{{ url($page->url) }}' + '/' + filterValue);
                        window.location.href = "{{ url(config('master.app.url.backend') . '/' . $page->url) }}" + '/' + filterValue; // reload dengan query string
                    });
                });
            </script>
        @endprepend

</x-app-layout>