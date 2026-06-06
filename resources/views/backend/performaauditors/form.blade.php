@php $isEdit = isset($penilaian); @endphp

{{ html()->form($isEdit ? 'PUT' : 'POST',
    url(config('master.app.url.backend') . '/performaauditors/penilaian' . ($isEdit ? '/' . $penilaian->id : ''))
)->id('form-penilaian')->class('form')->open() }}

<div class="p-4">

    {{-- Auditor --}}
    <div class="mb-4">
        <label class="form-label required fw-semibold">Auditor</label>
        @php $selectedAuditor = $isEdit ? $penilaian->auditor_id : (request()->query('auditor_id') ?? old('auditor_id')); @endphp
        <select name="auditor_id" id="auditor_id"
                class="form-select form-select-solid"
                data-control="select2" required>
            <option value="">— Pilih Auditor —</option>
            @foreach($auditors as $a)
                <option value="{{ $a->id }}" {{ $selectedAuditor == $a->id ? 'selected' : '' }}>
                    {{ $a->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Periode Audit --}}
    <div class="mb-4">
        <label class="form-label required fw-semibold">Periode Audit</label>
        @php $selectedPeriode = $isEdit ? $penilaian->audit_periode_id : old('audit_periode_id'); @endphp
        <select name="audit_periode_id" id="audit_periode_id"
                class="form-select form-select-solid" required>
            <option value="">— Pilih Periode —</option>
            @foreach($periodes as $p)
                <option value="{{ $p->id }}" {{ $selectedPeriode == $p->id ? 'selected' : '' }}>
                    {{ $p->periode_unit }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Preview metrik otomatis --}}
    <div id="metrik-preview" @unless($isEdit) style="display:none" @endunless class="mb-4">
        <div class="separator separator-dashed mb-3"></div>
        <div class="text-muted fw-semibold fs-7 text-uppercase mb-3">
            Metrik Performa Berdasarkan Data
        </div>

        @php
            $prev = $isEdit ? [
                'pct_responsivitas' => $penilaian->pct_responsivitas,
                'avg_hari_respon'   => $penilaian->avg_hari_respon,
                'pct_kecepatan'     => $penilaian->pct_kecepatan,
                'pct_catatan'       => $penilaian->pct_catatan,
                'skor_keseluruhan'  => $penilaian->skor_keseluruhan,
            ] : ['pct_responsivitas'=>0,'avg_hari_respon'=>null,'pct_kecepatan'=>0,'pct_catatan'=>0,'skor_keseluruhan'=>0];
        @endphp

        <div class="row g-3">
            <div class="col-6">
                <div class="fs-7 text-muted mb-1">% Responsivitas</div>
                <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height:8px">
                        <div id="bar-resp" class="progress-bar bg-primary rounded" style="width:{{ $prev['pct_responsivitas'] }}%"></div>
                    </div>
                    <span id="lbl-resp" class="fw-bold fs-7 w-40px text-end">{{ $prev['pct_responsivitas'] }}%</span>
                </div>
            </div>
            <div class="col-6">
                <div class="fs-7 text-muted mb-1">% Kecepatan</div>
                <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height:8px">
                        <div id="bar-speed" class="progress-bar bg-success rounded" style="width:{{ $prev['pct_kecepatan'] }}%"></div>
                    </div>
                    <span id="lbl-speed" class="fw-bold fs-7 w-40px text-end">{{ $prev['pct_kecepatan'] }}%</span>
                </div>
                <div id="lbl-hari" class="text-muted fs-8 mt-1">
                    {{ $prev['avg_hari_respon'] !== null ? 'Rata-rata '.$prev['avg_hari_respon'].' hari' : '' }}
                </div>
            </div>
            <div class="col-6">
                <div class="fs-7 text-muted mb-1">% Catatan</div>
                <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height:8px">
                        <div id="bar-cat" class="progress-bar bg-info rounded" style="width:{{ $prev['pct_catatan'] }}%"></div>
                    </div>
                    <span id="lbl-cat" class="fw-bold fs-7 w-40px text-end">{{ $prev['pct_catatan'] }}%</span>
                </div>
            </div>
            <div class="col-6">
                <div class="fs-7 text-muted mb-1">Skor Keseluruhan</div>
                <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height:8px">
                        <div id="bar-total" class="progress-bar rounded" style="width:{{ $prev['skor_keseluruhan'] }}%"></div>
                    </div>
                    <span id="lbl-total" class="fw-bolder fs-6 w-40px text-end">{{ $prev['skor_keseluruhan'] }}%</span>
                </div>
                <div id="lbl-label" class="text-muted fs-8 mt-1">{{ $isEdit ? $penilaian->label_skor : '' }}</div>
            </div>
        </div>
    </div>

    {{-- Catatan pimpinan --}}
    <div class="mb-1">
        <label class="form-label fw-semibold">Catatan Pimpinan <span class="text-muted fs-7">(opsional)</span></label>
        {{ html()->textarea('catatan', $isEdit ? $penilaian->catatan : old('catatan'))
            ->class('form-control form-control-solid')
            ->rows(3)
            ->placeholder('Tambahkan catatan evaluasi...') }}
    </div>

</div>

{{ html()->form()->close() }}

<script>
(function () {
    // Customise modal header & submit button dari loadModal
    $('.modal-title').html('<i class="ki-duotone ki-chart-line-up-2 fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> {{ $isEdit ? "Edit Penilaian" : "Rekam Penilaian" }}');
    $('.submit-data').html('<i class="ki-duotone ki-check fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> {{ $isEdit ? "Simpan Perubahan" : "Simpan Penilaian" }}');

    // Select2
    $('[data-control="select2"]').select2({ dropdownParent: $('.modal:visible').last() });

    @unless($isEdit)
    var previewUrl = "{{ route('performaauditors.preview') }}";

    function loadPreview() {
        var auditorId = $('#auditor_id').val();
        var periodeId = $('#audit_periode_id').val();
        if (!auditorId || !periodeId) { $('#metrik-preview').hide(); return; }

        $.ajax({
            url: previewUrl,
            method: 'POST',
            data: {
                auditor_id: auditorId,
                audit_periode_id: periodeId,
                _token: $('meta[name=csrf-token]').attr('content')
            },
            success: function (d) {
                $('#bar-resp').css('width', d.pct_responsivitas + '%');
                $('#lbl-resp').text(d.pct_responsivitas + '%');
                $('#bar-speed').css('width', d.pct_kecepatan + '%');
                $('#lbl-speed').text(d.pct_kecepatan + '%');
                $('#lbl-hari').text(d.avg_hari_respon !== null ? 'Rata-rata ' + d.avg_hari_respon + ' hari' : '');
                $('#bar-cat').css('width', d.pct_catatan + '%');
                $('#lbl-cat').text(d.pct_catatan + '%');

                var color = d.skor_keseluruhan >= 80 ? '#50cd89'
                          : d.skor_keseluruhan >= 60 ? '#009ef7'
                          : d.skor_keseluruhan >= 40 ? '#ffc700' : '#f1416c';
                var label = d.skor_keseluruhan >= 80 ? 'Sangat Baik'
                          : d.skor_keseluruhan >= 60 ? 'Baik'
                          : d.skor_keseluruhan >= 40 ? 'Cukup' : 'Perlu Perhatian';

                $('#bar-total').css({ width: d.skor_keseluruhan + '%', background: color });
                $('#lbl-total').text(d.skor_keseluruhan + '%');
                $('#lbl-label').text(label);
                $('#metrik-preview').show();
            }
        });
    }

    $('#auditor_id, #audit_periode_id').on('change', loadPreview);

    // Auto-load preview jika auditor sudah pre-selected (dari URL param)
    if ($('#auditor_id').val()) {
        // Trigger bila periode dipilih
        $('#audit_periode_id').on('change', loadPreview);
    }
    @endunless
})();
</script>
