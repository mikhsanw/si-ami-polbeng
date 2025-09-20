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
            @forelse($auditperiodes as $periode) {{-- Menggunakan $periode sesuai controller --}}
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-body p-4 d-flex flex-column">
                            <h4 class="card-title fw-bold">{{ $periode->nama_periode }} ({{ $periode->tahun_akademik }})</h4>
                            <p class="text-muted mb-2">Unit yang Diaudit: <strong>{{ $periode->unit->nama ?? 'N/A' }}</strong></p>

                            {{-- Status Badge Dinamis --}}
                            <span class="badge rounded-pill fw-medium mb-3 align-self-start {{ $periode->statusClass }}">
                                {{ $periode->statusText }}
                            </span>
                            
                            {{-- Multiple Progress Bars (Stacked) --}}
                            <div class="mb-2">
                                <small class="text-muted">Progres Unit: {{ $periode->overall_progress }}% dari {{ $periode->total_indikator }} Indikator</small>
                            </div>
                            <div class="progress" style="height: 25px;">
                                @php
                                    $total = $periode->total_indikator;
                                    // Hitung persentase untuk setiap status
                                    $p_selesai          = ($total > 0) ? round(($periode->status_counts['selesai'] / $total) * 100) : 0;
                                    $p_diajukan         = ($total > 0) ? round(($periode->status_counts['diajukan'] / $total) * 100) : 0;
                                    $p_draft_dikerjakan = ($total > 0) ? round(($periode->status_counts['draft_dikerjakan'] / $total) * 100) : 0;
                                    $p_revisi           = ($total > 0) ? round(($periode->status_counts['revisi'] / $total) * 100) : 0;
                                @endphp

                                {{-- Urutan penting untuk stacked bars: status yang paling "actionable" untuk auditor --}}
                                @if($p_selesai > 0)
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $p_selesai }}%;" aria-valuenow="{{ $p_selesai }}" aria-valuemin="0" aria-valuemax="100">
                                    <small>{{ $periode->status_counts['selesai'] }} Diterima</small>
                                </div>
                                @endif
                                @if($p_diajukan > 0)
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $p_diajukan }}%;" aria-valuenow="{{ $p_diajukan }}" aria-valuemin="0" aria-valuemax="100">
                                    <small>{{ $periode->status_counts['diajukan'] }} Menunggu</small>
                                </div>
                                @endif
                                @if($p_draft_dikerjakan > 0)
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $p_draft_dikerjakan }}%;" aria-valuenow="{{ $p_draft_dikerjakan }}" aria-valuemin="0" aria-valuemax="100">
                                    <small>{{ $periode->status_counts['draft_dikerjakan'] }} Draft Unit</small>
                                </div>
                                @endif
                                @if($p_revisi > 0)
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $p_revisi }}%;" aria-valuenow="{{ $p_revisi }}" aria-valuemin="0" aria-valuemax="100">
                                    <small>{{ $periode->status_counts['revisi'] }} Revisi Unit</small>
                                </div>
                                @endif
                                {{-- Menampilkan sisa 'belum dikerjakan' --}}
                                @if(($p_selesai + $p_diajukan + $p_draft_dikerjakan + $p_revisi) < 100 && $periode->total_indikator > 0)
                                    <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ 100 - ($p_selesai + $p_diajukan + $p_draft_dikerjakan + $p_revisi) }}%;" aria-valuenow="{{ 100 - ($p_selesai + $p_diajukan + $p_draft_dikerjakan + $p_revisi) }}" aria-valuemin="0" aria-valuemax="100">
                                        <small>{{ $periode->status_counts['belum_dikerjakan'] }} Belum</small>
                                    </div>
                                @endif
                            </div>
                            <small class="text-muted mt-1 d-block">Indikator Terisi oleh Unit: {{ $periode->status_counts['total_terisi'] }}</small>

                            {{-- Tombol Aksi Dinamis --}}
                            <a href="{{ route($page->code.'.audit-kriteria', $periode->id) }}" class="btn btn-primary w-100 fw-semibold mt-auto">
                                {{ $periode->status_counts['diajukan'] > 0 ? 'Verifikasi Pengajuan' : 'Lihat Progres Unit' }}
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