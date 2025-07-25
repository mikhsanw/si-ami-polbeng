{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
		<div class="form-group row mb-8">
            <div class="col-xl-3">
			    {!! html()->label()->class("control-label")->for("nama")->text("Nama") !!}
            </div>
            
            <div class="col-xl-9">
			    {!! html()->text("nama", isset($data) ? $data->nama : null)->placeholder("Type Nama here")->class("form-control")->id("nama") !!}
		</div>
		</div>
		<div class="form-group row mb-8">
            <div class="col-xl-3">
			    {!! html()->label()->class("control-label")->for("deskripsi")->text("Deskripsi") !!}
            </div>
            
            <div class="col-xl-9">
			    {!! html()->text("deskripsi", isset($data) ? $data->deskripsi : null)->placeholder("Type Deskripsi here")->class("form-control")->id("deskripsi") !!}
		    </div>
		</div>
		<div class="form-group row mb-8">
            <div class="col-xl-3">
                {!! html()->label()->class("control-label")->for("lembaga_akreditasi_id")->text("Lembaga Akreditasi") !!}
            </div>
            
            <div class="col-xl-9">
			    {!! html()->select("lembaga_akreditasi_id", $lembaga_akreditasi_id, isset($data) ? $data->lembaga_akreditasi_id : null)->placeholder('')->class("form-select")->id("lembaga_akreditasi_id") !!}
            </div>
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