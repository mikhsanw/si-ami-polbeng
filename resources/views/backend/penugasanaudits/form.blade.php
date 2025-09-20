{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
        {{-- BAGIAN 1: INFORMASI UMUM (TETAP SAMA) --}}
        <div class="mb-4 p-3 bg-body-tertiary rounded">
            <div class="row">
                <div class="col-md-6">
                    <strong>Unit:</strong> {{ $auditPeriode->unit->nama }}<br>
                    <strong>Periode:</strong> {{ $auditPeriode->tahun_akademik }}
                </div>
                <div class="col-md-6">
                    <strong>Standar:</strong> {{ $auditPeriode->instrumenTemplate->nama }}<br>
                    <strong>Lembaga:</strong> {{ $auditPeriode->instrumenTemplate->lembagaAkreditasi->singkatan }}
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: DETAIL INDIKATOR (TETAP SAMA) --}}
        <div class="mb-3">
            <label class="form-label fw-bold fs-5">Indikator Penilaian:</label>
            <p class="fs-6">{{ $data->nama }}</p>
        </div>
        <hr>

        {{-- =============================================== --}}
        {{-- BAGIAN 3: HASIL PENILAIAN (DIUBAH JADI VIEW) --}}
        {{-- =============================================== --}}
       
        @if($data->tipe === 'LKPS')
            {{-- TAMPILAN LIHAT UNTUK LKPS --}}
            <div>
                <h5 class="fw-bold mb-3">Data Kinerja (LKPS) yang Diinput:</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Deskripsi Data</th>
                            <th>Nilai yang Diinput</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data->indikatorInputs as $field)
                            <tr>
                                <td>{{ $field->label_input }}</td>
                                <td>
                                    <strong>{{ optional($data->hasilAuditForPeriode($auditPeriode->id)->dataAuditInput->where('indikator_input_id', $field->id)->first())->nilai_variable ?? '-' }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-muted text-center">Tidak ada data input yang didefinisikan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                 <div class="mt-3">
                    <h6 class="fw-bold">Skor Hasil Kalkulasi Sistem:</h6>
                    <p class="fs-4 text-primary fw-bold">{{ optional($data->hasilAuditForPeriode($auditPeriode->id))->skor_auditee ?? 'Belum terhitung' }}</p>
                 </div>
            </div>
        @endif

        {{-- TAMPILAN LIHAT UNTUK LED --}}
        <div>
            <h5 class="fw-bold mb-3">Hasil Evaluasi Diri (Skor Auditee):</h5>
            @forelse ($data->rubrikPenilaians->sortByDesc('skor')->whereNotNull('deskripsi') as $rubrik)
                <div class="form-check mb-3">
                    <input class="form-check-input" disabled type="radio" 
                        name="skor_auditee" 
                        value="{{ $rubrik->skor }}" 
                        id="skor_{{ $rubrik->skor }}"
                        {{ (int)optional($data->hasilAuditForPeriode($auditPeriode->id))->skor_auditee === (int)$rubrik->skor ? 'checked' : '' }}>

                    <label class="" for="skor_{{ $rubrik->skor }}">
                        <strong class="text-primary">Skor {{ $rubrik->skor }}:</strong> {{ $rubrik->deskripsi }}
                    </label>
                </div>
            @empty
                <p class="text-muted">Rubrik penilaian untuk indikator ini belum didefinisikan.</p>
            @endforelse
        </div>

        {{-- BAGIAN 4: DOKUMEN BUKTI (DIUBAH JADI VIEW) --}}
        <div class="mt-4">
            <h5 class="fw-bold mb-3">Dokumen Bukti Terlampir</h5>
            @if($data->hasilAuditForPeriode($auditPeriode->id)->files->isNotEmpty())
                <div class="list-group">
                    @foreach ($data->hasilAuditForPeriode($auditPeriode->id)->files as $key => $file)
                        <a href="{{ asset($file->link_public_stream) }}" class="list-group-item list-group-item-action text-primary d-flex align-items-center" target="_blank">
                            <i class="fas fa-file-alt fa-fw me-3 text-primary"></i>
                            Bukti Penilaian {{ $key + 1 }}
                            <i class="fas fa-external-link-alt ms-auto"></i>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-muted">Tidak ada dokumen bukti yang dilampirkan.</p>
            @endif
        </div>

        <div class="p-3 mt-4 border rounded bg-light">
            <h5 class="fw-bold mb-3">Panel Validasi Auditor</h5>

            {{-- Dropdown untuk memilih aksi --}}
            <div class="form-group mb-3">
                <label class="form-label fw-bold" for="auditor_action">Pilih Aksi:</label>
                <select name="action" id="auditor_action" class="form-control">
                    <option value="">-- Pilih Tindakan --</option>
                    <option value="finalisasi">Finalisasi Skor</option>
                    <option value="minta_revisi">Minta Revisi</option>
                </select>
            </div>

            {{-- Area dinamis yang akan muncul/hilang --}}
            <div id="action_form_container">
                <div id="form_finalisasi" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="form-label" for="skor_final">Skor Final:</label>
                        <select name="skor_final" id="skor_final" class="form-select w-50">
                            <option value="">Pilih Skor</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>

                <div id="form_revisi" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="form-label" for="catatan_auditor">Catatan & Rekomendasi untuk Revisi:</label>
                        <textarea name="catatan_auditor" class="form-control" rows="4" placeholder="Jelaskan apa yang perlu diperbaiki oleh auditee..."></textarea>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
{!! html()->hidden('indikator_id')->id('indikator_id')->value($data->id) !!}
{!! html()->hidden('audit_periode_id')->id('audit_periode_id')->value($auditPeriode->id) !!}

{!! html()->hidden('table-id','datatable')->id('table-id') !!}
{{--{!! html()->hidden('function','loadMenu,sidebarMenu')->id('function') !!}--}}
{{--{!! html()->hidden('redirect',url('/dashboard'))->id('redirect') !!}--}}
{!! html()->form()->close() !!}
<style>
    .select2-container {
        z-index: 9999 !important;
        width: 100% !important;
    }

    .modal-lg {
        max-width: 1000px !important;
    }

    .control-label {
        font-weight: 500 !important;
        font-size: 1.15rem !important;
        margin: .5rem !important;
    }
</style>

<script>
    $('.form-select').select2();
    $('.submit-data').html('<i class="fa fa-save"></i> Simpan Keputusan');

    //tinymce
    $(document).ready(function() {
        const actionSelect = $('#auditor_action');
        const formFinalisasi = $('#form_finalisasi');
        const formRevisi = $('#form_revisi');

        actionSelect.on('change', function() {
            const selectedAction = $(this).val();

            // Sembunyikan semua form dan tombol simpan terlebih dahulu
            formFinalisasi.hide();
            formRevisi.hide();

            if (selectedAction === 'finalisasi') {
                // Jika memilih "Finalisasi Skor", tampilkan form skor
                formFinalisasi.slideDown();
                formRevisi.hide();
            } else if (selectedAction === 'minta_revisi') {
                // Jika memilih "Minta Revisi", tampilkan form catatan
                formRevisi.slideDown();
            }
        });
    });
</script>