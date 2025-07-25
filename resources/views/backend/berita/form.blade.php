{{ html()->form(isset($data)?'PUT':'POST', (isset($data) ? route($page->code.'.update',$data->id) : route($page->code.'.store')))->id('form-create-'.$page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
    <div class='form-group'>
			{!! html()->label()->class('control-label')->for('judul')->text('Judul') !!}
			{!! html()->text('judul',isset($data) ? $data->judul : null)->placeholder('Type Judul here')->class('form-control')->id('judul') !!}
		</div>
		<div class='form-group'>
			{!! html()->label()->class('control-label')->for('isi')->text('Isi') !!}
			{!! html()->textarea('isi',isset($data) ? $data?->isi : null)->class('form-control tinymce')->id('isi') !!}
		</div>
		<div class='form-group'>
			{!! html()->label()->class('control-label')->for('tanggal')->text('Tanggal') !!}
			{!! html()->dateTime('tanggal',isset($data) ? $data?->tanggal : null)->class('form-control')->id('tanggal') !!}
		</div>
		<div class='form-group'>
            {!! html()->label()->class('control-label')->for('gambar')->text('Gambar') !!}
			{!! html()->file('gambar')->class('form-control')->id('gambar')->accept('image/*') !!}
            @if(isset($data) && $data->file)
			<img src="{{asset($data->file?->link_stream)}}" class="w-25 pt-2" alt="Gambar belum tersedia">
            @endif
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