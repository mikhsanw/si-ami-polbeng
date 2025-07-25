
<!DOCTYPE html>
-->
<html lang="en">
	<!--begin::Head-->
	<head>
<base href="../../../" />
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
		<link rel="shortcut icon" href="{{asset('assets/media/logos/favicon.ico')}}" />
		<!--begin::Fonts(mandatory for all pages)-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
		<link href="{{asset('assets/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
		<script>// Frame-busting to prevent site from being loaded within a frame without permission (click-jacking) if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat">
		<!--begin::Theme mode setup on page load-->
		<script>
		var defaultThemeMode = "dark"; 
		var themeMode; 
		if ( document.documentElement ) {
			if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { 
				themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); 
			} else { 
				if ( localStorage.getItem("data-bs-theme") !== null ) { 
					themeMode = localStorage.getItem("data-bs-theme"); 
				} else { 
					themeMode = defaultThemeMode; 
				} 
			} if (themeMode === "system") { 
				themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; 
			} 
			document.documentElement.setAttribute("data-bs-theme", themeMode); 
		}
		</script>

		{{ $slot }}
		
		<script>var hostUrl = "assets/";</script>
		<script src="{{asset('assets/plugins/global/plugins.bundle.js')}}"></script>
		<script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
		<script src="{{asset('assets/js/custom/authentication/sign-in/general.js')}}"></script>
	</body>
	<!--end::Body-->
</html>