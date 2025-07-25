<x-app-layout>

    <div class="card border-transparent" data-bs-theme="light" style="background-color: #1C325E;">
        <!--begin::Body-->
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <div class="card-body d-flex ps-xl-15">
            <!--begin::Wrapper-->
            <div class="m-0">
                <!--begin::Title-->
                <div class="position-relative fs-2x z-index-2 fw-bold text-white mb-7">
                Welcome to Dashboard</div>
                <!--end::Title-->
                
            </div>
            <!--begin::Wrapper-->
            <!--begin::Illustration-->
            <img src="assets/media/illustrations/sigma-1/17-dark.png" class="position-absolute me-3 bottom-0 end-0 h-200px" alt="">
            <!--end::Illustration-->
        </div>
        <!--end::Body-->
    </div>

</x-app-layout>