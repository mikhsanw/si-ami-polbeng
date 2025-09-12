{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update-rancangan',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <h5>Pustaka Kriteria</h5>
                <hr>
                <div class="tree-view">
                    <ul class="list-group">
                        @forelse ($kriterias as $kriteria)
                            @include($backend.'.'.$page->code.'._kriteria_item', [
                                'item' => $kriteria, 
                            ])
                        @empty
                            <p class="text-muted">Belum ada data kriteria.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="col-md-4 bg-light p-4 rounded instrument-template">
                <h5>Template: "Instrumen Akreditasi Prodi Teknik"</h5>
                <hr>
                @if($data->templateKriterias->isNotEmpty())
                @foreach($data->templateKriterias->whereNotNull('bobot') as $templateKriteria)
                    <div class="mb-3 input-bobot" id="bobot-{{ $templateKriteria->kriteria_id }}">
                        <label class="form-label">{{ $templateKriteria->kriteria->kode.' '.$templateKriteria->kriteria->nama }}</label>
                        <input type="number" name="bobot[{{ $templateKriteria->kriteria_id }}]" class="form-control" placeholder="Masukkan bobot..." value="{{ $helper->formatNumber($templateKriteria->bobot) }}">
                    </div>
                @endforeach
                @endif
                <div class="mb-3">
                   <p class="text-muted text-center" id="empty-bobot-msg">Belum ada kriteria yang dipilih.</p>
                </div>
                <div class="mt-3">
                    <span class="text-muted">* jika Bobot <strong>tidak diisi</strong> maka akan <strong>dianggap 0</strong>.</span>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-muted">* Silakan pilih kriteria di sebelah kiri untuk menambahkan ke template.</span>
            </div>
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

    .summary-card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .tree-view .list-group-item {
        border: none;
        cursor: pointer;
    }
    .tree-view .child {
        padding-left: 25px;
    }
    .list-group-item
    {
        padding: 5px 0px;
        margin-top: 5px
    }
    .list-group-item .form-check {
        margin-right: 0px;
        padding-left: 0px;
    }
</style>

<script>
    $('.modal-title').html('<i class="fa fa-plus-circle"></i> Tambah Data {!! $page->title !!}');
    $('.submit-data').html('<i class="fa fa-save"></i> Simpan Data');
    
    
    $(document).ready(function() {
        const container = $(".instrument-template");
        toggleEmptyMessage();
        // Fungsi untuk menambahkan input
        function addInput(id, label) {
            // Cek apakah sudah ada
            if ($('#bobot-' + id).length) return;

            const html = `
                <div class="mb-3 input-bobot" id="bobot-${id}">
                    <label class="form-label">${label}</label>
                    <input type="number" name="bobot[${id}]" class="form-control" placeholder="Masukkan bobot...">
                </div>`;
            container.append(html);
            toggleEmptyMessage();
        }

        // Fungsi untuk menghapus input
        function removeInput(id) {
            $('#bobot-' + id).remove();
            toggleEmptyMessage();
        }

        // Event listener untuk checkbox
        $(document).on('change', '.kriteria-checkbox', function () {
            const id = $(this).data('id');
            const label = $(this).data('label');

            if ($(this).is(':checked')) {
                addInput(id, label);
            } else {
                removeInput(id);
            }
        });

        function toggleEmptyMessage() {
            if ($('.input-bobot').length === 0) {
                $('#empty-bobot-msg').show();
            } else {
                $('#empty-bobot-msg').hide();
            }
        }


        // Inisialisasi select2
        $('.form-select').select2({
            minimumResultsForSearch: Infinity,
            placeholder: "Pilih Lembaga Akreditasi"
        });
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
    });
    $('#modal-master').on('hidden.bs.modal', function () {
        tinymce.remove('.tinymce'); // Destroy all TinyMCE instances
    });


</script>