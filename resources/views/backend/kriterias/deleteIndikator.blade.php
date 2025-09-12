{!! html()->form('DELETE', route($page->code.'.destroy-indikator', $data->id))->id('form-create-'.$page->code)->class('form form-horizontal')->open() !!}
<div class="row">
    <div class="col-md-12">
        <label class="control-label h6">Apakah Anda Yakin Ingin Menghapus Data Ini?</label>
        <div class="info-data">
            <div class="panel">
                <div class="panel-body panel-dark">
                    <div class="json-view">
                        @foreach(collect(json_decode($data, true))->except(['id','created_at','updated_at','deleted_at']) as $key => $value)
                            <div class="json-row">
                                <div class="json-key">{{ $key }}</div>
                                <div class="json-separator">:</div>
                                <div class="json-value">
                                    @if (is_array($value))
                                        <pre class="json-array">{!! json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                    @else
                                        {!! nl2br(e($value)) !!}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <span class="message"></span>
        </div>
    </div>
</div>
{!! html()->hidden('table-id','datatable')->id('table-id') !!}
{!! html()->form()->close() !!}
<style>
    .json-view {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .json-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: baseline;
        padding: 8px 12px;
        border: 1px solid #e1e1e1;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    .json-key {
        font-weight: bold;
        color: #444;
        background-color: #f1f1f1;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
    .json-separator {
        color: #dc3545; /* red */
    }
    .json-value {
        color: #0d6efd; /* blue */
        word-break: break-word;
        flex: 1;
    }
    pre.json-array {
        margin: 0;
        font-size: 0.9em;
        font-family: monospace;
        white-space: pre-wrap;
        background-color: #f8f8f8;
        padding: 6px;
        border-radius: 4px;
    }
</style>
<script>
    $('.modal-title').html('<i class="mdi mdi-delete-forever"></i> Hapus Data {!! $page->title !!}');
    $('.submit-data').html('<i class="fa fa-trash"></i> Hapus Data');
</script>
