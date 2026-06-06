$(document).ready(function () {
    var datatable = $('#datatable').DataTable({
        responsive: true,
        lengthChange: true,
        processing: true,
        serverSide: false,
        stateSave: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        ajax: "{{ route($url . '.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'nama' },
            { data: 'total_penugasan', className: 'text-center' },
            {
                data: 'pct_responsivitas', className: 'text-center',
                render: function (data) { return pctBadge(data, 'primary'); }
            },
            {
                data: 'pct_kecepatan', className: 'text-center',
                render: function (data, type, row) {
                    var badge = pctBadge(data, 'success');
                    if (row.avg_hari_respon !== null) {
                        badge += '<div class="text-muted fs-8">' + row.avg_hari_respon + ' hari</div>';
                    }
                    return badge;
                }
            },
            {
                data: 'pct_catatan', className: 'text-center',
                render: function (data) { return pctBadge(data, 'info'); }
            },
            {
                data: 'skor_keseluruhan', className: 'text-center',
                render: function (data) {
                    if (data === null || data === undefined) return '<span class="badge badge-light">-</span>';
                    var color = data >= 80 ? 'success' : (data >= 60 ? 'primary' : (data >= 40 ? 'warning' : 'danger'));
                    var label = data >= 80 ? 'Sangat Baik' : (data >= 60 ? 'Baik' : (data >= 40 ? 'Cukup' : 'Perlu Perhatian'));
                    return '<span class="badge badge-light-' + color + ' fs-7 fw-bold">' + data + '%</span>'
                         + '<div class="text-muted fs-8">' + label + '</div>';
                }
            },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' },
        ],
    });

    exportButtons();

    $('#search').on('keyup', function () {
        datatable.search(this.value).draw();
    });
});

function pctBadge(data, color) {
    if (data === null || data === undefined) return '<span class="badge badge-light">-</span>';
    return '<span class="badge badge-light-' + color + ' fs-7">' + data + '%</span>';
}

var exportButtons = () => {
    const documentTitle = 'Performa Auditor';
    var buttons = new $.fn.dataTable.Buttons('#datatable', {
        buttons: [
            { extend: 'copyHtml5',  title: documentTitle },
            { extend: 'excelHtml5', title: documentTitle },
            { extend: 'csvHtml5',   title: documentTitle },
            { extend: 'pdfHtml5',   title: documentTitle },
        ]
    }).container().appendTo($('#kt_datatable_example_buttons'));

    const exportButtons = document.querySelectorAll('#kt_datatable_example_export_menu [data-kt-export]');
    exportButtons.forEach(exportButton => {
        exportButton.addEventListener('click', e => {
            e.preventDefault();
            const exportValue = e.target.getAttribute('data-kt-export');
            const target = document.querySelector('.dt-buttons .buttons-' + exportValue);
            target.click();
        });
    });
};
