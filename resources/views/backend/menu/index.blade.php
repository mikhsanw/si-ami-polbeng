<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div id="kt_datatable_example_1_export" class="d-none"></div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
            @can('create '.$page->code)
            <a href="#" class="btn btn-sm btn-primary btn-action" data-title="Tambah" data-action="create" data-url="{{ config('master.app.url.backend').'/'.$page->url }}">
            <i class="ki-duotone ki-plus fs-2"></i>Tambah</a>
            @endcan
            </div>
        </div>
    </div>

    <div class="card-body py-4">
        <div class="panel-container show">
            <div class="panel-content">
                {!! html()->form('POST', route($page->code.'.sorted'))->id('myForm')->acceptsFiles()->class('form form-horizontal')->open() !!}
                <div class="alert alert-success mb-0" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="alert-icon width-3">
                                <span class="icon-stack icon-stack-sm">
                                    <i class="ni ni-list color-success-800 icon-stack-2x font-weight-bold"></i>
                                </span>
                        </div>
                        <div class="flex-1">
                            <span class="h5 m-0 fw-700"><i class="{!! $page->icon !!}"></i> List Menu {!! config('master.app.profile.name') !!} !</span>
                            Susun menu dengan benar.
                        </div>
                    </div>
                </div>
                <div class="col col-md-12 col-xl-12 ">
                    <div class="panel-tag">
                        <p class="loading text-center" style="display: none">
                            <button class="btn btn-outline-dark rounded-pill waves-effect waves-themed text-center" type="button" disabled="">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Loading...
                            </button>
                        </p>
                        <div class="table-responsive">
                            <div class="dd p-10 fit-width" id="nestable" style="min-width: 100%;">
                                <div class="list">
                                    {{--  Nestable Data --}}
                                </div>
                                <div>
                                    {!! html()->textarea('sort')->id('nestable-output')->style('display:none')->class('form-control')->placeholder('Nestable Output') !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-success">
                    <a href="#" class="btn btn-sm btn-info-light submit-data">
                        <i class="fa fa-save"></i> Simpan
                    </a>
                </div>
                {!! html()->hidden('function','sidebarMenu')->id('function') !!}
                {!! html()->form()->close() !!}
            </div>
        </div>
    </div>
</div>

@prepend('css')
<!-- <link rel="stylesheet" href="{{ url('/assets/vendor/nestable2/jquery.nestable.css') }}" /> -->
<style type="text/css">

.cf:after { visibility: hidden; display: block; font-size: 0; content: " "; clear: both; height: 0; }
* html .cf { zoom: 1; }
*:first-child+html .cf { zoom: 1; }

html { margin: 0; padding: 0; }
body { font-size: 100%; margin: 0; padding: 1.75em; font-family: 'Helvetica Neue', Arial, sans-serif; }

h1 { font-size: 1.75em; margin: 0 0 0.6em 0; }

a { color: #2996cc; }
a:hover { text-decoration: none; }

p { line-height: 1.5em; }
.small { color: #666; font-size: 0.875em; }
.large { font-size: 1.25em; }

/**
 * Nestable
 */

.dd { position: relative; display: block; margin: 0; padding: 0; max-width: 600px; list-style: none; font-size: 13px; line-height: 20px; }

.dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
.dd-list .dd-list { padding-left: 30px; }
.dd-collapsed .dd-list { display: none; }

.dd-item,
.dd-empty,
.dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: 13px; line-height: 20px; }

.dd-handle { display: block; height: 30px; margin: 5px 0; padding: 5px 10px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
    background: #fafafa;
    background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:         linear-gradient(top, #fafafa 0%, #eee 100%);
    -webkit-border-radius: 3px;
            border-radius: 3px;
    box-sizing: border-box; -moz-box-sizing: border-box;
}
.dd-handle:hover { color: #2ea8e5; background: #fff; }

.dd-item > button { display: block; position: relative; cursor: pointer; float: left; width: 25px; height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; }
.dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
.dd-item > button[data-action="collapse"]:before { content: '-'; }

.dd-placeholder,
.dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; -moz-box-sizing: border-box; }
.dd-empty { border: 1px dashed #bbb; min-height: 100px; background-color: #e5e5e5;
    background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                      -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
    background-image:    -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                         -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
    background-image:         linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                              linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
    background-size: 60px 60px;
    background-position: 0 0, 30px 30px;
}

.dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
.dd-dragel > .dd-item .dd-handle { margin-top: 0; }
.dd-dragel .dd-handle {
    -webkit-box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
            box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
}

/**
 * Nestable Extras
 */

.nestable-lists { display: block; clear: both; padding: 30px 0; width: 100%; border: 0; border-top: 2px solid #ddd; border-bottom: 2px solid #ddd; }

#nestable-menu { padding: 0; margin: 20px 0; }

#nestable-output,
#nestable2-output { width: 100%; height: 7em; font-size: 0.75em; line-height: 1.333333em; font-family: Consolas, monospace; padding: 5px; box-sizing: border-box; -moz-box-sizing: border-box; }

#nestable2 .dd-handle {
    color: #fff;
    border: 1px solid #999;
    background: #bbb;
    background: -webkit-linear-gradient(top, #bbb 0%, #999 100%);
    background:    -moz-linear-gradient(top, #bbb 0%, #999 100%);
    background:         linear-gradient(top, #bbb 0%, #999 100%);
}
#nestable2 .dd-handle:hover { background: #bbb; }
#nestable2 .dd-item > button:before { color: #fff; }

@media only screen and (min-width: 700px) {

    .dd { float: left; width: 48%; }
    .dd + .dd { margin-left: 2%; }

}

.dd-hover > .dd-handle { background: #2ea8e5 !important; }

/**
 * Nestable Draggable Handles
 */

.dd3-content { display: block; height: 30px; margin: 5px 0; padding: 5px 10px 5px 40px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
    background: #fafafa;
    background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:         linear-gradient(top, #fafafa 0%, #eee 100%);
    -webkit-border-radius: 3px;
            border-radius: 3px;
    box-sizing: border-box; -moz-box-sizing: border-box;
}
.dd3-content:hover { color: #2ea8e5; background: #fff; }

.dd-dragel > .dd3-item > .dd3-content { margin: 0; }

.dd3-item > button { margin-left: 30px; }

.dd3-handle { position: absolute; margin: 0; left: 0; top: 0; cursor: pointer; width: 30px; text-indent: 100%; white-space: nowrap; overflow: hidden;
    border: 1px solid #aaa;
    background: #ddd;
    background: -webkit-linear-gradient(top, #ddd 0%, #bbb 100%);
    background:    -moz-linear-gradient(top, #ddd 0%, #bbb 100%);
    background:         linear-gradient(top, #ddd 0%, #bbb 100%);
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
.dd3-handle:before { content: '≡'; display: block; position: absolute; left: 0; top: 3px; width: 100%; text-align: center; text-indent: 0; color: #fff; font-size: 20px; font-weight: normal; }
.dd3-handle:hover { background: #ddd; }

/**
 * Socialite
 */

.socialite { display: block; float: left; height: 35px; }

    </style>
@endprepend
@prepend('js')
    <script src="{{ url('/assets/vendor/nestable-/jquery.nestable.js') }}"></script>
    <script src="{{ url('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
    <script src="{{ url('js/'.$backend.'/'.$page->code.'/datatable.js') }}"></script>
    <script src="{{ url('js/jquery-crud.js') }}"></script>
    <script>
        $(document).on("keyup", "#title", function () {
            let title = $(this).val();
            let value = title.replace(/ /g, "") + "s";
            $("#url").val(value.toLowerCase());
            $("#code").val(value.toLowerCase());
        });
    </script>
@endprepend
</x-app-layout>
