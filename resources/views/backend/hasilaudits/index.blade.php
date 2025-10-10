<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h1 class="fw-bold">Siklus Audit Aktif</h1>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="row">
                @forelse($auditperiodes as $periode)
                    {{-- Ubah $auditperiode menjadi $periode untuk konsistensi --}}
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 rounded-3">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="fs-2 text-primary me-3">
                                        <i class="ki-duotone ki-bank fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <div>
                                        <h5 class="card-title fw-bold text-gray-800 mb-0">
                                            {{ $periode->nama_periode }}
                                        </h5>
                                        <small class="text-muted">{{ $periode->tahun_akademik }}</small>
                                    </div>
                                </div>

                                <p class="text-muted fs-6 mb-3">Unit:
                                    <strong>{{ $periode->unit->nama ?? 'N/A' }}</strong>
                                </p>

                                {{-- Status Badge Dinamis --}}
                                <span
                                    class="badge rounded-pill fw-medium mb-4 align-self-start {{ $periode->statusClass }}">
                                    {{ $periode->statusText }}
                                </span>

                                {{-- Multiple Progress Bars (Stacked) --}}
                                <div class="mb-2">
                                    <small class="text-muted">Progres Audit: {{ $periode->overall_progress }}% dari
                                        {{ $periode->total_indikator }} Indikator</small>
                                </div>
                                <div class="progress" style="height: 25px;"> {{-- Tinggikan sedikit agar teks di dalam terlihat --}}
                                    @php
                                        $total = $periode->total_indikator;
                                        // Hitung persentase untuk setiap status
                                        $p_selesai =
                                            $total > 0 ? round(($periode->status_counts['selesai'] / $total) * 100) : 0;
                                        $p_diajukan =
                                            $total > 0
                                                ? round(($periode->status_counts['diajukan'] / $total) * 100)
                                                : 0;
                                        $p_draft_dikerjakan =
                                            $total > 0
                                                ? round(($periode->status_counts['draft_dikerjakan'] / $total) * 100)
                                                : 0;
                                        $p_revisi =
                                            $total > 0 ? round(($periode->status_counts['revisi'] / $total) * 100) : 0;
                                        // Sisa 'belum dikerjakan' akan dihitung secara implisit jika progress bar lainnya tidak 100%
                                    @endphp

                                    {{-- Urutan penting untuk stacked bars: yang paling "final" di kiri --}}
                                    @if ($p_selesai > 0)
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $p_selesai }}%;" aria-valuenow="{{ $p_selesai }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                            <small>{{ $periode->status_counts['selesai'] }} Selesai</small>
                                        </div>
                                    @endif
                                    @if ($p_diajukan > 0)
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: {{ $p_diajukan }}%;" aria-valuenow="{{ $p_diajukan }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                            <small>{{ $periode->status_counts['diajukan'] }} Diajukan</small>
                                        </div>
                                    @endif
                                    @if ($p_draft_dikerjakan > 0)
                                        <div class="progress-bar bg-warning" role="progressbar"
                                            style="width: {{ $p_draft_dikerjakan }}%;"
                                            aria-valuenow="{{ $p_draft_dikerjakan }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                            <small>{{ $periode->status_counts['draft_dikerjakan'] }} Draft</small>
                                        </div>
                                    @endif
                                    @if ($p_revisi > 0)
                                        <div class="progress-bar bg-danger" role="progressbar"
                                            style="width: {{ $p_revisi }}%;" aria-valuenow="{{ $p_revisi }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                            <small>{{ $periode->status_counts['revisi'] }} Revisi</small>
                                        </div>
                                    @endif
                                    {{-- Jika ada bagian yang belum dikerjakan dan bukan 0% total, bisa ditampilkan sebagai sisa --}}
                                    @if ($p_selesai + $p_diajukan + $p_draft_dikerjakan + $p_revisi < 100 && $periode->overall_progress < 100)
                                        <div class="progress-bar bg-secondary" role="progressbar"
                                            style="width: {{ 100 - ($p_selesai + $p_diajukan + $p_draft_dikerjakan + $p_revisi) }}%;"
                                            aria-valuenow="{{ 100 - ($p_selesai + $p_diajukan + $p_draft_dikerjakan + $p_revisi) }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                            <small>{{ $periode->status_counts['belum_dikerjakan'] }} Belum</small>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted mt-1 d-block">Indikator Terisi:
                                    {{ $periode->status_counts['total_terisi'] }}</small>


                                {{-- Tombol Aksi Dinamis --}}
                                <a href="{{ route($page->code . '.audit-kriteria', $periode->id) }}"
                                    class="btn btn-primary w-100 fw-semibold mt-auto">
                                    {{ $periode->overall_progress > 0 ? 'Lanjutkan Evaluasi' : 'Mulai Evaluasi' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            Tidak ada siklus audit yang aktif saat ini.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @prepend('css')
        <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
            type="text/css" />
    @endprepend
    @prepend('js')
        <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
        {{-- <script src="{{ asset('js/'.$backend.'/'.$page->code.'/datatables.js') }}"></script> --}} {{-- Mungkin tidak perlu datatables.js di halaman ini --}}
        <script src="{{ asset('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
        <script src="{{ asset('js/jquery-crud.js') }}"></script>
        <script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    @endprepend

</x-app-layout>
