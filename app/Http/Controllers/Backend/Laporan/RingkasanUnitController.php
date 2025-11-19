<?php

namespace App\Http\Controllers\Backend\Laporan;

use App\Http\Controllers\Controller;
use App\Models\AuditPeriode;
use Illuminate\Http\Request;

class RingkasanUnitController extends Controller
{
    public function index(Request $request)
    {
        // Bersihkan data HasilAudit yang tidak sesuai dengan template indikator
        // $auditPeriodes = \App\Models\AuditPeriode::with('instrumenTemplate.templateIndikators')->get();
        // foreach ($auditPeriodes as $auditPeriode) {
        //     $auditPeriodeId = $auditPeriode->id;

        //     // Dapatkan daftar indikator yang valid dari template indikator
        //     $validIndikatorIds = $auditPeriode->instrumenTemplate->templateIndikators
        //         ->pluck('indikator_id')
        //         ->toArray();

        //     // Hapus hasil audit yang indikatornya tidak terdapat dalam template indikator
        //     $deleted = \App\Models\HasilAudit::where('audit_periode_id', $auditPeriodeId)
        //         ->whereNotIn('indikator_id', $validIndikatorIds)
        //         ->delete();
        // }

        $user = $request->user();
        $id = $request->get('id') ? urldecode($request->get('id')) : null;
        if ($request->ajax()) {
            $data = AuditPeriode::with(['unit', 'penugasanAuditors.user'])
                ->where('tahun_akademik', 'LIKE', '%'.$id.'%')
                ->get();

            return datatables()->of($data)
                ->addColumn('auditor', function ($data) {
                    return $data->penugasanAuditors->map(function ($pa) {
                        return $pa->user->name ?? '-';
                    })->implode(', ');
                })
                ->addColumn('status_audit', function ($data) {

                    $counts = $data->hasilAudits()
                        ->whereIn('status_terkini', ['Selesai', 'Revisi', 'Diajukan'])
                        ->selectRaw('status_terkini, COUNT(*) AS total')
                        ->groupBy('status_terkini')
                        ->pluck('total', 'status_terkini');

                    // Tentukan status utama
                    if ($counts->isEmpty()) {
                        $badge = '<span class="badge badge-light">Belum Dikerjakan</span>';
                    } elseif ($counts->has('Revisi')) {
                        $badge = '<span class="badge badge-light-danger">Revisi Diperlukan</span>';
                    } elseif ($counts->has('Diajukan')) {
                        $badge = '<span class="badge badge-light-warning">Menunggu Validasi</span>';
                    } elseif ($counts->has('Selesai') && $counts->count() === 1) {
                        $badge = '<span class="badge badge-light-success">Selesai</span>';
                    } else {
                        $badge = '<span class="badge badge-light">Dalam Proses</span>';
                    }

                    return $badge.' <button 
                        type="button" 
                        class="btn btn-sm btn-light-info btn-chart"
                        data-selesai="'.$counts->get('Selesai', 0).'"
                        data-diajukan="'.$counts->get('Diajukan', 0).'"
                        data-revisi="'.$counts->get('Revisi', 0).'"
                        title="Lihat Grafik">
                        <i class="fa fa-chart-pie"></i>
                    </button>';
                })
                ->addColumn('indikator_terisi', function ($data) {
                    $totalIndikator = $data->instrumenTemplate->templateIndikators->count();
                    $filledIndikatorsCount = $data->hasilAudits()
                        ->whereIn('status_terkini', [
                            config('master.content.hasil_audit.status_terkini.Diajukan'),
                            config('master.content.hasil_audit.status_terkini.Revisi'),
                            config('master.content.hasil_audit.status_terkini.Selesai'),
                        ])
                        ->count();

                    return $filledIndikatorsCount.' / '.$totalIndikator;
                })
                ->addColumn('persentase_terisi', function ($data) {
                    $totalIndikator = $data->instrumenTemplate->templateIndikators->count();
                    if ($totalIndikator == 0) {
                        return '0%';
                    }
                    $filledIndikatorsCount = $data->hasilAudits()
                        ->whereIn('status_terkini', [
                            config('master.content.hasil_audit.status_terkini.Diajukan'),
                            config('master.content.hasil_audit.status_terkini.Revisi'),
                            config('master.content.hasil_audit.status_terkini.Selesai'),
                        ])
                        ->count();

                    return round(($filledIndikatorsCount / $totalIndikator) * 100).'%';
                })
                ->addColumn('action', function ($data) {
                    $button = '';
                    $button .= '<button type="button" class="btn-action btn btn-sm btn-light-primary" data-title="Detail" data-action="show-detail" data-url="'.url($this->url.'/'.$data->id.'/show').'" data-id="'.$data->id.'" title="Tampilkan"><i class="fa fa-eye text-info"></i></button>';

                    return "<div class='btn-group'>".$button.'</div>';
                })
                ->addIndexColumn()
                ->rawColumns(['action', 'indikator_terisi', 'status_audit'])
                ->make();
        }

        $data = \App\Models\AuditPeriode::latest()->get()
            ->pluck('tahun_akademik', 'tahun_akademik')
            ->toArray();
        $filterOptions = ['' => 'Pilih Periode'] + $data;

        return view('backend.ringkasanunits.index', compact('filterOptions', 'id'));
    }

    public function show($id)
    {
        $data = AuditPeriode::with([
            'unit',
            'penugasanAuditors.user',
            'hasilAudits' => fn ($q) => $q->where('status_terkini', '!=', 'Draft'),
        ])
            ->findOrFail($id);

        return view('backend.ringkasanunits.show', compact('data'));
    }
}
