$(document).ready(function () {
	var datatable = $('#datatable').DataTable({
		responsive: true,
		lengthChange: false,
		searchDelay: 500,
        processing: true,
        serverSide: true,
        lengthMenu: [[10, 25, 50, 100 ,200 , 500, -1], [10, 25, 50, 100 ,200 , 500, "All"]],
		ajax: dynamicUrl,
		language: {
            {{-- Uncomment this line to use Indonesian language --}}
            {{--url: "{{ asset(config('master.app.web.assets').'/assets/vendor_components/datatable/indonesian.json') }}"--}}
        },
		columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex',orderable: false, searchable: false, orderable: false, className: 'text-center' },
				{ data: 'nama' },
				{ data: 'tipe' },
				{ data: 'user.name' },
				{ data: 'sub' },

			{ data: 'action', orderable: false, searchable: false , className: 'text-center'}
		],
        columnDefs: [
                {
                    // Target kolom 'DT_RowIndex' (indeks 0) dan 'action' (indeks 5)
                    targets: [0, 5], 
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    // Target kolom 'sub' (indeks 4)
                    // Ingat, penghitungan indeks dimulai dari 0
                    targets: 4, 
                    // Logika visibilitas:
                    // Kolom ini akan terlihat (true) jika isChildPage bernilai false.
                    // Kolom ini akan disembunyikan (false) jika isChildPage bernilai true.
                    visible: !isChildPage,
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ]
        
	});

    exportButtons();
    $('#search').on( 'keyup', function () {
        datatable.search( this.value ).draw();
    } );

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

