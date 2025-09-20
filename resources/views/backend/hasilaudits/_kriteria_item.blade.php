@php
    // Default level
    $level = isset($level) ? $level : 0;

    // Hitung padding: tiap level tambah 20px
    $padding = 20 * $level;
    $collapseId = $parentId . '-' . $item['id'];
@endphp

{{-- Header kriteria --}}
<div class="d-flex align-items-center collapsible py-2 toggle mb-0 collapsed" style="padding-left: {{ $padding }}px;"
    data-bs-toggle="collapse" data-bs-target="#kt_{{ $collapseId }}" aria-expanded="false">

    <div class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
        <i class="ki-duotone ki-minus-square toggle-on text-primary fs-1">
            <span class="path1"></span><span class="path2"></span>
        </i>
        <i class="ki-duotone ki-plus-square toggle-off fs-1">
            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
        </i>
    </div>
    <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
        {{-- Diubah: Menggunakan properti model yang benar --}}
        {{ $item->kode . ' ' . $item->nama }}
    </h4>
</div>

{{-- Isi collapse --}}
<div id="kt_{{ $collapseId }}" class="fs-6 ms-1 collapse">
    {{-- DIUBAH: Menggunakan relasi 'childrenRecursive' --}}
    @if ($item->childrenRecursive && $item->childrenRecursive->isNotEmpty())
        {{-- Kalau punya sub-kriteria, tampilkan secara rekursif --}}
        @foreach ($item->childrenRecursive as $child)
            @include($backend . '.hasilaudits._kriteria_item', [
                'item' => $child,
                'parentId' => $collapseId,
                'level' => $level + 1,
            ])
        @endforeach
    @else
        {{-- Tidak punya sub-kriteria → tampilkan indikator --}}
        @if ($item->indikators && $item->indikators->isNotEmpty())
            <div class="py-2">
                @forelse ($item->indikators as $indikator)
                    @php
                        // --- INI BAGIAN YANG DIPERBAIKI ---
                        // Cek dulu apakah relasi hasilAudit tidak kosong sebelum memanggil first()
                        $hasil = $indikator->hasilAuditForPeriode($auditPeriode->id) ?? null;

                        // Tentukan status berdasarkan data $hasil
                        $status = $hasil ? $hasil->status_terkini : 'BELUM_DIKERJAKAN';
                    @endphp
                    <div class="d-flex justify-content-between align-items-center mb-2"
                        style="padding-left: {{ $padding + 45 }}px">

                        <div class="text-gray-600 fs-6">
                            <span class="bullet me-3"></span>
                            {{ $indikator->nama }}
                            <span class="badge bg-success rounded-pill text-white">{{ $indikator->tipe }}</span>
                        </div>
                        <div class="d-flex align-items-center">

                            @if ($status === 'Selesai')
                                <span class="badge badge-light-success me-3">Selesai</span>
                                <a href="#" class="btn btn-sm btn-light-info btn-action"
                                    data-id="{{ $indikator->id }}" data-action="show-audit"
                                    data-url="{{ route($page->code . '.show', [$indikator->id, 'audit_periode_id' => $auditPeriode->id]) }}"
                                    data-title="Lihat Hasil Final">Lihat Hasil</a>

                            @elseif ($status === 'Revisi')
                                <span class="badge badge-light-danger me-3">Revisi Diperlukan</span>
                                <a href="#" class="btn btn-sm btn-danger btn-action me-2"
                                    data-action="edit-audit"
                                    data-id="{{ $indikator->id }}"
                                    data-url="{{ route($page->code . '.edit', [$indikator->id, 'audit_periode_id' => $auditPeriode->id]) }}"
                                    data-title="Perbaiki Evaluasi">Perbaiki</a>
                                {{-- Setelah perbaikan, auditee bisa mengajukan lagi --}}
                                <button type="button" class="btn btn-sm btn-primary btn-submit-indikator"
                                    data-id="{{ $indikator->id }}"
                                    data-audit-periode-id="{{ $auditPeriode->id }}"
                                    data-status="Diajukan" {{-- Status yang akan di-set saat submit --}}
                                    data-message="Apakah Anda yakin ingin mengajukan indikator ini? Setelah diajukan, Anda tidak dapat mengeditnya sampai diverifikasi.">
                                    Ajukan
                                </button>

                            @elseif ($status === 'Diajukan')
                                <span class="badge badge-light-info me-3">Diajukan</span> {{-- Mengubah warna ke info untuk diajukan --}}
                                <a href="#" class="btn btn-sm btn-light-info btn-action"
                                    data-id="{{ $indikator->id }}" data-action="show-audit"
                                    data-url="{{ route($page->code . '.show', [$indikator->id, 'audit_periode_id' => $auditPeriode->id]) }}"
                                    data-title="Lihat Isian Anda">Lihat</a>
                                {{-- Mungkin tambahkan tombol "Batalkan Pengajuan" jika diperlukan --}}

                            @elseif ($status === 'Draft')
                                <span class="badge badge-light-warning me-3">Draft</span>
                                <a href="#" class="btn btn-sm btn-warning btn-action me-2"
                                    data-title="Formulir Evaluasi Diri" data-action="edit-audit"
                                    data-id="{{ $indikator->id }}"
                                    data-url="{{ route($page->code . '.edit', [$indikator->id, 'audit_periode_id' => $auditPeriode->id]) }}">Lanjutkan</a>
                                <button type="button" class="btn btn-sm btn-primary btn-submit-indikator"
                                    data-id="{{ $indikator->id }}"
                                    data-audit-periode-id="{{ $auditPeriode->id }}"
                                    data-status="Diajukan"
                                    data-message="Apakah Anda yakin ingin mengajukan indikator ini? Setelah diajukan, Anda tidak dapat mengeditnya sampai diverifikasi.">
                                    Ajukan
                                </button>

                            @else {{-- Status 'BELUM_DIKERJAKAN' (atau NULL) --}}
                                <span class="badge badge-light me-3">Belum Dikerjakan</span>
                                <a href="#" class="btn btn-sm btn-primary btn-action"
                                    data-title="Formulir Evaluasi Diri" data-action="edit-audit"
                                    data-id="{{ $indikator->id }}"
                                    data-url="{{ route($page->code . '.edit', [$indikator->id, 'audit_periode_id' => $auditPeriode->id]) }}">Kerjakan</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted fst-italic" style="padding-left: {{ $padding + 45 }}px">Tidak ada indikator
                        untuk kriteria ini.</div>
                @endforelse
            </div>
        @else
            <div class="text-muted ps-5 mb-2">Tidak ada indikator.</div>
        @endif
    @endif
</div>

@prepend('js')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.btn-submit-indikator', function() {
                const indikatorId = $(this).data('id');
                const auditPeriodeId = $(this).data('audit-periode-id');
                const targetStatus = $(this).data('status');
                const confirmMessage = $(this).data('message');

                Swal.fire({
                    title: 'Konfirmasi Pengajuan',
                    text: confirmMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ajukan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tampilkan loader SweetAlert2 saat AJAX diproses
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Lakukan AJAX call untuk mengubah status indikator
                        $.ajax({
                            url: '{{ route($page->code . '.update-status-indikator') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                indikator_id: indikatorId,
                                audit_periode_id: auditPeriodeId,
                                status: targetStatus
                            },
                            success: function(response) {
                                Swal.close(); // Tutup loader

                                if (response.status) {
                                    Swal.fire(
                                        'Berhasil!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        location.reload(); // Refresh halaman setelah sukses
                                    });
                                } else {
                                    Swal.fire(
                                        'Gagal!',
                                        response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr) {
                                Swal.close(); // Tutup loader

                                Swal.fire(
                                    'Terjadi Kesalahan!',
                                    'Gagal mengajukan indikator. Silakan coba lagi.',
                                    'error'
                                );
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endprepend