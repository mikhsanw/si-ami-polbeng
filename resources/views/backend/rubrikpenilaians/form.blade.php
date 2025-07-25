{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("skor")->text("Skor") !!}
			{!! html()->text("skor", isset($data) ? $data->skor : null)->placeholder("Type Skor here")->class("form-control")->id("skor") !!}
		</div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("deskripsi")->text("Deskripsi") !!}
			{!! html()->textarea("deskripsi", isset($data) ? $data->deskripsi : null)->placeholder("Type Deskripsi here")->class("form-control")->id("deskripsi") !!}
		</div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("formula_kondisi")->text("Formula_kondisi") !!}
			{!! html()->textarea("formula_kondisi", isset($data) ? $data->formula_kondisi : null)->placeholder("Type Formula_kondisi here")->class("form-control")->id("formula_kondisi") !!}
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