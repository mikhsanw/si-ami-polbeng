{{--<script>--}}
    $(function () {
        // negative block
        $(document).on('input', '.negative-block', function (e) {
            if (e.keyCode === 109 || e.keyCode === 189) e.preventDefault();
            if ($(this).val() < 0) $(this).val(0);
        });
        // end negative block

        //remove all whitespace
        $(document).on('input', '.remove-whitespace', function (e) {
            $(this).val($(this).val().replace(/\s/g, ''));
        });
        $(document).on('change', '.remove-whitespace', function (e) {
            $(this).val($(this).val().replace(/\s/g, ''));
        });
        // end remove all whitespace

        // remove whitespace on first and last
        $(document).on('input', '.remove-whitespace-first-last', function (e) {
            $(this).val($(this).val().replace(/^\s+/, '').replace(/\s+$/, ''));
        });
        $(document).on('change', '.remove-whitespace-first-last', function (e) {
            $(this).val($(this).val().replace(/^\s+/, '').replace(/\s+$/, ''));
        });
        // end remove whitespace on first and last

        //regex only number
        $(document).on('input', '.only-number', function (e) {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
        });
        $(document).on('change', '.only-number', function (e) {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
        });
        // end regex only number

        //regex only alphabet
        $(document).on('input', '.only-alphabet', function (e) {
            $(this).val($(this).val().replace(/[^a-zA-Z]/g, ''));
        });
        $(document).on('change', '.only-alphabet', function (e) {
            $(this).val($(this).val().replace(/[^a-zA-Z]/g, ''));
        });
        // end regex only alphabet

        // format rupiah
        $(document).on('input', '.format-rupiah', function (e) {
            $(this).val(formatRupiah($(this).val(), 'Rp. '));
        });
        $(document).on('change', '.format-rupiah', function (e) {
            $(this).val(formatRupiah($(this).val(), 'Rp. '));
        });
        // end format rupiah

        //sort_text
        document.querySelectorAll('.sort_text').forEach(function(element) {
            let text = element.textContent.trim();
            let text_sort = text.substring(0, 5);
            if (text.length > 5) {
                element.innerHTML = text_sort + '...';
            }
        });
        //end sort_text

        // Input File Validation
        $(window.document).on('change', 'input[type="file"]', function () {
            clearError();
            let id = $(this).attr('id');
            let size = $(this).data('size') || 0;
            let max_byte = size * 1024;
            const accept = $(this).attr('accept').split(',').map(item => item.trim());
            const fileInput = $(this)[0];

            $.each(fileInput.files, function (index, value) {
                if (value) {
                    if (size !== 0 && value.size > max_byte) {
                        errorBuilder({ [id]: `File ${value.name} melebihi batas maksimal ${size / 1024} MB` });
                        fileInput.value = '';
                        return false;
                    }

                    let fileType = value.type.split('/');
                    let check = false;

                    accept.forEach(function (item) {
                        if (item.endsWith("/*")) {
                            // Match the main type (e.g., image, video) and allow any subtype
                            let mainType = item.split('/')[0];
                            if (fileType[0] === mainType || mainType === '*') {
                                check = true;
                            }
                        } else {
                            // Match both main type and subtype
                            let ty = item.split('/');
                            if (ty.length === 2 && ty[0] === fileType[0] && (ty[1] === fileType[1] || ty[1] === '*')) {
                                check = true;
                            }
                        }
                    });

                    if (!check) {
                        errorBuilder({ [id]: `File ${value.name} tidak sesuai dengan tipe file yang diizinkan` });
                        fileInput.value = '';
                        return false;
                    }
                }
            });
        });
    });
    // End Input File Validation

    // Function to format rupiah
    function formatRupiah(angka, prefix = 'Rp. ') {
        const numberString = angka.toString().replace(/[^0-9]/g, '');
        const split = numberString.split(',');
        const rupiah = split[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        const result = split[1] ? `${rupiah},${split[1]}` : rupiah;
        return prefix + result;
    }
    // End Function to format rupiah

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    // Show small notification
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-toggle="push-menu"]').forEach(function (element) {
            element.addEventListener('click', function () {
                if (document.body.classList.contains('sidebar-collapse')) {
                    localStorage.setItem('sidebar', 'false');
                    $('.small-notify').addClass('hide');
                } else {
                    localStorage.setItem('sidebar', 'true');
                    $('.small-notify').removeClass('hide');
                }
            });
        });

        if (localStorage.getItem('sidebar') === 'true') {
            document.body.classList.add('sidebar-collapse');
            $('.small-notify').addClass('hide');
        } else {
            document.body.classList.remove('sidebar-collapse');
            $('.small-notify').removeClass('hide');
        }
    });
{{--</script>--}}
