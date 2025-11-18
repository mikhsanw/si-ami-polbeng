<?php

namespace App\Http\Controllers\Backend\Laporan;

use App\Http\Controllers\Controller;
use App\Models\AuditPeriode;
use Illuminate\Http\Request;

class RingkasanUnitController extends Controller
{
    public function index(Request $request)
    {
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
                    $status = $data->hasilaudits()->whereNotIn('status_terkini', ['Draft'])->pluck('status_terkini');
                    if ($status->isEmpty()) {
                        return '<span class="badge badge-light">Belum Dikerjakan</span>';
                    } elseif ($status->count() === 1 && $status->first() === 'Selesai') {
                        return '<span class="badge badge-light-success">Selesai</span>';
                    } elseif ($status->contains('Revisi')) {
                        return '<span class="badge badge-light-danger">Revisi Diperlukan</span>';
                    } elseif ($status->contains('Diajukan')) {
                        return '<span class="badge badge-light-warning">Menunggu Validasi</span>';
                    } else {
                        return '<span class="badge badge-light">Dalam Proses</span>';
                    }
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
        $data = AuditPeriode::with(['unit', 'penugasanAuditors.user'])
            ->withWhereHas('hasilAudits', function ($query) {
                $query->where('status_terkini', '!=', 'Draft');
            })
            ->where('id', $id)
            ->firstOrFail();

        return view('backend.ringkasanunits.show', compact('data'));
    }
}
