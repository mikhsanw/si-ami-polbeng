<div class="panel">
    <div class="panel-body">
        <div class="mb-4 p-3 bg-body-tertiary rounded border">
            <h5 class="fw-bold border-bottom pb-2 mb-3">Informasi Audit</h5>

            <div class="row mb-2">
                <div class="col-md-4 fw-bold">Periode</div>
                <div class="col-md-8 fw-medium">{{ $data->tahun_akademik ?? '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 fw-bold">Nama Unit</div>
                <div class="col-md-8 fw-medium">{{ $data->unit->nama ?? '-' }}</div>
            </div>
            <div class="row mb-5">
                <div class="col-md-4 fw-bold">Auditor</div>
                <div class="col-md-8 fw-medium">
                    @if ($data->penugasanAuditors->isNotEmpty())
                        {{ $data->penugasanAuditors->map(function ($pa) {
                                return $pa->user->name ?? '-';
                            })->join(', ') }}
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>

        {{-- =============================================== --}}
        {{-- BAGIAN BARU: MENAMPILKAN HASIL AUDIT --}}
        {{-- =============================================== --}}
        <div class="mb-4 p-3 rounded border">
            <h5 class="fw-bold border-bottom pb-2 mb-3">Hasil Audit</h5>

            @if ($data->hasilAudits->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="px-3">No</th>
                                <th scope="col">Indikator</th>
                                <th scope="col">Status Terkini</th>
                                <th scope="col">Skor</th>
                                <th scope="col" style="text-align: center">Temuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data->hasilAudits as $index => $hasilAudit)
                                <tr>
                                    <td class="px-3">{{ $index + 1 }}</td>
                                    <td>{{ $hasilAudit->indikator->nama ?? '-' }}</td>
                                    <td>{{ $hasilAudit->status_terkini ?? '-' }}</td>
                                    <td>{{ $hasilAudit->skor_final ?? '-' }}</td>
                                    <td>{{ $hasilAudit->catatan_final ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
        </div>
    @else
        <p class="text-muted">Tidak ada hasil audit yang tersedia.</p>
        @endif

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
