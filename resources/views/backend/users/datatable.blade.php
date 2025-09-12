$(document).ready(function () {
	var datatable = $('#datatable').DataTable({
		responsive: true,
		lengthChange: false,
		searchDelay: 500,
        processing: true,
        serverSide: true,
        stateSave: true,
        lengthMenu: [[10, 25, 50, 100 ,200 , 500, -1], [10, 25, 50, 100 ,200 , 500, "All"]],
		ajax: "{{ url(config('master.app.url.backend').'/'.$url.'/data') }}",
		language: {
            {{-- Uncomment this line to use Indonesian language --}}
            {{--url: "{{ asset(config('master.app.web.assets').'/assets/vendor_components/datatable/indonesian.json') }}"--}}
        },
		columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex',orderable: false, searchable: false, orderable: false, className: 'text-center' },
            { data: 'name' , 'defaultContent':''},
			{ data: 'email' , 'defaultContent':''},
			{ data: 'role' , 'defaultContent':''},
            { data: 'last_login_at' , 'defaultContent':''},
            { data: 'created_at', 
                "render": function (data, type, row) {
                    if (data) {
                        return moment(data).format('DD MMMM YYYY HH:mm'); // Pakai moment.js
                    }
                    return '-';
                }
            },
			{ data: 'action', orderable: false, searchable: false , className: 'text-center'}
		],
        
	});

    exportButtons();
    $('#search').on( 'keyup', function () {
        datatable.search( this.value ).draw();
    } );

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        const parent = e.target.closest('tr');
        const customerName = parent.querySelectorAll('td')[1].innerText;
        Swal.fire({
            text: "Are you sure you want to delete " + customerName + "?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Yes, delete!",
            cancelButtonText: "No, cancel",
            customClass: {
                confirmButton: "btn fw-bold btn-danger",
                cancelButton: "btn fw-bold btn-active-light-primary"
            }
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    type: "POST",
                    url: "{{config('master.app.url.backend')}}/"+url+"/"+id,
                    data: {
                        '_method': 'DELETE',
                        '_token': '{{ csrf_token() }}',
                    },
                    success: function (data) {
                        if (data.status === true) {
                            Swal.fire({
                                text: "Deleting " + customerName,
                                icon: "info",
                                buttonsStyling: false,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(function () {
                                Swal.fire({
                                    text: "You have deleted " + customerName + "!.",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    // delete row data from server and re-draw datatable
                                    $('#' + $(this).data('id')).remove();
                                    $('#datatable').DataTable().draw();
                                });
                            });
                        }else{
                            swal.fire("Error!", data.message, "error");
                        }
                    },
                    error: function (e) {
                        swal.fire("Error!", e.responseJSON.message, "error");
                    }      
                });
            }
        });
    });
});

// Hook export buttons
var exportButtons = () => {
    const documentTitle = 'Customer Orders Report';
    var buttons = new $.fn.dataTable.Buttons('#datatable', {
        buttons: [
            {
                extend: 'copyHtml5',
                title: documentTitle
            },
            {
                extend: 'excelHtml5',
                title: documentTitle
            },
            {
                extend: 'csvHtml5',
                title: documentTitle
            },
            {
                extend: 'pdfHtml5',
                title: documentTitle
            }
        ]
    }).container().appendTo($('#kt_datatable_example_buttons'));

    // Hook dropdown menu click event to datatable export buttons
    const exportButtons = document.querySelectorAll('#kt_datatable_example_export_menu [data-kt-export]');
    exportButtons.forEach(exportButton => {
        exportButton.addEventListener('click', e => {
            e.preventDefault();

            // Get clicked export value
            const exportValue = e.target.getAttribute('data-kt-export');
            const target = document.querySelector('.dt-buttons .buttons-' + exportValue);

            // Trigger click event on hidden datatable export buttons
            target.click();
        });
    });
}
// Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
var handleSearchDatatable = () => {
    const filterSearch = document.querySelector('[data-kt-docs-table-filter="search"]');
    filterSearch.addEventListener('keyup', function (e) {
        // Check if datatable is properly initialized
        if (datatable) {
            datatable.search(e.target.value).draw();
        } else {
            console.error('Datatable is not properly initialized.');
        }
    });
}

var handleDeleteRows = () => {
    // Select all delete buttons
    document.addEventListener('click', function (e) {
        if (e.target.matches('[data-kt-docs-table-filter="delete_row"]')) {
            e.preventDefault();
            console.log('masuk');

            // Select parent row
            const parent = e.target.closest('tr');

            // Get customer name
            const customerName = parent.querySelectorAll('td')[1].innerText;

            // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
            Swal.fire({
                text: "Are you sure you want to delete " + customerName + "?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Yes, delete!",
                cancelButtonText: "No, cancel",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: $(this).data('url'),
                        success: function (data) {
                            Swal.fire({
                                text: "Deleting " + customerName,
                                icon: "info",
                                buttonsStyling: false,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(function () {
                                Swal.fire({
                                    text: "You have deleted " + customerName + "!.",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    // delete row data from server and re-draw datatable
                                    $('#' + $(this).data('id')).remove();
                                });
                            });
                        },
                        error: function (e) {
                            swal.fire("Error!", e.responseJSON.message, "error");
                        }
                    });
                    
                } else if (result.dismiss === 'cancel') {
                    Swal.fire({
                        text: customerName + " was not deleted.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                }
            });
        }
    });
}