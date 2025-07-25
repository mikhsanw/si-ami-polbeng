
<!DOCTYPE html>
<!--
Author: Keenthemes
Product Name: Metronic 
Product Version: 8.2.1
Purchase: https://1.envato.market/EA4JP
Website: http://www.keenthemes.com
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
License: For each use you must have a valid license purchased only from above link in order to legally use the theme for your project.
-->
<html lang="en">
	<!--begin::Head-->
	<head>
<base href="../" />
		<title>Metronic - The World's #1 Selling Bootstrap Admin Template - Metronic by KeenThemes</title>
		<meta charset="utf-8" />
		<meta name="description" content="The most advanced Bootstrap 5 Admin Theme with 40 unique prebuilt layouts on Themeforest trusted by 100,000 beginners and professionals. Multi-demo, Dark Mode, RTL support and complete React, Angular, Vue, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js, Node.js, Flask, Symfony & Laravel versions. Grab your copy now and get life-time updates for free." />
		<meta name="keywords" content="metronic, bootstrap, bootstrap 5, angular, VueJs, React, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js, Node.js, Flask, Symfony & Laravel starter kits, admin themes, web design, figma, web development, free templates, free admin themes, bootstrap theme, bootstrap template, bootstrap dashboard, bootstrap dak mode, bootstrap button, bootstrap datepicker, bootstrap timepicker, fullcalendar, datatables, flaticon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content="Metronic - The World's #1 Selling Bootstrap Admin Template - Metronic by KeenThemes" />
		<meta property="og:url" content="https://keenthemes.com/metronic" />
		<meta property="og:site_name" content="Metronic by Keenthemes" />
		<link rel="canonical" href="https://preview.keenthemes.com/metronic8" />
		
		
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
		
		<!--begin::Root-->
        <div class="d-flex flex-column flex-root" id="kt_app_root">
            <!--begin::Wrapper-->
            <div class="d-flex flex-column flex-center flex-column-fluid">
                <!--begin::Content-->
                <div class="d-flex flex-column flex-center text-center p-10">
                    <!--begin::Card-->
                    <div class="card card-flush w-lg-650px py-5">
                        <!--begin::Card body-->
                        <div class="card-body py-15 py-lg-20">
                            @yield('content')
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Root-->

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
		<script src="{{ url('/js/jquery.loadmodal.js') }}"></script>
		<script src="{{ url('/js/jquery.blockUI.js') }}"></script>
		<script src="{{ url('/js/jquery-layouts.js') }}"></script>
		<!--end::Javascript-->
	</body>
	<!--end::Body-->
</html>