{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("nama")->text("Nama Unit/Jurusan") !!}
			{!! html()->text("nama", isset($data) ? $data->nama : null)->placeholder("Ketik Nama di sini")->class("form-control")->id("nama") !!}
		</div>
        <div class="form-group">
            {!! html()->label()->class("control-label")->for("user_id")->text("Pilih Koordinator Unit/Jurusan") !!}
            {!! html()->select("user_id", $user_id, isset($data) ? $data->user_id : null)->placeholder("Pilih")->class("form-select")->attributes(['data-control' => 'select2', 'data-placeholder' => 'Select an option'])->id("user_id") !!}
        </div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("tipe")->text("Tipe") !!}
			{!! html()->select("tipe", $tipe, isset($data) ? config('master.content.unit.tipe.'.$data->tipe) : null)->placeholder("Pilih Tipe")->class("form-control")->id("tipe") !!}
		</div>
        <div id="prodi-section" class="form-group" style="display: none;">
            {!! html()->label()->class("control-label")->text("Program Studi (Prodi)") !!}
            
            <div id="prodi-inputs-container">
                {{-- Jika ini form EDIT dan tipenya JURUSAN, render prodi yang sudah ada --}}
                @if(isset($data) && $data->tipe === 'Jurusan' && $data->children->count() > 0)
                    @foreach($data->children as $index => $prodiItem)
                    <div class="row mt-2 prodi-input-row">
                        <div class="col-md-5">
                            <input type="text" name="prodi[{{ $index }}][nama]" class="form-control" placeholder="Nama Program Studi" value="{{ $prodiItem->nama }}" required>
                            {{-- Simpan ID prodi yang ada untuk proses update --}}
                            <input type="hidden" name="prodi[{{ $index }}][id]" value="{{ $prodiItem->id }}">
                        </div>
                        <div class="col-md-5">
                            <select name="prodi[{{ $index }}][user_id]" class="form-control" required>
                                <option value="">Pilih User</option>
                                @foreach($user_id as $id => $name)
                                    <option value="{{ $id }}" {{ $prodiItem->user_id == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-danger btn-block remove-prodi-button" type="button" title="Hapus Prodi">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            <button type="button" id="add-prodi-button" class="btn btn-sm btn-light-primary mt-2">
                <i class="fa fa-plus"></i> Tambah Prodi Lainnya
            </button>
        </div>
    </div>
</div>
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
    $('.modal-title').html('<i class="fa fa-plus-circle"></i> Tambah Data {!! $page->title !!}');
    $('.submit-data').html('<i class="fa fa-save"></i> Simpan Data');

    //tinymce
    $(document).ready(function() {
         $('[data-control="select2"]').select2();
        var options = {
            selector: ".tinymce", 
            height : "480",
            menubar: false,
            toolbar: ["styleselect fontselect fontsizeselect",
                "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
                "bullist numlist | outdent indent | blockquote subscript superscript | advlist | autolink | lists charmap | print preview |  code"],
            plugins : "advlist autolink link image lists charmap print preview code",
            setup: function(editor) {
                editor.on('init', function() {
                    $('form').on('submit', function() {
                        editor.save();
                    });
                });
            }
        };
        if ( KTThemeMode.getMode() === "dark" ) {
            options["skin"] = "oxide-dark";
            options["content_css"] = "dark";
        }
        tinymce.init(options);

        // prodi dan jurusan
        const allUsers = {!! json_encode($user_id ?? []) !!};
        let prodiIndex = {{ isset($data) && $data->children ? $data->children->count() : 0 }};

        // Fungsi untuk membuat dropdown user dari objek 'allUsers'
        function buildUserSelect() {
            let selectHtml = `<select name="prodi[${prodiIndex}][user_id]" class="form-select" data-control="select2" required>`;
            selectHtml += '<option value="" selected disabled>Pilih Koordinator Prodi</option>';

            // Loop melalui objek user dan buat setiap option
            for (const id in allUsers) {
                selectHtml += `<option value="${id}">${allUsers[id]}</option>`;
            }

            selectHtml += '</select>';
            return selectHtml;
        }

        // Fungsi untuk menambah baris input prodi baru (sekarang dengan user select)
        function addProdiInput() {
            const userSelectDropdown = buildUserSelect();

            // HTML untuk satu baris input, sekarang menggunakan grid system untuk layout
            const prodiInputHtml = `
                <div class="row mt-2 prodi-input-row">
                    <div class="col-md-5">

                        <input type="text" name="prodi[${prodiIndex}][nama]" class="form-control" placeholder="Nama Program Studi" required>
                    </div>
                    <div class="col-md-5">
                        ${userSelectDropdown}
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-danger btn-block remove-prodi-button" type="button">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#prodi-inputs-container').append(prodiInputHtml);
            prodiIndex++;
        }

        // Fungsi untuk menampilkan/menyembunyikan section prodi (tidak berubah banyak)
        function toggleProdiSection() {
            const selectedTipe = $('#tipe').val();

            if (selectedTipe === 'Jurusan') {
                $('#prodi-section').slideDown();
                if (!{{ isset($data) ? 'true' : 'false' }} && $('#prodi-inputs-container').children().length === 0) {
                    addProdiInput();
                }
            } else {
                $('#prodi-section').slideUp();
            }
            $('[data-control="select2"]').select2();

        }

        // --- EVENT LISTENERS ---

        $('#tipe').on('change', toggleProdiSection);
        $('#add-prodi-button').on('click', addProdiInput);

        // Event listener untuk tombol hapus
        $('#prodi-inputs-container').on('click', '.remove-prodi-button', function() {
            $(this).closest('.prodi-input-row').remove();
        });

        // --- INISIALISASI ---
        toggleProdiSection();
    });

    
    $('#modal-master').on('hidden.bs.modal', function () {
        tinymce.remove('.tinymce'); // Destroy all TinyMCE instances
    });
</script>