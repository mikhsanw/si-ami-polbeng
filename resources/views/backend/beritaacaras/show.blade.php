<div class="panel">
    <div class="panel-body">
        <div class="row">
            <!-- file view -->
            <div class="col-md-12">
                <div class="form-group">
                    @if ($data->file)
                        @if ($data->file->extension != 'pdf')
                            <p>File yang diunggah: <a href="{{ asset($data->file->link_download) }}" target="_blank"> <i
                                        class="fa fa-file"></i> Unduh Dokumen</a></p>
                        @else
                            <object data="{{ asset($data->file->link_stream) }}" type="application/pdf" width="100%"
                                height="650px">
                                <!-- Fallback content for browsers that can't display the object -->
                                <p>Your browser does not support embedded PDFs. You can <a
                                        href="{{ asset($data->file->link_download) }}">download the file here</a>.</p>
                            </object>
                        @endif
                    @else
                        <p>Tidak ada file yang diunggah.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <style>
        .modal-lg {
            max-width: 1000px !important;
        }
    </style>
    <script>
        $('.submit-data').hide();
        $('.modal-title').html('<i class="fa fa-search"></i> Detail Data {!! $page->title !!}');
    </script>
