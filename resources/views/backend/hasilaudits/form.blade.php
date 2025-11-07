{{ html()->form(isset($data) ? 'PUT' : 'POST', isset($data) ? route($page->code . '.update', $data->id) : route($page->code . '.store'))->id('form-create-' . $page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
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
        @if (optional($data->hasilAuditForPeriode($auditPeriode->id))->status_terkini === 'Revisi')
            <div class="alert alert-danger d-flex align-items-center p-4 mb-4">
                <i class="fas fa-exclamation-triangle fa-2x me-4"></i>
                <div class="d-flex flex-column">
                    <h5 class="mb-1">Perlu Revisi</h5>
                    <span>Auditor memberikan catatan berikut untuk perbaikan:</span>
                    <span
                        class="mt-2 fst-italic">"{{ optional($data->hasilAuditForPeriode($auditPeriode->id))->logAktivitasAudit()->latest()->first()->catatan_aksi ?? '-' }}"</span>
                </div>
            </div>
        @endif
        <div class="mb-3">
            <label class="form-label fw-bold fs-5">Indikator Penilaian:</label>
            <p class="fs-6">{{ $data->nama }}</p>
        </div>
        @if ($data->tipe === 'LED')
            {{-- TAMPILAN UNTUK LED (LED) --}}
            <div id="form-led">
                <label class="form-label fw-bold fs-5">Pilih Tingkat Capaian (Skor):</label>
                @forelse ($data->rubrikPenilaians->sortByDesc('skor')->whereNotNull('deskripsi') as $rubrik)
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="skor_auditee" value="{{ $rubrik->skor }}"
                            id="skor_{{ $rubrik->skor }}"
                            {{ (int) optional($data->hasilAuditForPeriode($auditPeriode->id))->skor_auditee === (int) $rubrik->skor ? 'checked' : '' }}>

                        <label class="form-check-label" for="skor_{{ $rubrik->skor }}">
                            <strong class="text-primary">Skor {{ $rubrik->skor }}:</strong> {{ $rubrik->deskripsi }}
                        </label>
                    </div>
                @empty
                    <p class="text-muted">Rubrik penilaian untuk indikator ini belum didefinisikan.</p>
                @endforelse

                {{-- <div class="form-group mt-4">
                    <label class="form-label fw-bold fs-5" for="deskripsi_pemenuhan">Deskripsi Pemenuhan / Analisis Diri</label>
                    <textarea class="form-control tinymce" name="deskripsi_pemenuhan" id="deskripsi_pemenuhan" rows="5"></textarea>
                </div> --}}
            </div>
        @elseif($data->tipe === 'LKPS')
            {{-- TAMPILAN UNTUK LKPS (OTOMATIS) --}}
            <div id="form-lkps">
                <label class="form-label fw-bold fs-5">Input Data Kinerja (LKPS):</label>
                @forelse ($data->indikatorInputs->sortBy('urutan') as $field)
                    <div class="col-4 mb-3">
                        <label for="field_{{ $field->id }}">{{ $field->label_input }} dalam
                            {{ $field->tipe_data }}</label>
                        <input type="text" class="form-control pb-2" name="lkps_data[{{ $field->id }}]"
                            id="field_{{ $field->id }}"
                            value="{{ $data->hasilAuditForPeriode($auditPeriode->id)?->dataAuditInputForInput($field->id)?->nilai_variable ?? '' }}"
                            required>
                        <small class="form-text text-muted">Variabel: <code>{{ $field->nama_variable }}</code></small>
                    </div>
                @empty
                    <p class="text-muted">Tidak ada input data kinerja yang tersedia.</p>
                @endforelse
                {{-- <div class="form-group mt-4">
                    <label class="form-label fw-bold fs-5" for="analisis_tambahan">Analisis / Catatan Tambahan</label>
                    <textarea class="form-control" name="analisis_tambahan" id="analisis_tambahan" rows="3"></textarea>
                </div> --}}
            </div>

        @endif

        <div class="mt-4">
            <label class="form-label fw-bold fs-5">Unggah Dokumen Bukti</label>
            @if ($data->hasilAuditForPeriodes($auditPeriode->id)->flatMap->files->isNotEmpty())
                <div class="mb-3 border p-3">
                    <small class="form-text">Dokumen pendukung yang telah dilampirkan sebelumnya</small>
                    <div id="existingFilesList">
                        @foreach ($data->hasilAuditForPeriodes($auditPeriode->id)->flatMap->files as $key => $file)
                            <div class="list-group-item list-group-item-action d-flex align-items-center mb-2"
                                id="file-{{ $file->id }}"> {{-- Tambahkan ID unik per file --}}
                                <a href="{{ asset($file->link_public_stream) }}"
                                    class="text-primary d-flex align-items-center flex-grow-1" target="_blank">
                                    <i class="fas fa-file-alt fa-fw me-3 text-primary"></i>
                                    {{ basename($file->alias) . '.' . $file->extension }}
                                    <i class="fas fa-external-link-alt ms-auto"></i>
                                </a>
                                {{-- Tombol Hapus --}}
                                @if (in_array(optional($data->hasilAuditForPeriode($auditPeriode->id))->status_terkini, ['Revisi', 'Draft']))
                                    <button type="button" class="btn btn-sm btn-danger ms-3 delete-existing-file-btn"
                                        data-file-id="{{ $file->id }}"
                                        data-file-name="{{ basename($file->alias) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div id="uploadContainer">
                {{-- Baris upload file pertama --}}
                <div class="input-group mb-3 upload-row">
                    <input class="form-control" type="file" name="upload_file[]">
                    {{-- Tombol hapus tidak ada untuk baris pertama --}}
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" id="add-file-btn">
                <i class="fas fa-plus me-2"></i>Tambah File
            </button>
        </div>
    </div>
</div>
{!! html()->hidden('indikator_id')->id('indikator_id')->value($data->id) !!}
{!! html()->hidden('audit_periode_id')->id('audit_periode_id')->value($auditPeriode->id) !!}

{!! html()->hidden('table-id', 'datatable')->id('table-id') !!}
{{-- {!! html()->hidden('function','loadMenu,sidebarMenu')->id('function') !!} --}}
{{-- {!! html()->hidden('redirect',url('/dashboard'))->id('redirect') !!} --}}
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
    $('.submit-data').html('<i class="fa fa-save"></i> Simpan Data');

    //tinymce
    $(document).ready(function() {
        // Fungsi untuk menambah baris upload file
        $('#add-file-btn').click(function() {
            let newRow = `
                <div class="input-group mb-3 upload-row">
                    <input class="form-control" type="file" name="upload_file[]">
                    <button class="btn btn-outline-danger btn-remove-file" type="button">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            $('#uploadContainer').append(newRow);
        });

        $('#existingFilesList').on('click', '.delete-existing-file-btn', function() {
            var fileId = $(this).data('file-id');
            var fileName = $(this).data('file-name');
            var $fileElement = $(`#file-${fileId}`); // Elemen file yang akan dihapus

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan menghapus dokumen bukti " + fileName +
                    ". Aksi ini tidak bisa dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim AJAX request untuk menghapus file
                    $.ajax({
                        url: "{{ route('hasilaudits.deleteFile') }}", // Sesuaikan dengan route Anda
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            file_id: fileId,
                            // Jika diperlukan, bisa tambahkan hasil_audit_id atau audit_periode_id
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Dihapus!',
                                    response.message,
                                    'success'
                                );
                                // Hapus elemen file dari DOM
                                $fileElement.next('small')
                                    .remove(); // Hapus <small>
                                $fileElement.remove(); // Hapus <a> list group item

                                // Jika tidak ada file tersisa, mungkin sembunyikan container mb-3 border p-3
                                if ($('#existingFilesList').children().length ===
                                    0) {
                                    $('#existingFilesList').closest(
                                        '.mb-3.border.p-3').remove();
                                }

                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan saat menghapus file.',
                                'error'
                            );
                            console.error("AJAX error: ", xhr.responseText);
                        }
                    });
                }
            });
        });

        // Fungsi untuk menghapus baris upload file
        $('#uploadContainer').on('click', '.btn-remove-file', function() {
            $(this).closest('.upload-row').remove();
        });

        // Membersihkan TinyMCE saat modal ditutup (jika form ini ada di dalam modal)
        $('body').on('hidden.bs.modal', '.modal', function() {
            if (tinymce.get('deskripsi_pemenuhan')) {
                tinymce.get('deskripsi_pemenuhan').setContent('');
            }
        });

        // Inisialisasi TinyMCE
        var options = {
            selector: ".tinymce",
            height: "300",
            menubar: false,
            toolbar: ["styleselect fontselect fontsizeselect",
                "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
                "bullist numlist | outdent indent | blockquote subscript superscript | advlist | autolink | lists charmap | print preview |  code"
            ],
            plugins: "advlist autolink link image lists charmap print preview code",
            setup: function(editor) {
                editor.on('init', function() {
                    $('form').on('submit', function() {
                        editor.save();
                    });
                });
            }
        };
        if (KTThemeMode.getMode() === "dark") {
            options["skin"] = "oxide-dark";
            options["content_css"] = "dark";
        }
        tinymce.init(options);
    });
    $('#modal-master').on('hidden.bs.modal', function() {
        tinymce.remove('.tinymce'); // Destroy all TinyMCE instances
    });
</script>
