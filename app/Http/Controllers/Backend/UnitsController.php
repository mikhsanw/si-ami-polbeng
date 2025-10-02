<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitsController extends Controller
{
    public function index(Request $request, $id = null)
    {
        if ($request->ajax()) {
            $user = $request->user();
            $data = $id != null ? $this->model::with('user')->where('parent_id', $id)->get() : $this->model::with('user')->whereNull('parent_id')->get();

            return datatables()->of($data)
                ->addColumn('sub', function ($q) {
                    if ($q->tipe == 'Jurusan') {
                        $kelola = '<div style="text-align: center;"><a href="'.route($this->code.'.index', ['id' => $q->id]).'" class="text-info"><i class="fa fa-share text-info"></i></a></div>';
                    } else {
                        $kelola = '<div style="text-align: center;"><i class="fa fa-minus text-secondary"></i></div>';
                    }

                    return $kelola ?? null;
                })
                ->addColumn('action', function ($data) use ($user) {
                    $button = '';
                    $button .= '<button type="button" class="btn-action btn btn-sm btn-light-primary" data-title="Detail" data-action="detail" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Tampilkan"><i class="fa fa-eye text-info"></i></button>';
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
                ->rawColumns(['sub', 'action'])
                ->make();
        }

        return view($this->view.'.index', compact('id'));
    }

    public function create()
    {
        $data = [
            'user_id' => \App\Models\User::pluck('name', 'id'),
            'tipe' => config('master.content.unit.tipe'),
        ];

        return view($this->view.'.form', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'tipe' => 'required|string',
            'parent_id' => 'nullable|exists:units,id',
            'user_id' => 'nullable|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validatedData = $validator->validated();

            $this->model::create($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil disimpan.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error saat menyimpan data: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server. Data gagal disimpan.',
            ], 500);
        }
    }

    public function show($id)
    {
        $data = $this->model::find($id);

        return view($this->view.'.show', compact('data'));
    }

    public function edit($id)
    {
        $data = [
            'data' => $this->model::find($id),
            'user_id' => \App\Models\User::pluck('name', 'id'),
            'tipe' => config('master.content.unit.tipe'),
        ];

        return view($this->view.'.form', $data);
    }

    public function update(Request $request, $id)
    {
        //
        $rules = [
            'nama' => 'required|string|max:255',
            'tipe' => 'required|string',
            'parent_id' => 'nullable|exists:units,id',
            'user_id' => 'nullable|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $data = $this->model::findOrFail($id);
            $validatedData = $validator->validated();

            $data->update($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diperbarui.',
            ], 200);

        } catch (\Exception $e) {

            Log::error('Error saat memperbarui data: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server. Data gagal diperbarui.',
            ], 500);
        }
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
