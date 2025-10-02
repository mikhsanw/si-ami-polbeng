{{ html()->form(isset($data) ? 'PUT' : 'POST', isset($data) ? route($page->code . '.update-rancangan', $data->id) : route($page->code . '.store'))->id('form-create-' . $page->code)->acceptsFiles()->class('form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="mb-3">Pustaka Indikator untuk Template: "{{ $data->nama ?? 'Instrumen Baru' }}"</h5>
                <p class="text-muted">Lembaga Akreditasi:
                    <strong>{{ $data->LembagaAkreditasi->nama ?? 'Belum ditentukan' }}</strong>
                </p>
                <hr>

                <div class="form-check form-switch  mb-2">
                    <label class="form-check-label ms-2" for="toggle-expand-all">
                        Tampilkan Semua
                    </label>
                    <input class="form-check-input" type="checkbox" id="toggle-expand-all">
                </div>

                {{-- Pastikan template ($data) dan relasi lembaga sudah dimuat --}}
                @if (isset($data) && $data->lembaga_akreditasi_id)
                    {{-- Tree View Kriteria dan Indikator --}}
                    {{-- Partial ini menerima parameter: $kriterias (koleksi kriteria), $selectedIndikatorIds (array ID indikator yang sudah terpilih), $backend, $page --}}
                    {{-- Kita akan memuat tree view ini via AJAX agar lebih dinamis --}}
                    <div id="indikator-tree-view" class="tree-view">
                        {{-- Konten ini akan diisi via AJAX setelah halaman dimuat --}}
                        <p class="text-muted text-center"><i class="fas fa-spinner fa-spin me-2"></i>Memuat indikator...
                        </p>
                    </div>
                @else
                    <p class="text-danger text-center">Template belum memiliki Lembaga Akreditasi. Harap tentukan
                        lembaga terlebih dahulu.</p>
                @endif
            </div>

            {{-- Kolom Kanan: Indikator Terpilih dan Bobot --}}
            <div class="col-md-4 bg-light p-4 rounded instrument-template">
                <h5 class="mb-3">Indikator Terpilih & Bobot</h5>
                <hr>
                <div id="selected-indikators-bobot">
                    {{-- Ini akan diisi oleh JS, termasuk saat halaman dimuat jika ada data --}}
                    @if (isset($data) && $data->templateIndikators->isNotEmpty())
                        @foreach ($data->templateIndikators as $templateIndikator)
                            <div class="mb-3 input-bobot" id="bobot-{{ $templateIndikator->indikator_id }}">
                                <label class="form-label fw-medium">{{ $templateIndikator->indikator->nama }}</label>
                                <input type="number" step="0.01"
                                    name="bobot[{{ $templateIndikator->indikator_id }}]" class="form-control"
                                    placeholder="Contoh: 1,02 (gunakan koma)"
                                    value="{{ $templateIndikator->bobot ?? '' }}">
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="mb-3">
                    <p class="text-muted text-center" id="empty-bobot-msg">Belum ada indikator yang dipilih.</p>
                </div>
                <div class="mt-3">
                    <span class="text-muted">* Jika Bobot <strong>tidak diisi</strong> maka akan <strong>dianggap
                            0</strong>.</span>
                </div>
            </div>
        </div>
    </div>
</div>
{!! html()->hidden('table-id', 'datatable')->id('table-id') !!}
{{-- Hidden input untuk menyimpan semua indikator yang dipilih, termasuk yang belum punya bobot --}}
{{ html()->hidden('selected_indikators', '')->id('selected-indikators-input') }}
{{ html()->form()->close() }}

<style>
    /* Styling Anda */
    .tree-view .list-group-item {
        border: none;
        cursor: pointer;
    }

    .tree-view .child {
        padding-left: 25px;
    }

    .list-group-item {
        padding: 5px 0px;
        margin-top: 5px
    }

    .list-group-item .form-check {
        margin-right: 0px;
        padding-left: 0px;
    }

    .list-group-item i {
        width: 20px;
        text-align: center;
    }

    /* Contoh: bikin modal-xl jadi 95% lebar layar */
    #modal-master .modal-dialog.modal-xl {
        max-width: 95% !important;
        width: 95% !important;
    }


    /* Memastikan ikon sejajar */
</style>

<script>
    $(document).ready(function() {
        $('#modal-master .modal-dialog')
            .removeClass('modal-sm modal-lg')
            .addClass('modal-xl');

        // ---- Fungsi Spesifik untuk Form Rancangan Template ----
        const selectedIndikatorsContainer = $('#selected-indikators-bobot');
        const emptyBobotMsg = $('#empty-bobot-msg');
        const selectedIndikatorsInput = $('#selected-indikators-input');
        let selectedIndikatorIds = new Set(); // Menggunakan Set untuk menyimpan ID indikator yang dipilih

        // Fungsi untuk mengupdate hidden input `selected_indikators`
        function updateSelectedIndikatorsInput() {
            selectedIndikatorsInput.val(JSON.stringify(Array.from(selectedIndikatorIds)));
        }

        // Fungsi untuk toggle pesan "Belum ada indikator yang dipilih"
        function toggleEmptyMessage() {
            if (selectedIndikatorsContainer.children('.input-bobot').length === 0) {
                emptyBobotMsg.show();
            } else {
                emptyBobotMsg.hide();
            }
        }

        // Fungsi untuk menambahkan input bobot indikator
        function addIndikatorBobotInput(id, label, initialValue = '') {
            if ($(`#bobot-${id}`).length) return;

            const html = `
                <div class="mb-3 input-bobot" id="bobot-${id}">
                    <label class="form-label fw-medium">${label}</label>
                    <input type="number" step="0.01" name="bobot[${id}]" class="form-control" placeholder="Contoh: 1,02 (gunakan koma)" value="${initialValue}">
                </div>`;
            selectedIndikatorsContainer.append(html);
            selectedIndikatorIds.add(id);
            toggleEmptyMessage();
            updateSelectedIndikatorsInput();
        }

        // Fungsi untuk menghapus input bobot indikator
        function removeIndikatorBobotInput(id) {
            $(`#bobot-${id}`).remove();
            selectedIndikatorIds.delete(id);
            toggleEmptyMessage();
            updateSelectedIndikatorsInput();
        }

        // Event listener untuk checkbox indikator
        $(document).on('change', '.indikator-checkbox', function() {
            const indikatorId = $(this).val();
            const indikatorLabel = $(this).closest('.form-check').find('.form-check-label').text()
                .trim();

            if ($(this).is(':checked')) {
                addIndikatorBobotInput(indikatorId, indikatorLabel);
            } else {
                removeIndikatorBobotInput(indikatorId);
            }
        });

        // Inisialisasi tree view dan selected indikator saat halaman dimuat (mode buat/edit)
        const currentLembagaId = "{{ $data->lembaga_akreditasi_id ?? 'null' }}";
        const currentTemplateId = "{{ $data->id ?? 'null' }}";

        if (currentLembagaId !== 'null') {
            // Lakukan AJAX call untuk mendapatkan tree view kriteria dan indikator
            $.ajax({
                url: '{{ route($page->code . '.get-indikator-tree') }}', // Ganti dengan route yang sesuai
                method: 'GET',
                data: {
                    lembaga_id: currentLembagaId,
                    template_id: currentTemplateId // Kirim ID template untuk menandai yang sudah terpilih
                },
                success: function(response) {
                    $('#indikator-tree-view').html(response);
                    // Setelah tree view dimuat, inisialisasi status checkbox dan Set ID
                    // Ini penting agar input bobot di kolom kanan terisi dari data yang sudah ada
                    $('.indikator-checkbox:checked').each(function() {
                        const id = $(this).val();
                        // Ambil label dari elemen yang sudah ada di kolom kanan jika ada
                        let label = $(this).closest('.form-check').find('.form-check-label')
                            .text().trim();
                        let initialBobot = $(`#bobot-${id} input`).val() ||
                            ''; // Ambil bobot jika sudah ada

                        addIndikatorBobotInput(id, label, initialBobot);
                    });
                    toggleEmptyMessage();
                },
                error: function() {
                    $('#indikator-tree-view').html(
                        '<p class="text-danger text-center">Gagal memuat indikator. Silakan coba lagi.</p>'
                    );
                }
            });

            // Inisialisasi selectedIndikatorIds dari data yang sudah ada di kolom kanan saat edit
            @if (isset($data) && $data->templateIndikators->isNotEmpty())
                @foreach ($data->templateIndikators as $ti)
                    selectedIndikatorIds.add('{{ $ti->indikator_id }}');
                @endforeach
            @endif
        } else {
            $('#indikator-tree-view').html(
                '<p class="text-danger text-center">Template belum memiliki Lembaga Akreditasi. Harap tentukan lembaga terlebih dahulu.</p>'
            );
        }

        updateSelectedIndikatorsInput(); // Pastikan hidden input terisi di awal
        toggleEmptyMessage(); // Pastikan pesan kosong muncul/sembunyi dengan benar


        // ---- Fungsi Umum & TinyMCE ----
        $('.modal-title').html('<i class="fa fa-plus-circle"></i> Tambah Data {!! $page->title !!}');
        $('.submit-data').html('<i class="fa fa-save"></i> Simpan Data');



        // TinyMCE (jika masih diperlukan, biasanya untuk deskripsi template)
        // var options = { /* ... TinyMCE options ... */ };
        // tinymce.init(options);
        // $('#modal-master').on('hidden.bs.modal', function () { tinymce.remove('.tinymce'); });
    });
    $(document).off('click', '.toggle-child').on('click', '.toggle-child', function() {
        let target = $(this).data('target');
        let $child = $('#' + target);

        let icon = $(this);
        if ($child.hasClass('d-none')) {
            $child.removeClass('d-none');
            icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        } else {
            $child.addClass('d-none');
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        }
    });

    // Event listener untuk toggle expand/collapse semua
    $(document).on('change', '#toggle-expand-all', function() {
        if ($(this).is(':checked')) {
            // Expand semua
            $('.child').removeClass('d-none');
            $('.toggle-child')
                .removeClass('fa-chevron-right')
                .addClass('fa-chevron-down');
        } else {
            // Collapse semua
            $('.child').addClass('d-none');
            $('.toggle-child')
                .removeClass('fa-chevron-down')
                .addClass('fa-chevron-right');
        }
    });
</script>
