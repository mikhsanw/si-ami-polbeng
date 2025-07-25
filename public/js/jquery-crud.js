/*jshint esversion: 11 */
$(window.document).on('click', '.btn-action', function (e) {
    e.preventDefault();

    const id = $(this).data('id') ?? '';
    const url = $(this).data('url') ?? window.location.href;
    // const url = window.location.href;
    const action = $(this).data('action') ?? '';
    const title = $(this).data('title') ?? '';
    const modalId = $(this).data('modalid') ?? 'modal-master';
    const bgClass = $(this).data('bgClass') ?? 'bg-default';
    const icon = $(this).data('icon') ?? 'fa fa-edit';
    const actionUrls = {
        'create': 'create',
        'edit': `${id}/edit`,
        'delete': `delete/${id}`,
        'show': `${id}`,
        'detail': `detail/${id}`,
    };
    const urlExtension = actionUrls[action] || '';
    console.log(urlExtension ? `/${url}/${urlExtension}` : url)
    const modalOptions = {
        url: urlExtension ? `/${url}/${urlExtension}` : url,
        id: modalId,
        dlgClass: 'fade',
        bgClass: bgClass,
        title: title,
        icon: icon,
        width: 'whatever',
        modal: {
            keyboard: false,
            backdrop: 'static',
        },
        ajax: {
            dataType: 'html',
            method: 'GET',
            cache: false,
            beforeSend() {
                $.showLoading();
            },
            success() {
                $.hideLoading();
                $(`#${modalId}`).modal('show');
            },
            error: function (xhr) {
                $.hideLoading();
                $.showError(xhr.status + ' ' + xhr.statusText);
            }
        },
    };

    $.loadModal(modalOptions);
});

$(window.document).on('click', '.submit-data', function (e) {
    e.preventDefault();
    // tinymce.triggerSave();
    const btnSubmit = e.target;
    const textBtn = btnSubmit.innerText;
    const parent = $(this).parents('.modal').length ? $(this).parents('.modal') : $(this).parents();
    const form = btnSubmit.form ?? btnSubmit.closest('form') ?? parent.find('form');
    const formBtn = btnSubmit.attributes['data-form'] ? btnSubmit.attributes['data-form'].value : form.id;
    const formId = formBtn ?? form.attr('id');
    const progress = $('.progress-bar');
    const dismiss = parent.find('[data-bs-dismiss]');
    clearError();

    if (!formValidate([formId])) {
        return false;
    }
   
    $('#' + formId).ajaxForm({
        dataType: 'json',
        uploadProgress: function (event, position, total, percentComplete) {
            const percentVal = percentComplete + '%';
            progress.width(percentVal);
            progress.html(percentComplete === 100 ? 'Mohon tunggu...' : percentVal);
        },
        beforeSubmit: function () {
            progress.width('0%');
            progress.html('0% Complete');
            dismiss.prop('disabled', true);
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            $('.progress').show();
        },
        success: function (response, status, xhr, $form) {
            $('.progress').hide();
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa fa-save"></i> ' + textBtn;
            dismiss.prop('disabled', false);

            if (response.status === true) {
                const _targetTable = $form.find('input[name="table-id"]').val();
                const _targetFunction = $form.find('input[name="function"]').val();
                const _redirect = $form.find('input[name="redirect"]').val() || '';

                swal.fire({
                    title: response.title || 'Good job!',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });

                if (_targetTable) {
                    _targetTable.split(',').forEach((tableId) => {
                        let tableElement = $(`#${tableId}`);
                        if (tableElement.length) {
                            let table = tableElement.DataTable();
                            if (table && $.fn.DataTable.isDataTable(`#${tableId}`)) {
                                table.ajax.reload();
                            }
                        }
                    });
                }
                if (_targetFunction) {
                    _targetFunction.split(',').forEach((func) => {
                        if (typeof window[func] === 'function') {
                            window[func]();
                        }
                    });
                }
                if (_redirect) {
                    window.location.href = _redirect;
                }
                const modalId = e.target.closest('.modal').id;
                if (modalId) {
                    $(`#${modalId}`).modal('hide');
                }else{
                    $('.modal').modal('hide');
                }
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
            }
        },
        error: function (xhr) {
            $('.progress').hide();
            dismiss.prop('disabled', false);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa fa-save"></i> ' + textBtn;
            errorBuilder(xhr);
        }
    }).submit();
});

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

const clearError = (targetClass = 'error-msg') => {
    $(`.invalid-feedback, .alert-danger, .${targetClass}`).remove();
    $('.is-invalid').removeClass('is-invalid border-danger');
}

const formValidate = (formIds) => {
    let isValid = true;
    let errors = {};

    formIds.forEach(formId => {
        $(`#${formId} [required]`).each(function (e, field) {
            if (!field.value || (['radio', 'checkbox'].includes(field.type) && !field.checked)) {
                errors[field.id] = 'This field is required.';
                isValid = false;
            }
        });
    });

    if (!isValid) errorBuilder(errors);
    return isValid;
}

$(window.document).on('click', '.delete-file', function (e) {
    e.preventDefault();
    let btn = $(this);
    swal.fire({
        title: btn.data('title'),
        text: btn.data('message'),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, Delete!",
        cancelButtonText: "No, Cancel!",
        closeOnConfirm: false,
        closeOnCancel: false
    }, function (isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: btn.data('url'),
                success: function (data) {
                    if (data.status === true) {
                        $('#' + btn.data('id')).remove();
                        swal.fire("Deleted!", data.message, "success");
                    }
                },
                error: function (e) {
                    swal.fire("Error!", e.responseJSON.message, "error");
                }
            });
        } else {
            swal.DismissReason.cancel;
        }
    });
});
