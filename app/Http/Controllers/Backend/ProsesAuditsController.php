<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProsesAuditsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id = null)
    {
        $user = $request->user();
        if ($request->ajax()) {
            $id = $id ?? $request->get('id');
            $data = $this->model::with(['indikator', 'logAktivitasAudit', 'auditPeriode'])
                ->where('status_terkini', '!=', 'Draft')
                ->whereHas('auditPeriode', function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->where(function ($q) use ($user) {
                    $q->whereHas('auditPeriode.penugasanAuditors', fn ($query) => $query->where('user_id', $user->id))
                        ->orWhereHas('auditPeriode.unit', fn ($query2) => $query2->where('user_id', $user->id));
                })
                ->latest('updated_at')
                ->get();

            return datatables()->of($data)
                ->addColumn('action', function ($data) use ($user) {
                    $button = '';
                    $button .= '<button type="button" class="btn-action btn btn-sm btn-light-primary" data-title="Detail" data-action="show-audit" data-url="'.route($this->code.'.show', [$data->id]).'" data-id="'.$data->id.'" title="Tampilkan"><i class="fa fa-eye text-info"></i></button>';
                    if (in_array('Super Admin', $user->getRoleNames()->toArray() ?? [])) {
                        if (auth()->user()->hasRole('Super Admin')) {
                            $button .= '<a type="button" class="btn btn-sm btn-light-warning btn-action" data-title="Edit" data-action="edit" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Edit"> <i class="fa fa-edit text-warning"></i> </a> ';
                            $button .= '<button type="button" class="btn-action btn btn-sm btn-light-danger" data-title="Delete" data-action="delete" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Delete"> <i class="fa fa-trash text-danger"></i> </button>';
                        }
                    } else {
                        if ($user->hasPermissionTo($this->code.' edit')) {
                            $button .= '<a type="button" class="btn btn-sm btn-light-warning btn-action" data-title="Edit" data-action="edit" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Edit"> <i class="fa fa-edit text-warning"></i> </a> ';
                        }
                        if ($user->hasPermissionTo($this->code.' delete')) {
                            $button .= '<button type="button" class="btn-action btn btn-sm btn-light-danger" data-title="Delete" data-action="delete" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Delete"> <i class="fa fa-trash text-danger"></i> </button>';
                        }
                    }

                    return "<div class='btn-group'>".$button.'</div>';
                })
                ->editColumn('status_terkini', function ($data) {
                    return $data->status_terkini === 'Selesai' ? '<span class="badge badge-light-success">Selesai</span>' : ($data->status_terkini === 'Revisi' ? '<span class="badge badge-light-danger">Revisi Diperlukan</span>' : ($data->status_terkini === 'Diajukan' ? '<span class="badge badge-light-warning">Menunggu Validasi</span>' : '<span class="badge badge-light">Belum Dikerjakan</span>'));
                })
                ->addIndexColumn()
                ->rawColumns(['action', 'status_terkini'])
                ->make();
        }
        $data = \App\Models\AuditPeriode::orderBy('created_at')->whereHas('penugasanAuditors', fn ($query) => $query->where('user_id', $user->id))->orWhereHas('unit', fn ($query2) => $query2->where('user_id', $user->id))->get()
            ->pluck('periode_unit', 'id')
            ->toArray();
        $filterOptions = ['' => 'Pilih Periode Unit'] + $data;

        return view($this->view.'.index', compact('id', 'filterOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = [
            'data' => $this->model::find($id),
        ];

        return view($this->view.'.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
