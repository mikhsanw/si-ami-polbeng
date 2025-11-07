{{ html()->form(isset($data) ? 'PUT' : 'POST', isset($data) ? route($page->code . '.update-indikator', $data->id) : route($page->code . '.store-indikator'))->id('form2-create-' . $page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
        @if ($parent ?? null)
            {{-- Jika ada parent, tampilkan nama kriteria --}}
            {!! html()->hidden('parent_id')->id('parent_id')->value($parent->id) !!}
            {!! html()->hidden('redirect', route($page->code . '.index') . '/' . $parent->lembaga_akreditasi_id) !!}

            <div class="form-group">
                {!! html()->label()->class('control-label')->for('parent_nama')->text('Kriteria') !!}
                {!! html()->text('parent_nama', null)->class('form-control')->id('parent_nama')->attributes(['readonly' => 'readonly'])->value($parent->nama) !!}
            </div>
        @endif

        <div class="form-group">
            {!! html()->label()->class('control-label')->for('nama')->text('Nama Indikator') !!}
            {!! html()->textarea('nama', isset($data) ? $data->nama : null)->placeholder('Ketik Nama di sini')->class('form-control')->id('nama') !!}
        </div>

        <div class="form-group">
            {!! html()->label()->class('control-label')->for('tipe')->text('Tipe Penilaian') !!}
            {!! html()->select(
                    'tipe',
                    config('master.content.kriteria.tipe'),
                    isset($data) ? config('master.content.kriteria.tipe.' . $data->tipe) : null,
                )->placeholder('Pilih Tipe Penilaian')->class('form-select')->id('tipe') !!}
        </div>
        {{-- Manual input: Rubrik Penilaian --}}
        <div class="form-group mt-3" id="manual_rubrik" style="display: none;">
            <hr>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 10%;">Skor</th>
                        <th style="width: 90%;">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $isLamemba = isset($parent) && $parent->lembagaAkreditasi->singkatan === 'LAMEMBA';
                        $labelLamemba = [
                            2 => 'Melampaui',
                            1 => 'Memenuhi Kriteria',
                            0 => 'Tidak Memenuhi Kriteria',
                        ];
                    @endphp

                    @for ($i = $isLamemba ? 2 : 4; $i >= 0; $i--)
                        @php
                            $label = $isLamemba ? $labelLamemba[$i] ?? '' : '';
                            $value = old(
                                "rubrik_manual_deskripsi.$i",
                                $existingRubrikDeskripsi[$i] ?? ($isLamemba ? $label : ''),
                            );
                        @endphp
                        <tr>
                            <td class="text-center fw-bold fs-5 align-middle">
                                {{ $i }}
                            </td>
                            <td>
                                <textarea name="rubrik_manual_deskripsi[{{ $i }}]" class="form-control" rows="2"
                                    placeholder="Deskripsi untuk skor {{ $i }}" {{ $isLamemba ? 'readonly' : '' }}>{{ $value }}</textarea>
                            </td>
                        </tr>
                    @endfor



                </tbody>
            </table>
        </div>

        <div id="otomatis_input">
            {{-- INPUT FIELD DEFINISI --}}
            <hr>
            <h5 class="mt-4">Definisi Input Field</h5>
            <p class="text-muted">Tentukan data apa saja yang perlu diisi oleh auditee untuk indikator ini. Variabel
                akan digunakan dalam formula di bawah.</p>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45%;">Label Input (Teks yang dilihat pengguna)</th>
                        <th style="width: 30%;">Nama Variabel (Untuk formula)</th>
                        <th style="width: 20%;">Tipe Data</th>
                        <th style="width: 5%;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="dynamicFieldsContainer">
                    {{-- Baris pertama sebagai contoh --}}
                    @forelse ($inputFields as $idx => $field)
                        <tr>
                            <td>
                                <input type="text" name="input_fields[{{ $idx }}][label]"
                                    value="{{ old("input_fields.$idx.label", $field['label']) }}"
                                    placeholder="Ketik Deskripsi Input di sini" class="form-control">
                            </td>
                            <td>
                                <input type="text" name="input_fields[{{ $idx }}][variable]"
                                    value="{{ old("input_fields.$idx.variable", $field['variable']) }}"
                                    placeholder="e.g., jumlah_dosen" class="form-control">
                            </td>
                            <td>
                                <select name="input_fields[{{ $idx }}][tipe_data]" class="form-select">
                                    <option value="ANGKA" {{ $field['tipe_data'] == 'ANGKA' ? 'selected' : '' }}>Angka
                                    </option>
                                    <option value="PERSENTASE"
                                        {{ $field['tipe_data'] == 'PERSENTASE' ? 'selected' : '' }}>Persentase</option>
                                    <option value="TEKS" {{ $field['tipe_data'] == 'TEKS' ? 'selected' : '' }}>Teks
                                    </option>
                                </select>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-secondary move-up"
                                        title="Pindah ke atas">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary move-down"
                                        title="Pindah ke bawah">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm remove-field" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td>
                                <input type="text" name="input_fields[0][label]"
                                    placeholder="e.g., Masukkan Jumlah Dosen" class="form-control">
                            </td>
                            <td>
                                <input type="text" name="input_fields[0][variable]" placeholder="e.g., jumlah_dosen"
                                    class="form-control">
                            </td>
                            <td>
                                <select name="input_fields[0][tipe_data]" class="form-select">
                                    <option value="ANGKA">Angka</option>
                                    <option value="PERSENTASE">Persentase</option>
                                    <option value="TEKS">Teks</option>
                                </select>
                            </td>
                            <td>
                                {{-- Tombol hapus tidak ada untuk baris pertama --}}
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
            <button type="button" class="btn btn-success btn-sm" id="addField">
                <i class="fas fa-plus me-1"></i> Tambah Input Field
            </button>

            {{-- FORMULA PENILAIAN --}}
            <hr>
            <h5 class="mt-4">Formula Penilaian Otomatis</h5>
            <p class="text-muted">Masukkan rumus untuk menghitung skor. Gunakan "Nama Variabel" yang Anda definisikan di
                atas.</p>
            <div class="form-group mb-4">
                {!! html()->label()->class('control-label')->for('formula_penilaian')->text('Rumus') !!}
                <small class="form-text text-muted">(Gunakan huruf, angka, _ , + - * / <> = ! && || ? : () [] dan
                        spasi.)</small>
                {!! html()->textarea('formula_penilaian', isset($data) ? $data->formula_penilaian : null)->placeholder('Contoh: (A/B)*100 atau (A >= 60) ? 4 : 2')->class('form-control')->id('formula_penilaian')->attributes(['rows' => 3]) !!} {{-- Menambahkan atribut rows --}}

                <button type="button" id="checkFormula" class="btn btn-info btn-sm mt-2">
                    <i class="fas fa-check"></i> Cek Formula
                </button>
                <div id="formulaResult" class="mt-2"></div>
            </div>

        </div>
    </div>
</div>
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
    $('.form-select').select2({
        minimumResultsForSearch: Infinity,
    });
    $('.modal-title').html('<i class="fa fa-plus-circle"></i> Tambah Data {!! $page->title !!}');
    $('.submit-data').html('<i class="fa fa-save"></i> Simpan Data');

    //tinymce
    $(document).ready(function() {
        $('.modal-title').html('<i class="fa fa-plus-circle"></i> Tambah Data {!! $page->title !!}');
        $('.submit-data').html('<i class="fa fa-save"></i> Simpan Data');

        // Tampilkan input sesuai tipe penilaian
        const tipePenilaian = document.getElementById('tipe');

        function toggleInputForm(value) {
            if (value === 'LED') {
                $('#manual_rubrik').show().find('textarea').prop('disabled', false);
                $('#otomatis_input').hide().find('textarea, input').prop('disabled', true);
            } else if (value === 'LKPS') {
                $('#manual_rubrik').hide().find('textarea').prop('disabled', true);
                $('#otomatis_input').show().find('textarea, input').prop('disabled', false);
            } else {
                $('#manual_rubrik').hide().find('textarea').prop('disabled', true);
                $('#otomatis_input').hide().find('textarea, input').prop('disabled', true);
            }
        }

        // Saat halaman load
        toggleInputForm($(tipePenilaian).val());

        // Saat select berubah
        $(tipePenilaian).on('change', function() {
            toggleInputForm($(this).val());
        });

        // Dynamic input fields
        let fieldIndex = $('#dynamicFieldsContainer tr').length;

        // Fungsi untuk menambah baris input field baru
        $('#addField').click(function() {
            let newRow = `
                <tr>
                    <td>
                        <input type="text" name="input_fields[${fieldIndex}][label]" placeholder="e.g., Masukkan Jumlah Dosen" class="form-control">
                    </td>
                    <td>
                        <input type="text" name="input_fields[${fieldIndex}][variable]" placeholder="e.g., jumlah_maba" class="form-control">
                    </td>
                    <td>
                    <select name="input_fields[${fieldIndex}][tipe_data]" class="form-select">
                        <option value="ANGKA">Angka</option>
                        <option value="PERSENTASE">Persentase</option>
                        <option value="TEKS">Teks</option>
                    </select>
                </td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-secondary move-up"
                                title="Pindah ke atas">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary move-down"
                                title="Pindah ke bawah">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm remove-field" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            $('#dynamicFieldsContainer').append(newRow);
            fieldIndex++;
        });

        // Fungsi untuk menghapus baris (menggunakan event delegation)
        $('#dynamicFieldsContainer').on('click', '.remove-field', function() {
            $(this).closest('tr').remove();
        });

        $('#checkFormula').click(function() {
            let formula = $('#formula_penilaian').val();
            let resultDiv = $('#formulaResult');
            resultDiv.html('<span class="text-muted">Memeriksa formula...</span>');

            // Ambil semua nama variabel dari tabel input_fields
            let variables = [];
            $('input[name^="input_fields"]').each(function() {
                let name = $(this).attr('name');
                if (name.includes('[variable]')) {
                    let val = $(this).val().trim();
                    if (val) variables.push(val);
                }
            });

            $.ajax({
                url: "{{ route($page->code . '.cek-formula') }}", // rute akan kita buat
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    formula: formula,
                    variables: variables // kirim array variabel ke backend
                },
                success: function(res) {
                    if (res.valid) {
                        resultDiv.html(
                            '<span class="text-success"><i class="fas fa-check-circle"></i> ' +
                            res.message + '</span>');
                    } else {
                        resultDiv.html(
                            '<span class="text-danger"><i class="fas fa-times-circle"></i> ' +
                            res.message + '</span>');
                    }
                },
                error: function(xhr) {
                    resultDiv.html(
                        '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan.</span>'
                    );
                }
            });
        });

        //sortable rows
        // Fungsi Pindah Naik
        $('#dynamicFieldsContainer').on('click', '.move-up', function() {
            let row = $(this).closest('tr');
            let prev = row.prev();
            if (prev.length) {
                row.insertBefore(prev);
                updateInputNames();
            }
        });

        // Fungsi Pindah Turun
        $('#dynamicFieldsContainer').on('click', '.move-down', function() {
            let row = $(this).closest('tr');
            let next = row.next();
            if (next.length) {
                row.insertAfter(next);
                updateInputNames();
            }
        });

        // Fungsi untuk memperbarui nama array input setelah urutan berubah
        function updateInputNames() {
            $('#dynamicFieldsContainer tr').each(function(index) {
                $(this).find('input, select, textarea').each(function() {
                    let name = $(this).attr('name');
                    if (name) {
                        name = name.replace(/\[\d+\]/, `[${index}]`);
                        $(this).attr('name', name);
                    }
                });
                // update nomor urut (jika ada kolom nomor)
                $(this).find('.order-number').text(index + 1);
            });
        }




        var options = {
            selector: ".tinymce",
            height: "480",
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
