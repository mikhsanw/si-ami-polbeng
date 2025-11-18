{{ html()->form(isset($data) ? 'PUT' : 'POST', isset($data) ? route($page->code . '.update', $data->id) : route($page->code . '.store'))->id('form-create-' . $page->code)->acceptsFiles()->class('form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
        <div class="form-group">
            {!! html()->label()->class('control-label')->for('tahun_akademik')->text('Tahun Akademik') !!}
            {!! html()->select('tahun_akademik', $tahun_akademik, isset($data) ? $data->tahun_akademik : null)->placeholder('Pilih Tahun Akademik')->class('form-control')->id('tahun_akademik') !!}
        </div>
        <div class="form-group">
            {!! html()->label()->class('control-label')->for('unit_id')->text('Unit') !!}
            {!! html()->select('unit_id', $unit_id, isset($data) ? $data->unit_id : null)->placeholder('Pilih Unit')->class('form-select')->id('unit_id') !!}
        </div>
        <div class="form-group">
            {!! html()->label()->class('control-label')->for('instrumen_template_id')->text('Instrumen Template') !!}
            {!! html()->select('instrumen_template_id', $instrumen_template_id, isset($data) ? $data->instrumen_template_id : null)->placeholder('Pilih Instrumen')->class('form-select')->id('instrumen_template_id') !!}
        </div>
        <div class="form-group row pt-3">
            <div class="col-3">
                {!! html()->label('Status')->for('status')->class('control-label') !!}
            </div>

            <div class="col-9">
                <div class="form-check form-switch form-check-custom form-check-solid">
                    {{-- Hidden input agar checkbox unchecked tetap kirim value --}}
                    <input type="hidden" name="status" value="0">

                    {{-- Checkbox --}}
                    {!! html()->checkbox('status', $data->status ?? true)->class('form-check-input')->id('status') !!}

                    {{-- Label --}}
                    {!! html()->label('Aktif')->for('status')->class('form-check-label fw-semibold text-gray-500 ms-3') !!}
                </div>
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

    .form {
        z-index: 9999 !important;
    }

    .modal-lg {
        max-width: 500px !important;
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
