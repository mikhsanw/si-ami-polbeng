
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<!--begin::Head-->
	<head>
<base href="../" />
		<title>@if(isset($title)){{ $title }} - @endif {{ config('master.app.profile.name') }}</title>
		<meta charset="utf-8" />
		<meta name="description" content="{{ config('master.app.profile.description') }}" />
		<meta name="keywords" content="{{ config('master.app.profile.keywords') }}" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta property="og:locale" content="id_ID" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content="@if(isset($title)){{ $title }}@endif" />
		<meta property="og:url" content="{{url('/')}}" />
		<meta property="og:site_name" content="{{ config('master.app.profile.name') }}" />
		<link rel="canonical" href="{{url('/')}}" />
		
		
		{!! includeFavicon() !!}

		<!--begin::Fonts-->
		{!! includeFonts() !!}
		<!--end::Fonts-->

		<!--begin::Global Stylesheets Bundle(used by all pages)-->
		@foreach(getGlobalAssets('css') as $path)
			{!! sprintf('<link rel="stylesheet" href="%s">', asset($path)) !!}
		@endforeach
		<!--end::Global Stylesheets Bundle-->

		<!--begin::Vendor Stylesheets(used by this page)-->
		@foreach(getVendors('css') as $path)
			{!! sprintf('<link rel="stylesheet" href="%s">', asset($path)) !!}
		@endforeach
		<!--end::Vendor Stylesheets-->

		<!--begin::Custom Stylesheets(optional)-->
		@foreach(getCustomCss() as $path)
			{!! sprintf('<link rel="stylesheet" href="%s">', asset($path)) !!}
		@endforeach
		<!--end::Custom Stylesheets-->
	@stack('css')
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_app_body" data-kt-app-layout="light-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
		<!--begin::Theme mode setup on page load-->
		<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
		<!--end::Theme mode setup on page load-->
		
        <!--begin::App-->
        <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
            <!--begin::Page-->
            <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
                @include('layouts.backend.parsial.header')
                <!--begin::Wrapper-->
                <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                    @include('layouts.backend.parsial.sidebar')
                    <!--begin::Main-->
                    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                        <!--begin::Content wrapper-->
                        <div class="d-flex flex-column flex-column-fluid">
                            @include('layouts.backend.parsial.toolbar')
                            <!--begin::Content-->
                            <div id="kt_app_content" class="app-content flex-column-fluid">
                                <!--begin::Content container-->
                                <div id="kt_app_content_container" class="app-container container-fluid">
                                    {{ $slot }}
                                </div>
                                <!--end::Content container-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Content wrapper-->
                        @include('layouts.backend.parsial.footer')
                    </div>
                    <!--end:::Main-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Page-->
        </div>
        <!--end::App-->

		<!--begin::Javascript-->
		<!--begin::Global Javascript Bundle(mandatory for all pages)-->
		@foreach(getGlobalAssets() as $path)
			{!! sprintf('<script src="%s"></script>', asset($path)) !!}
		@endforeach
		<!--end::Global Javascript Bundle-->

		<!--begin::Vendors Javascript(used by this page)-->
		@foreach(getVendors('js') as $path)
			{!! sprintf('<script src="%s"></script>', asset($path)) !!}
		@endforeach
		<!--end::Vendors Javascript-->

		<!--begin::Custom Javascript(optional)-->
		@foreach(getCustomJs() as $path)
			{!! sprintf('<script src="%s"></script>', asset($path)) !!}
		@endforeach
		<!--end::Custom Javascript-->

		@stack('js')
		<script src="{{ asset('js/jquery.loadmodal.js') }}"></script>
		<script src="{{ asset('js/jquery.blockUI.js') }}"></script>
		<script src="{{ asset('js/'.$backend.'/js/jquery.js') }}"></script>
		<!--end::Javascript-->
	</body>
	<!--end::Body-->
</html>