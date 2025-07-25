<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">
        <div class="card card-flush py-4">
            <div class="card-body col-8">
                <form id="form" class="form fv-plugins-bootstrap5 fv-plugins-framework" action="#">
                    @csrf
                    
                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        <label for="name" class=" form-label">Name</label>
                        <input type="text" class="form-control " id="name" name="name" required>
                        
                    </div>

                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        <label for="email" class=" form-label">Email Address</label>
                        <input type="email" class="form-control " id="email" name="email">
                       
                    </div>

                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        <label for="password" class=" form-label">Password</label>
                        <input type="password" class="form-control " id="password" name="password">
                        
                    </div>

                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        <label for="password_confirmation" class=" form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>

                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        <label for="roles" class=" form-label">Roles</label>
                            <select class="form-select" multiple aria-label="Roles" id="roles" name="roles[]">
                                @forelse ($roles as $role)
                                    @if ($role != 'Super Admin')
                                        <option value="{{ $role }}" {{ in_array($role, old('roles') ?? []) ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                    @else
                                        @if (Auth::user()->hasRole('Super Admin'))
                                            <option value="{{ $role }}" {{ in_array($role, old('roles') ?? []) ? 'selected' : '' }}>
                                                {{ $role }}
                                            </option>
                                        @endif
                                    @endif
                                @empty
                                    <option disabled>No roles available</option>
                                @endforelse

                            </select>
                    </div>
                    
                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        <button type="submit" id="btn-submit" data-url="users" class="btn btn-primary">
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
@prepend('js')
<script src="{{ url('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
<script>
$(document).ready(function () {
    const errorBuilder = (error, targetClass = 'modal-message') => {
        const errorValidation = (errors) => {
            for (const [index, value] of Object.entries(errors)) {
                const element = document.getElementById(index);
                if (element) {
                    const isSelect2 = $(element).hasClass('select2-hidden-accessible');
                    const targetElement = isSelect2
                        ? ($(element).next().find('.select2-selection')[0] || element)
                        : (['radio', 'checkbox'].includes($(element).attr('type')) ? ($(element).parent()[0] || element) : element);

                    targetElement.classList.add('is-invalid', 'border', 'border-danger');
                    targetElement.insertAdjacentHTML('afterend', `<span class="invalid-feedback" role="alert"><b>${value}</b></span>`);
                    if (index === Object.keys(errors)[0]) targetElement.focus();
                }
            }
        };

        const errorMessage = (message) => {
            const indexHash = targetClass.indexOf("#");
            $(indexHash !== -1 ? `${targetClass}` : `.${targetClass}`).html(`<div class="error-msg alert alert-danger text-white form-group m-15"><b>Opps!</b> ${message}</div>`);
        };

        if (error?.responseJSON?.errors) {
            errorValidation(error.responseJSON.errors);
        } else if (error?.responseJSON?.message) {
            errorMessage(error.responseJSON.message);
        } else if (error?.message) {
            errorMessage(error.message);
        } else {
            errorValidation(error);
        }
    };
    const formValidate = (formIds) => {
        let isValid = true;
        let errors = {};

        formIds.forEach(formId => {
            $(`#${formId} :required`).each(function (index, field) {
                if (!field.value || (['radio', 'checkbox'].includes(field.type) && !field.checked)) {
                    errors[field.id] = 'This field is required.';
                    isValid = false;
                }
            });
        });

        if (!isValid) errorBuilder(errors);
        return isValid;
    }

    const clearError = (targetClass = 'error-msg') => {
        $(`.invalid-feedback, .alert-danger, .${targetClass}`).remove();
        $('.is-invalid').removeClass('is-invalid border-danger');
    }

    // Handle form submission
    $('#btn-submit').click(function (e) {
        e.preventDefault();
        clearError();

        var form = $('#form');  // Use jQuery to select the form
        if (!formValidate(['form'])) {
            return false;
        }
        $(this).attr("data-kt-indicator", "on");  // Use attr() to set attributes
        $(this).prop('disabled', true);  // Use prop() to disable the button

        setTimeout(function () {
            $.ajax({
                type: 'POST',
                url: "{{ route('users.store') }}",
                data: form.serialize(),
                success: function (response) {
                    if (response.status === true) {
                        $('#btn-submit').removeAttr("data-kt-indicator");  // Use removeAttr() to remove attributes
                        $('#btn-submit').prop('disabled', false);  // Use prop() to enable the button

                        Swal.fire({
                            text: response.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{route('users.index')}}";
                        };
                        });
                    } else {
                        if (response.hasOwnProperty('data')) {
                            errorBuilder(response.data);
                        } else {
                            swal.fire({
                                title: response.title || 'Oops!',
                                text: response.message,
                                icon: 'error',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        }
                        $('#btn-submit').removeAttr("data-kt-indicator");  // Use removeAttr() to remove attributes
                        $('#btn-submit').prop('disabled', false);  // Use prop() to enable the button

                    }
                },
                error: function (xhr) {
                    $('#btn-submit').removeAttr("data-kt-indicator");  // Use removeAttr() to remove attributes
                    $('#btn-submit').prop('disabled', false);  // Use prop() to enable the button
                    errorBuilder(xhr);
                }
            });
        }, 2000);
    });

});
</script>
@endprepend
</x-app-layout>