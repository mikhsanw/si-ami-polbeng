{{ html()->form('DELETE',
    url(config('master.app.url.backend') . '/performaauditors/penilaian/' . $penilaian->id)
)->id('form-delete-penilaian')->open() }}

<div class="p-4">
    <div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
        <i class="ki-duotone ki-information-5 fs-2x text-warning">
            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
        </i>
        <div>Anda yakin ingin menghapus penilaian ini? Tindakan ini tidak dapat dibatalkan.</div>
    </div>

    <table class="table table-sm">
        <tr>
            <td class="fw-semibold w-150px">Auditor</td>
            <td>{{ $penilaian->auditor->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Periode Audit</td>
            <td>{{ optional($penilaian->auditPeriode->unit)->nama ?? '?' }} — {{ $penilaian->auditPeriode->tahun_akademik ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Skor Keseluruhan</td>
            <td>
                <span class="badge badge-light-{{ $penilaian->warna_skor }}">
                    {{ $penilaian->skor_keseluruhan }}% — {{ $penilaian->label_skor }}
                </span>
            </td>
        </tr>
    </table>
</div>

{{ html()->form()->close() }}

<script>
    $('.modal-title').html('<i class="ki-duotone ki-trash fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Hapus Penilaian');
    $('.submit-data').addClass('btn-danger').removeClass('btn-info').html('<i class="ki-duotone ki-trash fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Ya, Hapus');
</script>
