<?php

namespace App\Http\Controllers\Backend\Laporan;

use App\Http\Controllers\Controller;
use App\Models\HasilAudit;
use App\Models\Kriteria;
use App\Models\Unit;
use Illuminate\Http\Request;

class RingkasanStandarController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $id = $id ?? $request->get('id');

        if ($request->ajax()) {

            $query = HasilAudit::with([
                'indikator.kriteria:id,kode,nama',
                'auditPeriode.instrumenTemplate.lembagaAkreditasi',
            ])
                ->where('status_terkini', '!=', 'Draft');

            // Jika lembaga dipilih → filter
            if (! empty($id)) {
                $query->whereHas('auditPeriode.instrumenTemplate', function ($q) use ($id) {
                    $q->where('lembaga_akreditasi_id', $id);
                });
            }

            $all = $query->get()
                ->filter(fn ($h) => $h->indikator && $h->indikator->kriteria);

            $standarBermasalah = $all
                ->groupBy(fn ($h) => $h->indikator->kriteria->id)
                ->map(function ($items) {

                    $first = $items->first();
                    $kriteria = $first->indikator->kriteria;
                    $lembaga = optional($first->auditPeriode->instrumenTemplate->lembagaAkreditasi)->singkatan;
                    $threshold = ($lembaga === 'LAMEMBA') ? 1.0 : 3.0;

                    $notMet = $items->filter(function ($i) use ($threshold) {
                        $skor = floatval($i->skor_final);

                        return $i->status_terkini !== 'Selesai'
                            || ($i->skor_final !== null && $skor < $threshold);
                    })->count();

                    return [
                        'id' => $kriteria->id,
                        'kode' => $kriteria->kode,
                        'nama_kriteria' => $kriteria->nama,
                        'lembaga' => $lembaga,
                        'total_not_met' => $notMet,
                        'total_dinilai' => $items->count(),
                    ];
                })
                ->sortByDesc('total_not_met')
                ->values();

            return datatables()->of($standarBermasalah)
                ->addColumn('action', fn ($d) => '<button class="btn btn-sm btn-light-info btn-heatmap"
                                                data-kriteria-id="'.$d['id'].'"
                                                title="Detail Prodi Bermasalah">
                                                        <i class="fa fa-eye"></i></button>'
                )
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make(true);
        }

        // Dropdown Filter
        $filterOptions = \App\Models\LembagaAkreditasi::pluck('nama', 'id')
            ->prepend('Pilih Lembaga Akreditasi', '');

        return view('backend.ringkasanstandars.index', compact('filterOptions', 'id'));
    }

    public function show($id)
    {
        $hasil = HasilAudit::with([
            'auditPeriode.unit:id,nama',
            'auditPeriode.instrumenTemplate.lembagaAkreditasi',
            'indikator:id,kriteria_id',
        ])
            ->whereHas('indikator', fn ($q) => $q->where('kriteria_id', $id))
            ->where('status_terkini', '!=', 'Draft') // konsisten dg filter global
            ->get();

        $kriteria = Kriteria::find($id);

        // kumpulkan semua unit yang pernah dinilai untuk kriteria ini
        $units = $hasil->map(fn ($h) => $h->auditPeriode->unit)
            ->unique('id')
            ->values();

        $data = $units->map(function ($u) use ($hasil) {

            // semua hasil untuk unit ini & kriteria ini
            $rows = $hasil->filter(fn ($h) => $h->auditPeriode->unit->id === $u->id);

            if ($rows->isEmpty()) {
                return [
                    'unit' => $u->nama,
                    'status' => 'none',
                    'not_met' => 0,
                    'total' => 0,
                ];
            }

            // pilih sample untuk ambil lembaga (anggap sama per periode/template)
            $sample = $rows->first();
            $lembaga = optional($sample->auditPeriode->instrumenTemplate->lembagaAkreditasi)->singkatan;
            $threshold = ($lembaga === 'LAMEMBA') ? 1.0 : 3.0;

            // hitung kategori per indikator
            $count_total = $rows->count();
            $count_selesai = $rows->where('status_terkini', 'Selesai')->count();
            $count_fail = $rows->filter(function ($h) use ($threshold) {
                return $h->status_terkini === 'Selesai'
                    && ! is_null($h->skor_final)
                    && floatval($h->skor_final) < $threshold;
            })->count();
            $count_pending = $rows->filter(fn ($h) => $h->status_terkini !== 'Selesai')->count();

            // not_met: indikator yang belum terpenuhi (final gagal) + indikator belum selesai
            $not_met = $count_fail + $count_pending;

            // Tentukan status dengan prioritas
            if ($count_total === 0) {
                $status = 'none';
            } elseif ($count_fail > 0) {
                $status = 'fail';
            } elseif ($count_pending > 0) {
                // tidak ada fail, tapi masih ada indikator belum selesai
                $status = 'warn';
            } elseif ($count_selesai === $count_total && $count_fail === 0) {
                $status = 'ok';
            } else {
                // fallback
                $status = 'warn';
            }

            return [
                'unit_id' => $u->id,
                'unit' => $u->nama,
                'status' => $status,
                'not_met' => $not_met,
                'total' => $count_total,
                'count_selesai' => $count_selesai,
                'count_fail' => $count_fail,
                'count_pending' => $count_pending,
            ];
        });

        return response()->json([
            'kriteria' => $kriteria,
            'result' => $data->values(),
        ]);
    }

    public function detailIndikator($kriteriaId, $unitId)
    {
        $hasil = HasilAudit::with([
            'indikator:id,kriteria_id,nama,tipe',
            'auditPeriode:id,unit_id,instrumen_template_id',
            'auditPeriode.instrumenTemplate.lembagaAkreditasi',
            'auditPeriode.instrumenTemplate.templateIndikators:bobot,id',
        ])
            ->whereHas('indikator', fn ($q) => $q->where('kriteria_id', $kriteriaId))
            ->whereHas('auditPeriode', fn ($q) => $q->where('unit_id', $unitId))
            ->where('status_terkini', '!=', 'Draft')
            ->get();

        $unit = Unit::find($unitId);
        $kriteria = Kriteria::find($kriteriaId);

        // Tentukan threshold lembaga (ambil dari salah satu hasil)
        $sample = $hasil->first();
        $lembaga = optional($sample->auditPeriode->instrumenTemplate->lembagaAkreditasi)->singkatan;
        $threshold = ($lembaga === 'LAMEMBA') ? 1.0 : 3.0;

        $data = $hasil->map(function ($h) use ($threshold) {

            $skor = floatval($h->skor_final);
            $status = $h->status_terkini;

            if ($status === 'Selesai' && $skor >= $threshold) {
                $class = 'ok';
            } elseif ($status === 'Selesai' && $skor < $threshold) {
                $class = 'fail';
            } else {
                $class = 'warn';
            }

            return [
                'indikator' => $h->indikator->nama,
                'tipe' => $h->indikator->tipe,
                'bobot' => $h->auditPeriode->instrumenTemplate->templateIndikators->firstWhere('id', $h->indikator->id)->bobot ?? null,
                'skor_final' => $h->skor_final,
                'status' => $status,
                'class' => $class,
            ];
        });

        return response()->json([
            'kriteria' => $kriteria,
            'unit' => $unit,
            'threshold' => $threshold,
            'indikators' => $data,
        ]);
    }
}
