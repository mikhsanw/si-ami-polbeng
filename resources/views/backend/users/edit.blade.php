<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <div class="card card-flush py-4">
                <div class="card-body col-8">
                    <form id="formEdit" method="POST" action="{{ url('admin/users/'.$data->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-10">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $data->name }}" required>
                        </div>

                        <div class="mb-10">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $data->email }}">
                        </div>

                        <div class="mb-10">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>

                        <div class="mb-10">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>

                        <div class="mb-10">
                            <label for="roles" class="form-label">Roles</label>
                            <select class="form-select" multiple aria-label="Roles" id="roles" name="roles[]">
                                @foreach ($roles as $role)
                                    @if ($role != 'Super Admin')
                                        <option value="{{ $role }}" {{ in_array($role, $userRoles ?? []) ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                    @else
                                        @if (Auth::user()->hasRole('Super Admin'))
                                            <option value="{{ $role }}" {{ in_array($role, $userRoles ?? []) ? 'selected' : '' }}>
                                                {{ $role }}
                                            </option>
                                        @endif
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-10">
                            <button type="button" id="btn-submit" class="btn btn-primary">
                                <span class="indicator-label">Save Changes</span>
                                <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('js')
    <script src="{{ url('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
    <script>
    $(document).ready(function () {
        // cegah form submit bawaan browser
        $('#formEdit').on('submit', function (e) {
            e.preventDefault();
        });

        $('#btn-submit').click(function () {
            let form = $('#formEdit');
            console.log('Form Action:', form.attr('action')); // debug: pastikan bukan /edit

            $(this).attr("data-kt-indicator", "on").prop('disabled', true);

            $.ajax({
                type: 'POST', // tetap POST karena ada _method=PUT
                url: form.attr('action'),
                data: form.serialize(),
                success: function (response) {
                    $('#btn-submit').removeAttr("data-kt-indicator").prop('disabled', false);
                    if (response.status) {
                        Swal.fire({
                            text: response.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: { confirmButton: "btn btn-primary" }
                        }).then(() => {
                            window.location.href = "{{ route('users.index') }}";
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            text: response.message ?? 'Something went wrong'
                        });
                    }
                },
                error: function (xhr) {
                    $('#btn-submit').removeAttr("data-kt-indicator").prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        text: xhr.responseJSON?.message ?? 'Server error'
                    });
                }
            });
        });
    });
    </script>
    @endpush
</x-app-layout>
