{!! html()->modelForm($data,'PUT', route($page->code.'.update', $data->id))->id('form-create-'.$page->code)->acceptsFiles()->class('form form-horizontal')->open() !!}
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! html()->label('Nama Menu','title')->class('control-label') !!}
            <span class="text-danger">*</span>
            {!! html()->text('title',$data->title)->placeholder('Type name here')->class('form-control')->id('title') !!}
        </div>
        <div class="form-group">
            {!! html()->label('Informasi','subtitle')->class('control-label') !!}
            {!! html()->span('e.g : Welcome to Menu page')->class('text-danger') !!}
            {!! html()->text('subtitle',$data->subtitle)->placeholder('Type subtitle here')->class('form-control')->id('subtitle') !!}
        </div>
        <div class="form-group">
            {!! html()->label('Model','model')->class('control-label') !!}
            {!! html()->select('model',$model,collect(explode('\\', $data->model))->last())->placeholder('Pilih Model (optional)')->class('form-control select2')->id('model') !!}
        </div>
        <div class="form-group">
            {!! html()->label('Kode Menu','code')->class('control-label') !!}
            <span class="text-danger">*</span>
            {!! html()->text('code',$data->code)->placeholder('Type code here')->class('form-control')->id('code') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! html()->label('Icon','icon')->class('control-label') !!}
            <span class="text-danger">*</span>
            <div class="preview-grup">
                <div class="icon-preview" data-toggle="tooltip" title="Preview of selected Icon">
                    <i id="IconPreview" class="{{$data->icon}}"></i>
                </div>

                <button type="button" id="GetIconPicker" class="btn btn-secondary btn-sm btn-icon-preview" data-iconpicker-input="input#IconInput" data-iconpicker-preview="i#IconPreview">Select Icon</button>
            </div>

            <div class="export">
                <!-- A "Hidden" or "Text"(display:none; etc.) type of Input Element to set a "Font Awesome icon classname" and post with Form -->
                <input type="hidden" id="IconInput" name="icon" value="{{$data->icon}}" placeholder="Hidden etc. input for icon classname" autocomplete="off" spellcheck="false" />
            </div>
        </div>
        <div class="form-group">
            {!! html()->label('Tipe Menu','type')->class('control-label') !!}
            <span class="text-danger">*</span>
            {!! html()->select('type',['' => 'Pilih Tipe Menu', 'backend' => 'Backend', 'frontend' => 'Frontend'],$data->type)->class('form-select')->id('type') !!}
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! html()->label('Tampilkan','show')->class('control-label') !!}
                    <span class="text-danger">*</span>
                    {!! html()->select('show',[1 => 'Ya', 0 => 'Tidak'],$data->show)->class('form-select')->id('show') !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! html()->label('Status','active')->class('control-label') !!}
                    <span class="text-danger">*</span>
                    {!! html()->select('active',[1 => 'Aktif', 0 => 'Tidak Aktif'],$data->active)->class('form-select')->id('active') !!}
                </div>
            </div>
        </div>
        <div class="form-group">
            {!! html()->label('Link','url')->class('control-label') !!}
            <span class="text-danger">*</span>
            {!! html()->text('url',$data->url)->placeholder('Type url here')->class('form-control')->id('url') !!}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            {!! html()->label('Tentukan Akses','role_id')->class('control-label') !!}
            <span class="text-danger">*</span>
            <div class="row p-5" id="role_id">
                @foreach($role as $key => $value)
                    <div class="col-12">
                        @php 
                        $role = \Spatie\Permission\Models\Role::find($key);
                        $userRole = $role->hasAnyPermission($data->code.' list',$data->code.' create',$data->code.' edit',$data->code.' delete'); 
                        @endphp
                        {!! html()->checkbox('role_id[]', $userRole?$key:'', $key)->id('access_group_'.$key)->class('filled-in chk-col-primary role_id')->attributes(['onclick'=>"checkAllLevel('access-menu-crud-$key',this)"]) !!}
                        {!! html()->label($value,'access_group_'.$key) !!}
                        <div class="form-control access-menu-crud-{{$key}} m-2">
                            <label class="control-label">Tentukan Hak Akses</label> <span class="text-danger">*</span>
                            <a href="javascript:void(0)" type="button" onclick="checkAll('access-crud-{{$key}}',{{$key}})" class="check-all-{{$key}} btn btn-xs btn-success"><i class="fa fa-check"></i> Check All</a>
                            <div class="row mt-2">
                                @foreach(config('master.app.level') as $i => $v)
                                    <div class="col-md-2 access-crud-{{$key}}">
                                        {!! html()->checkbox('access_crud_'.$key.'[]', ($role->hasAnyPermission($data->code.' '.$v) ? $v : ''), $v)->id('crud_'.$i.'_'.$key)->class('filled-in chk-col-info') !!}
                                        {!! html()->label(ucwords($v),'crud_'.$i.'_'.$key) !!}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="message"></div>
        <div class="progress" style="display: none;">
            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div id="statustxt">0%</div>
            </div>
        </div>
    </div>
</div>
{!! html()->hidden('function')->value('loadMenu,sidebarMenu')->id('function') !!}
{!! html()->form()->close() !!}
<style>
    .select2-container {
        z-index: 9999 !important;
        width: 100% !important;
    }

    .modal-lg {
        max-width: 1000px !important;
    }

    .show-all {
        max-height: calc(100vh - 210px);
        overflow-y: auto;
    }
    .preview-grup{
        width: 100%;
        margin: 0 0 10px;
        float: left;
        clear: both;
    }
    .icon-preview{
        float: left;
        width: 55px;
        height: 55px;
        margin: 0 15px 0 0;
        border-radius: 5px;
        background: #fff;
        text-align: center;
        line-height: 55px;
        color: #1e1e1e;

        i{
            font-size: 30px;
        }
    }
    
</style>
<link rel="stylesheet" href="{{url('assets/vendor/iconpicker/dist/fontawesome-5.11.2/css/all.min.css')}}" />
<link rel="stylesheet" href="{{url('assets/vendor/iconpicker/dist/iconpicker-1.5.0.css')}}" />
<script src="{{url('assets/vendor/iconpicker/dist/iconpicker-1.5.0.js')}}"></script>
<script src="{{ url('/assets/vendor/nestable-/jquery.nestable.js') }}"></script>
<script>
    $(document).ready(function () {
        // Default options
        IconPicker.Init({
            // Required: You have to set the path of IconPicker JSON file to "jsonUrl" option. e.g. '/content/plugins/IconPicker/dist/iconpicker-1.5.0.json'
            jsonUrl: '{{ url("assets/vendor/iconpicker/dist/iconpicker-1.5.0.json") }}',
            // Optional: Change the buttons or search placeholder text according to the language.
            searchPlaceholder: 'Search Icon',
            showAllButton: 'Show All',
            cancelButton: 'Cancel',
            noResultsFound: 'No results found.', // v1.5.0 and the next versions
            borderRadius: '20px', // v1.5.0 and the next versions
        });

        IconPicker.Run('#GetIconPicker');
    });
    $('.modal-title').html('<i class="mdi mdi-bookmark-plus"></i> Tambah Data {!! $page->title !!}');
    $('.select2').attr('data-control','select2');;

    

    $('.role_id').on('click', function () {
        let count = $('.role_id:checked').length;
        if (count > 0) {
            $('.access-menu-crud-' + $(this).val()).show();
        } else {
            $('.access-crud' + $(this).val()).find('input[type="checkbox"]').prop('checked', false);
            $('.access-menu-level' + $(this).val()).hide();
        }
    })

    function checkAll(param, key) {
        let div = $('.' + param),
            checked = div.find('input[type="checkbox"]:checked').length,
            total = div.find('input[type="checkbox"]').length;
        div.find('input[type="checkbox"]').prop('checked', checked !== total)
        $('.check-all-' + key).html(checked !== total ? '<i class="fa fa-check"></i> Uncheck All' : '<i class="fa fa-check"></i> Check All')
    }

    function checkAllLevel(param, obj) {
        $('.' + param).find('input[type="checkbox"]').prop('checked', $(obj).prop('checked'))
    }
</script>
