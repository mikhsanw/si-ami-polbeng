<div class="panel">
    <div class="panel-body">
        {{-- Header Status Card --}}
        <div class="card bg-light-success border-success border-dashed p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-40px">
                        <span class="symbol-label bg-success">
                            <i class="fa fa-check-circle fs-2 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <h4 class="fw-bold text-gray-800 mb-0">Berita Acara Audit Mutu Internal</h4>
                        <span class="text-muted fs-7">Dokumen Resmi Pelaksanaan Audit Selesai</span>
                    </div>
                </div>
                <span class="badge badge-success px-3 py-2 fs-7">
                    <i class="fa fa-check-circle text-white me-1"></i>Selesai & Disahkan
                </span>
            </div>
        </div>

        {{-- Detail Metadata Grid --}}
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="border rounded p-4 bg-light">
                    <div class="text-muted fs-7 mb-1">Unit Organisasi (Auditee)</div>
                    <div class="fw-bold fs-6 text-gray-800">{{ $data->auditPeriode->unit->nama ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-4 bg-light">
                    <div class="text-muted fs-7 mb-1">Tahun Akademik</div>
                    <div class="fw-bold fs-6 text-gray-800">{{ $data->auditPeriode->tahun_akademik ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-4 bg-light">
                    <div class="text-muted fs-7 mb-1">Tanggal Dibuat</div>
                    <div class="fw-bold fs-6 text-gray-800">{{ \App\Helpers\Helper::displayDateTime($data->created_at) }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-4 bg-light">
                    <div class="text-muted fs-7 mb-1">Tim Auditor</div>
                    <div class="fw-bold fs-6 text-gray-800">
                        @if(isset($data->auditPeriode->penugasanAuditors) && $data->auditPeriode->penugasanAuditors->isNotEmpty())
                            {{ $data->auditPeriode->penugasanAuditors->map(fn($p) => $p->user->name ?? '')->filter()->join(', ') }}
                        @else
                            <span class="text-muted italic">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Catatan Berita Acara --}}
        <div class="card mb-4 border">
            <div class="card-header border-0 min-h-40px pt-3 pb-0">
                <h5 class="fw-bold m-0 text-gray-800">Catatan / Ringkasan Berita Acara</h5>
            </div>
            <div class="card-body py-3">
                @if($data->catatan)
                    <div class="text-gray-700 fs-6">{!! $data->catatan !!}</div>
                @else
                    <p class="text-muted fs-7 mb-0">Tidak ada catatan tambahan.</p>
                @endif
            </div>
        </div>

        {{-- File View / Preview --}}
        <div class="card border">
            <div class="card-header border-0 min-h-40px pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0 text-gray-800">Dokumen Berita Acara (Lampiran)</h5>
                @if($data->file)
                    <a href="{{ asset($data->file->link_download) }}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="fa fa-download me-1"></i>Unduh Dokumen
                    </a>
                @endif
            </div>
            <div class="card-body py-3">
                @if ($data->file)
                    @if ($data->file->extension != 'pdf')
                        <div class="alert alert-info d-flex align-items-center p-4">
                            <i class="fa fa-file-alt fs-2x text-info me-3"></i>
                            <div>
                                <h6 class="mb-1">File Berkas Berita Acara</h6>
                                <p class="mb-0 fs-7">Dokumen terlampir format {{ strtoupper($data->file->extension) }}. <a href="{{ asset($data->file->link_download) }}" target="_blank" class="fw-bold text-primary">Klik di sini untuk mengunduh</a>.</p>
                            </div>
                        </div>
                    @else
                        <object data="{{ asset($data->file->link_stream) }}" type="application/pdf" width="100%" height="550px">
                            <p>Browser Anda tidak mendukung preview PDF secara langsung. Silakan <a href="{{ asset($data->file->link_download) }}" class="fw-bold text-primary" target="_blank">unduh file di sini</a>.</p>
                        </object>
                    @endif
                @else
                    <div class="alert alert-warning text-center my-2 fs-7">
                        <i class="fa fa-exclamation-triangle text-warning me-1"></i> Belum ada dokumen file yang diunggah untuk berita acara ini.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .modal-lg {
            max-width: 1000px !important;
        }
    </style>
    <script>
        $('.submit-data').hide();
        $('.modal-title').html('<i class="fa fa-file-alt text-primary me-2"></i> Detail Berita Acara Audit');
    </script>
</div>
