<div class="panel">
    <div class="panel-body">
        <div class="mb-4 p-3 bg-body-tertiary rounded border">
            <h5 class="fw-bold border-bottom pb-2 mb-3">Informasi Umum Indikator</h5>

            {{-- Menampilkan Nama Indikator --}}
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Nama Indikator</div>
                <div class="col-md-8 fw-medium">{{ $data->indikator->nama ?? 'Tidak tersedia' }}</div>
            </div>

            {{-- Menampilkan Tipe Penilaian --}}
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Tipe Penilaian</div>
                <div class="col-md-8">
                    @if ($data->indikator->tipe === 'LED')
                        <span class="badge bg-primary">{{ $data->indikator->tipe }}</span>
                    @else
                        <span class="badge bg-success">{{ $data->indikator->tipe }}</span>
                    @endif
                </div>
            </div>

            {{-- Menampilkan Kriteria Induk --}}
            <div class="row">
                <div class="col-md-4 text-muted">Kriteria Induk</div>
                <div class="col-md-8 fw-medium">
                    {{-- Lebih baik menampilkan nama kriteria daripada hanya ID-nya --}}
                    {{ $data->indikator->kriteria->nama ?? 'Tidak terhubung' }}
                </div>
            </div>
        </div>

        {{-- =============================================== --}}
        {{-- BAGIAN BARU: MENAMPILKAN HASIL AUDIT --}}
        {{-- =============================================== --}}
        <div class="mb-4 p-3 rounded border">
            <h5 class="fw-bold border-bottom pb-2 mb-3">Hasil Audit</h5>

            @if ($data)
                {{-- Menampilkan Status Terkini --}}
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Status</div>
                    <div class="col-md-8">
                        @if ($data->status_terkini === 'Selesai')
                            <span class="badge badge-light-success">Selesai</span>
                        @elseif ($data->status_terkini === 'Revisi')
                            <span class="badge badge-light-danger">Revisi Diperlukan</span>
                        @elseif ($data->status_terkini === 'Diajukan')
                            <span class="badge badge-light-warning">Menunggu Validasi</span>
                        @else
                            <span class="badge badge-light">Belum Dikerjakan</span>
                        @endif
                    </div>
                </div>

                {{-- Menampilkan Skor Auditee --}}
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Skor Auditee</div>
                    <div class="col-md-8 fw-bold fs-5 text-primary">{{ $data->skor_auditee ?? '-' }}</div>
                </div>

                {{-- Menampilkan Skor Final Auditor --}}
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Skor Final Auditor</div>
                    <div class="col-md-8 fw-bold fs-5 text-success">{{ $data->skor_final ?? 'Belum divalidasi' }}</div>
                </div>

                {{-- Menampilkan Temuan --}}
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Temuan</div>
                    <div class="col-md-8">
                        <p class="fst-italic mb-0">"{{ $data->catatan_final ?? 'Tidak ada catatan.' }}"</p>
                    </div>
                </div>

                {{-- Menampilkan Bukti Terlampir --}}
                <div class="row">
                    <div class="col-md-4 text-muted">Bukti Terlampir</div>
                    <div class="col-md-8">
                        @if ($data->files->isNotEmpty())
                            <div class="list-group list-group-flush">
                                @foreach ($data->files as $key => $file)
                                    {{-- Sesuaikan URL jika link_public_stream tidak langsung dapat diakses --}}
                                    <a href="{{ asset($file->link_public_stream ?? '#') }}"
                                        class="list-group-item list-group-item-action px-0 py-1" target="_blank">
                                        <i class="fas fa-file-alt fa-fw me-2 text-primary"></i>
                                        {{ $file->nama_file ?? 'File Bukti ' . ($key + 1) }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="mb-0 text-muted">Tidak ada bukti yang dilampirkan.</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-secondary text-center">
                    Indikator ini belum dikerjakan untuk periode audit ini.
                </div>
            @endif
        </div>

        {{-- =============================================== --}}
        {{-- BAGIAN BARU: LOG AKTIVITAS AUDIT --}}
        {{-- =============================================== --}}
        @if ($data && $data->logAktivitasAudit->isNotEmpty())
            <div class="mb-4 p-3 rounded border">
                <h5 class="fw-bold border-bottom pb-2 mb-3">Riwayat Aktivitas Audit</h5>
                <div class="timeline timeline-border-dashed">
                    @foreach ($data->logAktivitasAudit->sortByDesc('created_at') as $log)
                        <div class="timeline-item">
                            <div class="timeline-label fw-bold text-gray-800 fs-8">
                                {{ $log->created_at ? $helper->formatDate($log->created_at) : 'Tanggal tidak tersedia' }}
                            </div>
                            <div class="timeline-badge">
                                @if ($log->tipe_aksi === config('master.content.log_aktivitas_audit.tipe_aksi.SUBMIT_AWAL'))
                                    <i class="fa fa-dot-circle text-primary fs-3"></i>
                                @elseif ($log->tipe_aksi === config('master.content.log_aktivitas_audit.tipe_aksi.VALIDASI'))
                                    <i class="fa fa-dot-circle text-info fs-3"></i>
                                @elseif ($log->tipe_aksi === config('master.content.log_aktivitas_audit.tipe_aksi.FINALISASI_SKOR'))
                                    <i class="fa fa-dot-circle text-success fs-3"></i>
                                @elseif ($log->tipe_aksi === config('master.content.log_aktivitas_audit.tipe_aksi.MINTA_REVISI'))
                                    <i class="fa fa-dot-circle text-warning fs-3"></i>
                                @elseif ($log->tipe_aksi === config('master.content.log_aktivitas_audit.tipe_aksi.SUBMIT_REVISI'))
                                    <i class="fa fa-dot-circle text-warning fs-3"></i>
                                @else
                                    <i class="fa fa-dot-circle text-secondary fs-3"></i>
                                @endif
                            </div>
                            <div class="timeline-content d-flex justify-content-between align-items-start flex-wrap">
                                <div class="pe-3">
                                    <div class="fs-6 fw-semibold mb-1">
                                        {{ $log->catatan_aksi }}
                                    </div>
                                    <div class="text-muted fs-7">
                                        Oleh: {{ $log->user->name ?? 'Sistem' }}
                                        <span
                                            class="ms-1 text-gray-500">({{ $log->user->getRoleNames()->first() ?? 'Tidak diketahui' }})</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            @if ($data)
                {{-- Hanya tampilkan ini jika data HasilAudit ada tapi belum ada log --}}
                <div class="mb-4 p-3 rounded border">
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Riwayat Aktivitas Audit</h5>
                    <div class="alert alert-info text-center">
                        Belum ada riwayat aktivitas untuk indikator ini.
                    </div>
                </div>
            @endif
        @endif
        {{-- =============================================== --}}
        {{-- AKHIR BAGIAN LOG AKTIVITAS AUDIT --}}
        {{-- =============================================== --}}


    </div>
</div>
<style>
    .modal-lg {
        max-width: 1000px !important;
    }

    /* Gaya untuk timeline */
    .timeline {
        position: relative;
        padding-left: 20px;
        /* Ruang untuk garis vertikal */
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-badge {
        position: absolute;
        left: -18px;
        /* Sesuaikan posisi ikon agar di garis */
        top: 0;
        z-index: 1;
        background-color: #fff;
        /* Agar ikon tidak tertutup garis */
        border-radius: 50%;
        padding: 2px;
    }

    .timeline-badge .fa {
        display: block;
        /* Pastikan ikon terlihat */
    }

    .timeline-label {
        margin-left: 10px;
    }

    .timeline-content {
        margin-left: 10px;
        /* Jarak antara garis dan konten */
        padding-bottom: 15px;
        border-bottom: 1px dashed #e4e6ef;
        /* Garis bawah untuk setiap item */
    }

    .timeline-item:last-child .timeline-content {
        border-bottom: none;
        /* Hapus garis bawah item terakhir */
    }

    .timeline-border-dashed::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 2px;
        /* Lebar garis vertikal */
        background-color: #e4e6ef;
        /* Warna garis */
        border-left: 1px dashed #ced4da;
        /* Garis putus-putus */
    }
</style>
<script>
    $('.submit-data').hide();
    $('.modal-title').html('<i class="fa fa-search"></i> Detail Data {!! $page->title !!}');
</script>
