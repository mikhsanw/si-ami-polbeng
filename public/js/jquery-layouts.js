function logout() {
    swal.fire({
        title: 'Apakah kamu yakin?',
        text: "Kamu akan keluar dari aplikasi ini!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonText: 'Batal',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, keluar!',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'logout',
                type: 'POST',
                data: {
                    _token: '{!! csrf_token() !!}',
                    device: `web`
                },
                dataType: 'json',
                success: function (response) {
                    window.location.href = "{!! url('login') !!}";
                }
            });
        }else {
            // Swal.fire("Gagal download!", "", "error");
        }
    });
}