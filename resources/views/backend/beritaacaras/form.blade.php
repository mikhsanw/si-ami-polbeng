{{ html()->form(isset($data) ? 'PUT' : 'POST', isset($data) ? route($page->code . '.update', $data->id) : route($page->code . '.store'))->id('form-create-' . $page->code)->acceptsFiles()->class('form form form-horizontal')->open() }}
<div class="panel">
    <div class="panel-body">
        <div class="form-group">
            {!! html()->label()->class('control-label')->for('audit_periode_id')->text('Audit Periode') !!}
            {!! html()->select('audit_periode_id', $audit_periode_id, isset($data) ? $data->audit_periode_id : null)->placeholder('Pilih Periode Unit')->class('form-select')->id('audit_periode_id') !!}
        </div>
        <div class="form-group">
            {!! html()->label()->class('control-label')->for('file')->text('Upload File Berita Acara') !!}
            {!! html()->file('file')->class('form-control')->id('file') !!}
            @if (isset($data) && $data->file)
                <p>Current File: <a href="{{ asset($data->file->link_stream) }}" target="_blank"> <i
                            class="fa fa-file"></i> Lihat Dokumen</a></p>
            @endif
        </div>
        <div class="form-group">
            {!! html()->label()->class('control-label')->for('catatan')->text('Catatan') !!}
            {!! html()->textarea('catatan', isset($data) ? $data->catatan : null)->placeholder('Type Catatan here')->class('form-control')->id('catatan') !!}
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
    $('.form-select').select2();
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
