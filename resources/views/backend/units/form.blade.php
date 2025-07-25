{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
        {!! html()->hidden('parent_id')->id('parent_id') !!}

		<div class="form-group">
			{!! html()->label()->class("control-label")->for("nama")->text("Nama Unit/Jurusan") !!}
			{!! html()->text("nama", isset($data) ? $data->nama : null)->placeholder("Ketik Nama di sini")->class("form-control")->id("nama") !!}
		</div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("tipe")->text("Tipe") !!}
			{!! html()->select("tipe", $tipe, isset($data) ? config('master.content.unit.tipe.'.$data->tipe) : null)->placeholder("Pilih Tipe")->class("form-control")->id("tipe") !!}
		</div>
		<div class="form-group">
			{!! html()->label()->class("control-label")->for("user_id")->text("Koordinator Unit/Jurusan") !!}
			{!! html()->select("user_id", $user_id, isset($data) ? $data->user_id : null)->placeholder("Pilih Koordinator")->class("form-select")->attributes(['data-control' => 'select2', 'data-placeholder' => 'Select an option'])->id("user_id") !!}
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
        const parentId = $('.action-create').data('parent-id') || '';
        if(parentId){
            $('#parent_id').val(parentId);
            $('label[for="nama"]').text('Nama Program Studi');
            $('label[for="user_id"]').text('Koordinator Program Studi');
            $('#tipe').val('Program Studi').trigger('change');
            $('#tipe').prop('disabled', true);

            $('#form-create-{{$page->code}}').on('submit', function() {
            // Aktifkan kembali field 'tipe' agar nilainya ikut terkirim
            $('#tipe').prop('disabled', false);
        });

        }

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