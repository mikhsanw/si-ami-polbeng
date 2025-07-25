{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("nama")->text("Nama") !!}
			{!! html()->text("nama", isset($data) ? $data->nama : null)->placeholder("Type Nama here")->class("form-control")->id("nama") !!}
		</div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("singkatan")->text("Singkatan") !!}
			{!! html()->text("singkatan", isset($data) ? $data->singkatan : null)->placeholder("Type Singkatan here")->class("form-control")->id("singkatan") !!}
		</div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("kategori")->text("Kategori") !!}
			{!! html()->select("kategori", $kategori,isset($data) ? config('master.content.lembagaakreditasi.kategori.'.$data->kategori) : null)->placeholder("Pilih Kategori")->class("form-control")->id("kategori") !!}
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
        $('.select2').select2();
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