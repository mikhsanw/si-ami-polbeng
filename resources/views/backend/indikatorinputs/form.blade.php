{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("nama_variable")->text("Nama_variable") !!}
			{!! html()->text("nama_variable", isset($data) ? $data->nama_variable : null)->placeholder("Type Nama_variable here")->class("form-control")->id("nama_variable") !!}
		</div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("label_input")->text("Label_input") !!}
			{!! html()->text("label_input", isset($data) ? $data->label_input : null)->placeholder("Type Label_input here")->class("form-control")->id("label_input") !!}
		</div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("tipe_data")->text("Tipe_data") !!}
			{!! html()->text("tipe_data", isset($data) ? $data->tipe_data : null)->placeholder("Type Tipe_data here")->class("form-control")->id("tipe_data") !!}
		</div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("indikator_id")->text("Indikator") !!}
			{!! html()->select("indikator_id", $indikator_id, isset($data) ? $data->indikator_id : null)->placeholder("Pilih")->class("form-control select2")->id("indikator_id") !!}
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
    $('.select2').select2();
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