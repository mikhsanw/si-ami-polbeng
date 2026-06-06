<x-app-layout>
    <x-slot name="title">Performa Auditor: {{ $auditor->name }}</x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ url(config('master.app.url.backend') . '/performaauditors') }}" class="btn btn-light">
            <i class="ki-duotone ki-arrow-left fs-4"><span class="path1"></span><span class="path2"></span></i>
            Kembali
        </a>
    </div>

    {{-- Profil Auditor --}}
    <div class="card mb-6">
        <div class="card-body d-flex align-items-center gap-5 py-5">
            <div class="symbol symbol-60px">
                <span class="symbol-label bg-light-primary">
                    <i class="ki-duotone ki-user fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                </span>
            </div>
            <div>
                <h3 class="mb-1">{{ $auditor->name }}</h3>
                <span class="text-muted fs-6">{{ $auditor->email }}</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         RINGKASAN METRIK PERFORMA BERDASARKAN DATA
    ═══════════════════════════════════════════════════════════ --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-duotone ki-chart-line-up-2 fs-4 me-2 text-primary">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Metrik Performa Berdasarkan Data
            </h3>
            <div class="card-toolbar">
                <span class="badge badge-light-primary">{{ $totalPenugasan }} Penugasan</span>
            </div>
        </div>
        <div class="card-body py-6">
            @php
                $m = $metrikTotal;
                $skor = $m['skor_keseluruhan'];
                $warnaTotal = $skor >= 80 ? 'success' : ($skor >= 60 ? 'primary' : ($skor >= 40 ? 'warning' : 'danger'));
                $labelTotal = $skor >= 80 ? 'Sangat Baik' : ($skor >= 60 ? 'Baik' : ($skor >= 40 ? 'Cukup' : 'Perlu Perhatian'));
            @endphp

            <div class="row g-6 align-items-center">

                {{-- Skor Keseluruhan (bulat besar) --}}
                <div class="col-md-3 text-center">
                    <div class="position-relative d-inline-block mb-2">
                        <svg width="120" height="120" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#f5f5f5" stroke-width="12"/>
                            <circle cx="60" cy="60" r="50" fill="none"
                                stroke="{{ $skor >= 80 ? '#50cd89' : ($skor >= 60 ? '#009ef7' : ($skor >= 40 ? '#ffc700' : '#f1416c')) }}"
                                stroke-width="12"
                                stroke-dasharray="{{ round($skor * 3.14, 1) }} 314"
                                stroke-dashoffset="0"
                                transform="rotate(-90 60 60)"
                                stroke-linecap="round"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <div class="fw-bolder fs-2 lh-1">{{ $skor }}%</div>
                            <div class="text-muted fs-8">skor</div>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-light-{{ $warnaTotal }} fs-7">{{ $labelTotal }}</span>
                    </div>
                </div>

                {{-- 3 Metrik --}}
                <div class="col-md-9">
                    <div class="row g-5">

                        {{-- % Responsivitas --}}
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <div class="symbol symbol-35px me-3">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="ki-duotone ki-check-circle fs-4 text-primary">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-bold fs-7">% Responsivitas</div>
                                    <div class="text-muted fs-8">Indikator direspon</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="progress flex-grow-1" style="height:10px">
                                    <div class="progress-bar rounded"
                                        style="width:{{ $m['pct_responsivitas'] }}%;
                                               background:{{ $m['pct_responsivitas'] >= 80 ? '#50cd89' : ($m['pct_responsivitas'] >= 60 ? '#009ef7' : ($m['pct_responsivitas'] >= 40 ? '#ffc700' : '#f1416c')) }}">
                                    </div>
                                </div>
                                <span class="fw-bolder fs-5 w-50px text-end">{{ $m['pct_responsivitas'] }}%</span>
                            </div>
                        </div>

                        {{-- % Kecepatan --}}
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <div class="symbol symbol-35px me-3">
                                    <span class="symbol-label bg-light-success">
                                        <i class="ki-duotone ki-time fs-4 text-success">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-bold fs-7">% Kecepatan Respon</div>
                                    <div class="text-muted fs-8">
                                        @if($m['avg_hari_respon'] !== null)
                                            Rata-rata {{ $m['avg_hari_respon'] }} hari
                                        @else
                                            Belum ada data
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="progress flex-grow-1" style="height:10px">
                                    <div class="progress-bar rounded"
                                        style="width:{{ $m['pct_kecepatan'] }}%;
                                               background:{{ $m['pct_kecepatan'] >= 80 ? '#50cd89' : ($m['pct_kecepatan'] >= 60 ? '#009ef7' : ($m['pct_kecepatan'] >= 40 ? '#ffc700' : '#f1416c')) }}">
                                    </div>
                                </div>
                                <span class="fw-bolder fs-5 w-50px text-end">{{ $m['pct_kecepatan'] }}%</span>
                            </div>
                        </div>

                        {{-- % Catatan --}}
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <div class="symbol symbol-35px me-3">
                                    <span class="symbol-label bg-light-info">
                                        <i class="ki-duotone ki-message-edit fs-4 text-info">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-bold fs-7">% Kelengkapan Catatan</div>
                                    <div class="text-muted fs-8">Aksi disertai catatan</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="progress flex-grow-1" style="height:10px">
                                    <div class="progress-bar rounded"
                                        style="width:{{ $m['pct_catatan'] }}%;
                                               background:{{ $m['pct_catatan'] >= 80 ? '#50cd89' : ($m['pct_catatan'] >= 60 ? '#009ef7' : ($m['pct_catatan'] >= 40 ? '#ffc700' : '#f1416c')) }}">
                                    </div>
                                </div>
                                <span class="fw-bolder fs-5 w-50px text-end">{{ $m['pct_catatan'] }}%</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rincian Per Periode Audit --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Rincian Per Periode Audit</h3>
        </div>
        <div class="card-body py-4">
            @if($periodeDetail->isEmpty())
                <div class="text-muted text-center py-5">Belum ada penugasan audit.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="text-muted fw-bold fs-7 text-uppercase">
                            <tr>
                                <th>No</th>
                                <th>Unit</th>
                                <th>Tahun Akademik</th>
                                <th class="text-center">Total Indikator</th>
                                <th class="text-center">% Responsivitas</th>
                                <th class="text-center">% Kecepatan</th>
                                <th class="text-center">% Catatan</th>
                                <th class="text-center">Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periodeDetail as $i => $p)
                                @php
                                    $warna = $p->skor_keseluruhan >= 80 ? 'success'
                                           : ($p->skor_keseluruhan >= 60 ? 'primary'
                                           : ($p->skor_keseluruhan >= 40 ? 'warning' : 'danger'));
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $p->unit }}</td>
                                    <td>{{ $p->tahun_akademik }}</td>
                                    <td class="text-center">{{ $p->total_hasil }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-light-{{ $p->pct_responsivitas >= 60 ? 'success' : 'danger' }}">
                                            {{ $p->pct_responsivitas }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-{{ $p->pct_kecepatan >= 60 ? 'success' : 'warning' }}">
                                            {{ $p->pct_kecepatan }}%
                                        </span>
                                        @if($p->avg_hari_respon !== null)
                                            <div class="text-muted fs-8">{{ $p->avg_hari_respon }} hari</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-{{ $p->pct_catatan >= 60 ? 'success' : 'warning' }}">
                                            {{ $p->pct_catatan }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-{{ $warna }} fs-7">
                                            {{ $p->skor_keseluruhan }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Riwayat Penilaian Tersimpan oleh Pimpinan --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Penilaian oleh Pimpinan</h3>
            <div class="card-toolbar">
                @can($page->code . ' create')
                    <a href="#" class="btn btn-primary btn-sm btn-action"
                        data-title="Rekam Penilaian"
                        data-url="{{ '/' . config('master.app.url.backend') . '/performaauditors/penilaian/create?auditor_id=' . $auditor->id }}">
                        <i class="ki-duotone ki-plus fs-4"><span class="path1"></span><span class="path2"></span></i>
                        Rekam Penilaian
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body py-4">
            @if($penilaians->isEmpty())
                <div class="text-muted text-center py-5">Belum ada penilaian tersimpan.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="text-muted fw-bold fs-7 text-uppercase">
                            <tr>
                                <th>No</th>
                                <th>Periode Audit</th>
                                <th>Dinilai oleh</th>
                                <th class="text-center">% Responsivitas</th>
                                <th class="text-center">% Kecepatan</th>
                                <th class="text-center">% Catatan</th>
                                <th class="text-center">Skor Keseluruhan</th>
                                <th>Catatan Pimpinan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penilaians as $i => $p)
                                @php
                                    $warna = $p->skor_keseluruhan >= 80 ? 'success'
                                           : ($p->skor_keseluruhan >= 60 ? 'primary'
                                           : ($p->skor_keseluruhan >= 40 ? 'warning' : 'danger'));
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ optional($p->auditPeriode->unit)->nama ?? '?' }} — {{ $p->auditPeriode->tahun_akademik ?? '-' }}</td>
                                    <td>{{ $p->penilai->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-light-primary">{{ $p->pct_responsivitas }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-success">{{ $p->pct_kecepatan }}%</span>
                                        @if($p->avg_hari_respon !== null)
                                            <div class="text-muted fs-8">{{ $p->avg_hari_respon }} hari</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-info">{{ $p->pct_catatan }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-{{ $warna }} fs-7 fw-bold">
                                            {{ $p->skor_keseluruhan }}%
                                        </span>
                                        <div class="text-muted fs-8">{{ $p->label_skor }}</div>
                                    </td>
                                    <td class="text-truncate" style="max-width:180px" title="{{ $p->catatan }}">
                                        {{ $p->catatan ?? '-' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            @can($page->code . ' edit')
                                                <button type="button" class="btn btn-sm btn-light-warning btn-action"
                                                    data-title="Edit Penilaian"
                                                    data-action="edit"
                                                    data-id="{{ $p->id }}"
                                                    data-url="{{ config('master.app.url.backend') . '/performaauditors/penilaian' }}">
                                                    <i class="ki-duotone ki-pencil fs-5"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                            @endcan
                                            @can($page->code . ' delete')
                                                <button type="button" class="btn btn-sm btn-light-danger btn-action"
                                                    data-title="Hapus Penilaian"
                                                    data-action="delete"
                                                    data-id="{{ $p->id }}"
                                                    data-url="{{ config('master.app.url.backend') . '/performaauditors/penilaian' }}">
                                                    <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @prepend('js')
        <script src="{{ asset('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
        <script src="{{ asset('js/jquery-crud.js') }}"></script>
    @endprepend
</x-app-layout>
