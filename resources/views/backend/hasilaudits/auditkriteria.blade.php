<x-app-layout>
    <x-slot name="title">
        {{ __($page->title).' - '.$auditPeriode->tahun_akademik }}
    </x-slot>
    
<div class="card">
    <div class="card-header border-0 pt-6">
        <h1 class="text-muted">{{ $page->title }}</h1>
    </div>

    <div class="card-body py-4">
        <div class="m-0">
            @foreach ($kriterias as $kriteria)
                @include($backend.'.hasilaudits._kriteria_item', ['item' => $kriteria, 'parentId' => 'root'])
                <div class="separator separator-dashed"></div>
            @endforeach
        </div>

    </div>
</div>

@prepend('css')
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endprepend
@prepend('js')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('js/'.$backend.'/'.$page->code.'/datatables.js') }}"></script>
<script src="{{ asset('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
<script src="{{ asset('js/jquery-crud.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
@endprepend

</x-app-layout>