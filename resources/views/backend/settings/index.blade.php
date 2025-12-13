<x-app-layout>
    <x-slot name="title">
        {{ __('Pengaturan Sistem') }}
    </x-slot>

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                <!-- Header -->
                <div class="card mb-5 shadow-sm">
                    <div class="card-body">
                        <h3 class="fw-bold text-gray-800 mb-0">⚙️ Pengaturan Sistem Aplikasi</h3>
                        <small class="text-muted">Kelola konfigurasi untuk seluruh fungsi AMI POLBENG</small>
                    </div>
                </div>

                <div class="row g-4">

                    <!-- Konfigurasi Sistem -->
                    <div class="col-xl-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light d-flex justify-content-between">
                                <div class="card-title">
                                    <h3 class="fw-bold m-0">Konfigurasi Sistem</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="formSettings">
                                    <div class="mb-3">
                                        <label class="form-label">Tahun Akademik Aktif</label>
                                        <select class="form-select" name="tahun_akademik">
                                            <option value="">Pilih Tahun Akademik</option>
                                            @foreach ($settings['tahun_akademik'] as $key => $ta)
                                                <option value="{{ $key }}"
                                                    {{ isset($settings['tahun_akademik_active']) && $settings['tahun_akademik_active'] == $key ? 'selected' : '' }}>
                                                    {{ $ta }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option>Pilih Status</option>
                                            <option value="1"
                                                {{ isset($settings['status']) && $settings['status'] == 1 ? 'selected' : '' }}>
                                                Aktif</option>
                                            <option value="0"
                                                {{ isset($settings['status']) && $settings['status'] == 0 ? 'selected' : '' }}>
                                                Tidak Aktif</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary" id="btnSaveSettings">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Backup File -->
                    <div class="col-xl-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light d-flex justify-content-between">
                                <div class="card-title">
                                    <h6 class="mb-0">📦 Backup File</h6>
                                </div>
                                <div class="card-toolbar">
                                    <button class="btn btn-sm btn-danger" id="btnBackupFiles">
                                        <i class="fas fa-cloud-upload-alt"></i> Mulai Backup ke Google Drive
                                    </button>

                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Backup file yang tersimpan di server ke Google Drive secara otomatis.
                                </p>
                                <div id="backupStatus" class="text-primary fw-bold small mb-3">
                                </div>

                                <hr>

                                <h6 class="fw-semibold mb-2">Riwayat Backup Terbaru</h6>

                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle small">
                                        <thead>
                                            <tr>
                                                <th>Nama File</th>
                                                <th>Status</th>
                                                <th>Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody id="backupHistoryTable">
                                            @forelse ($backups ?? [] as $b)
                                                <tr>
                                                    <td class="text-truncate" style="max-width: 200px;">
                                                        {{ $b['nama'] }}
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $b['status'] == 'done' ? 'success' : 'warning' }}">
                                                            {{ strtoupper($b['status']) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($b['time'])->format('d M Y H:i') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Belum ada backup
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                </div> <!-- row -->
            </div>
        </div>
    </div>

    @prepend('css')
    @endprepend

    @prepend('js')
        <script>
            $(document).ready(function() {

                // Reset status ketika tahun akademik berubah
                $('select[name="tahun_akademik"]').on('change', function() {
                    $('select[name="status"]').val('');
                });

            });

            // Simpan pengaturan
            $('#formSettings').on('submit', function(e) {
                e.preventDefault();

                let formData = {
                    _token: "{{ csrf_token() }}",
                    tahun_akademik: $('select[name="tahun_akademik"]').val(),
                    status: $('select[name="status"]').val()
                };

                $.ajax({
                    url: "{{ route('settings.update') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        //reload page after 2 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan server'
                        });
                    }
                });
            });


            $('#btnBackupFiles').on('click', function() {

                // tampilkan pesan awal
                $('#backupStatus')
                    .text('Memulai backup...')
                    .removeClass('text-danger text-success')
                    .addClass('text-primary');

                $.ajax({
                    url: "{{ route('settings.backup.files') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {

                        if (res.status === 'success') {
                            $('#backupStatus')
                                .text(res.message + ` (${res.total} file)`)
                                .removeClass('text-primary text-danger')
                                .addClass('text-success');

                            Swal.fire({
                                icon: 'success',
                                title: 'Backup Dimulai',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            $('#backupStatus')
                                .text(res.message)
                                .removeClass('text-primary text-success')
                                .addClass('text-danger');

                            Swal.fire({
                                icon: 'warning',
                                title: 'Tadak Ada File untuk Dibackup',
                                text: res.message,
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#backupStatus')
                            .text('Backup gagal dimulai!')
                            .removeClass('text-primary text-success')
                            .addClass('text-danger');

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan server',
                        });
                    }
                });

            });
        </script>
    @endprepend

</x-app-layout>
