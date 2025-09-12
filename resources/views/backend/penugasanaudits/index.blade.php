<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>
    
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h1 class="fw-bold">Penugasan Audit Anda</h1>
        </div>
    </div>

    <div class="card-body py-4">
        <div class="row">
            @forelse($penugasanAuditor as $penugasan)
                @php
                    // Ambil objek auditPeriode agar lebih mudah dibaca
                    $auditperiode = $penugasan->auditPeriode;
                @endphp
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-body p-4 d-flex flex-column">
                            <h4 class="card-title fw-bold">{{ $auditperiode->tahun_akademik }}</h4>
                            <p class="text-muted mb-2">Unit yang Diaudit: <strong>{{ $auditperiode->unit->nama }}</strong></p>

                            {{-- Status Badge Dinamis --}}
                            <span class="badge rounded-pill fw-medium mb-3 align-self-start {{ $auditperiode->statusClass }}">
                                {{ $auditperiode->statusText }}
                            </span>
                            
                            {{-- Progress Bar Dinamis --}}
                            <div class="progress mb-4" role="progressbar" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ $auditperiode->progress }}%"></div>
                            </div>

                            {{-- Tombol Aksi Dinamis --}}
                            <a href="{{ route($page->code.'.audit-kriteria', $auditperiode->id) }}" class="btn btn-primary w-100 fw-semibold mt-auto">
                                {{ $auditperiode->progress > 0 ? 'Lanjutkan Validasi' : 'Mulai Validasi' }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        Tidak ada penugasan audit yang aktif untuk Anda saat ini.
                    </div>
                </div>
            @endforelse
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