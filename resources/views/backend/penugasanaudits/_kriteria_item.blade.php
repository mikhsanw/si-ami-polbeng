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
            @include($backend . '.'.$page->code.'._kriteria_item', [
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
                                <span class="badge badge-light-danger me-3">Menunggu Perbaikan</span>
                                <a href="#" class="btn btn-sm btn-light-danger btn-action"
                                    data-id="{{ $indikator->id }}" data-action="show-audit"
                                    data-url="{{ route($page->code . '.show', [$indikator->id, 'audit_periode_id' => $auditPeriode->id]) }}"
                                    data-title="Lihat Status Revisi">Lihat</a>
                            @elseif ($status === 'Diajukan')
                                <span class="badge badge-light-warning me-3">Menunggu Validasi</span>
                                <a href="#" class="btn btn-sm btn-primary btn-action"
                                    data-title="Formulir Validasi" data-action="edit-audit"
                                    data-id="{{ $indikator->id }}"
                                    data-url="{{ route($page->code . '.edit', [$indikator->id, 'audit_periode_id' => $auditPeriode->id]) }}">Validasi</a>
                            @else
                                <span class="badge badge-light me-3">Belum Dikerjakan</span>
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
