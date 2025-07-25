{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("tahun_akademik")->text("Tahun_akademik") !!}
			{!! html()->text("tahun_akademik", isset($data) ? $data->tahun_akademik : null)->placeholder("Type Tahun_akademik here")->class("form-control")->id("tahun_akademik") !!}
		</div>
		<div class="form-group">
            {!! html()->label()->class("control-label")->for("unit_id")->text("Unit") !!}
			{!! html()->select("unit_id", $unit_id, isset($data) ? $data->unit_id : null)->placeholder("Pilih Unit")->class("form-select")->id("unit_id") !!}
		</div>
		<div class="form-group">
            {!! html()->label()->class("control-label")->for("instrument_template_id")->text("Instrumen Template") !!}
			{!! html()->select("instrumen_template_id", $instrument_template_id, isset($data) ? $data->instrument_template_id : null)->placeholder("Pilih Instrumen")->class("form-select")->id("instrumen_template_id") !!}
		</div>
        <div class="form-group row">
            <div class="col-xl-3">
                {!! html()->label()->class("control-label")->for("is_active")->text("Status") !!}
            </div>
            
            <div class="col-xl-9">
                <div class="form-check form-switch form-check-custom form-check-solid">
                    {!! html()->checkbox("is_active", isset($data) ? $data->is_active : true)->class("form-check-input")->id("is_active") !!}
                    {!! html()->label()->class("form-check-label fw-semibold text-gray-500 ms-3")->for("is_active")->text("Aktif") !!}
                </div>
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

    .form{
        z-index: 9999 !important;
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