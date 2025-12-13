<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BeritaAcarasController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = $request->user();
            if ($user->hasRole(['Super Admin', 'Admin', 'Direktur'])) {
                $data = $this->model::with('auditPeriode')->get();
            } else {
                $data = $this->model::with('auditPeriode')
                    ->whereHas('auditPeriode.penugasanAuditors', fn ($query) => $query->where('user_id', $user->id))
                    ->orWhereHas('auditPeriode.unit', fn ($query2) => $query2->where('user_id', $user->id))
                    ->get();
            }

            return datatables()->of($data)
                ->addColumn('action', function ($data) use ($user) {
                    $button = '';
                    $button .= '<button type="button" class="btn-action btn btn-sm btn-light-primary" data-title="Detail" data-action="show" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Tampilkan"><i class="fa fa-eye text-info"></i></button>';
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
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make();
        }

        return view($this->view.'.index');
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin', 'Admin', 'Direktur'])) {
            // Ambil semua audit periode
            $filterOptions = \App\Models\AuditPeriode::orderBy('created_at')
                ->get()
                ->pluck('periode_unit', 'id')
                ->toArray();
        } else {
            // Ambil hanya audit periode yang ditugaskan ke user
            $filterOptions = \App\Models\AuditPeriode::orderBy('created_at')
                ->whereHas('penugasanAuditors', fn ($query) => $query->where('user_id', $user->id))
                ->get()
                ->pluck('periode_unit', 'id')
                ->toArray();
        }
        $data = [
            'audit_periode_id' => $filterOptions,

        ];

        return view($this->view.'.form', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'audit_periode_id' => 'required|'.config('master.regex.json'),
            'catatan' => 'nullable|'.config('master.regex.json'),
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:5000',
        ]);
        if ($data = $this->model::create($request->all())) {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $data->file()->create(
                    [
                        'alias' => 'file berita acara',
                        'data' => [
                            'name' => $file->hashName(), 'disk' => config('filesystems.default'),
                            'target' => Storage::disk(config('filesystems.default'))->putFile($this->code.'/'.date('Y').'/'.date('m').'/'.date('d'), $file),
                        ],
                    ]
                );
            }
            $response = ['status' => true, 'message' => 'Data berhasil disimpan'];
        }

        return response()->json($response ?? ['status' => false, 'message' => 'Data gagal disimpan']);
    }

    public function show($id)
    {
        $data = $this->model::find($id);

        return view($this->view.'.show', compact('data'));
    }

    public function edit($id)
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin', 'Admin', 'Direktur'])) {
            // Ambil semua audit periode
            $filterOptions = \App\Models\AuditPeriode::orderBy('created_at')
                ->get()
                ->pluck('periode_unit', 'id')
                ->toArray();
        } else {
            // Ambil hanya audit periode yang ditugaskan ke user
            $filterOptions = \App\Models\AuditPeriode::orderBy('created_at')
                ->whereHas('penugasanAuditors', fn ($query) => $query->where('user_id', $user->id))
                ->get()
                ->pluck('periode_unit', 'id')
                ->toArray();
        }
        $data = [
            'data' => $this->model::find($id),
            'audit_periode_id' => $filterOptions,

        ];

        return view($this->view.'.form', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'audit_periode_id' => 'required|'.config('master.regex.json'),
            'catatan' => 'nullable|'.config('master.regex.json'),
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:5000',
        ]);

        $data = $this->model::find($id);
        if ($data->update($request->all())) {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                if ($data->file) {
                    $data->file->delete();
                }
                $data->file()->create(
                    [
                        'alias' => 'file berita acara',
                        'data' => [
                            'name' => $file->hashName(), 'disk' => config('filesystems.default'),
                            'target' => Storage::disk(config('filesystems.default'))->putFile($this->code.'/'.date('Y').'/'.date('m').'/'.date('d'), $file),
                        ],
                    ]
                );
            }
            $response = ['status' => true, 'message' => 'Data berhasil disimpan'];
        }

        return response()->json($response ?? ['status' => false, 'message' => 'Data gagal disimpan']);
    }

    public function delete($id)
    {
        $data = $this->model::find($id);

        return view($this->view.'.delete', compact('data'));
    }

    public function destroy($id)
    {
        $data = $this->model::find($id);
        if ($data->delete()) {
            $response = ['status' => true, 'message' => 'Data berhasil dihapus'];
        }

        return response()->json($response ?? ['status' => false, 'message' => 'Data gagal dihapus']);
    }
}
