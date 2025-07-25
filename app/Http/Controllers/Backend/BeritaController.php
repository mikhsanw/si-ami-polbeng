<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = $request->user();
            $data=$this->model::all();
            return datatables()->of($data)
                ->addColumn('action', function ($data) use ($user) {
                    $button ='';
                    $button .= '<button type="button" class="btn-action btn btn-sm btn-light-primary" data-title="Detail" data-action="show" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Tampilkan"><i class="fa fa-eye text-info"></i></button>';
                    if (in_array('Super Admin', $user->getRoleNames()->toArray() ?? []) ){
                        if (auth()->user()->hasRole('Super Admin')){
                        $button.='<a type="button" class="btn btn-sm btn-light-warning btn-action" data-title="Edit" data-action="edit" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Edit"> <i class="fa fa-edit text-warning"></i> </a> ';
                        $button.='<button type="button" class="btn-action btn btn-sm btn-light-danger" data-title="Delete" data-action="delete" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Delete"> <i class="fa fa-trash text-danger"></i> </button>';
                        }
                    }else{
                        if($user->hasPermissionTo($this->code.' edit')){
                            $button.='<a type="button" class="btn btn-sm btn-light-warning btn-action" data-title="Edit" data-action="edit" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Edit"> <i class="fa fa-edit text-warning"></i> </a> ';
                        }
                        if($user->hasPermissionTo($this->code.' delete')){
                            $button.='<button type="button" class="btn-action btn btn-sm btn-light-danger" data-title="Delete" data-action="delete" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Delete"> <i class="fa fa-trash text-danger"></i> </button>';
                        }
                    }
                    return "<div class='btn-group'>".$button."</div>";
                })
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make();
        }
        return view($this->view.'.index');
    }

    public function create()
    {
        return view($this->view.'.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required',
			'isi' => 'required',
			'tanggal' => 'required',
            'gambar'=>'image|mimes:jpeg,png,jpg|max:2048'
        ]);
        if ($data = $this->model::create($request->all())) {
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $data->file()->create(
                    [
                        'alias' => 'gambar',
                        'data' => [
                            'name' => $file->hashName(), 'disk' => config('filesystems.default'),
                            'target' => Storage::disk(config('filesystems.default'))->putFile($this->code . '/' . date('Y') . '/' . date('m') . '/' . date('d'), $file),
                        ],
                    ]
                );
            }
            $response=[ 'status'=>TRUE, 'message'=>'Data berhasil disimpan'];
        }
        return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal disimpan']);
    }

    public function show($id)
    {
        $data = $this->model::find($id);
        return view($this->view.'.show', compact('data'));
    }

    public function edit($id)
    {
        $data = $this->model::find($id);
        return view($this->view.'.form', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required',
			'isi' => 'required',
			'tanggal' => 'required',
        ]);

        $data=$this->model::find($id);
        if($data->update($request->all())){
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $data->file()->updateOrCreate(
                    ['fileable_id' => $data->id, 'fileable_type' => get_class($data), 'alias' => 'gambar'],
                    [
                        'data' => [
                            'name' => $file->getClientOriginalName(), 'disk' => config('filesystems.default'),
                            'target' => Storage::disk(config('filesystems.default'))->putFile($this->code . '/' . date('Y') . '/' . date('m') . '/' . date('d'), $file),
                        ],
                    ]
                );
            }
            $response=[ 'status'=>TRUE, 'message'=>'Data berhasil disimpan'];
        }
        return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal disimpan']);
    }

    public function delete($id)
    {
        $data=$this->model::find($id);
        return view($this->view.'.delete', compact('data'));
    }

    public function destroy($id)
    {
        $data=$this->model::find($id);
        if($data->delete()){
            $response=[ 'status'=>TRUE, 'message'=>'Data berhasil dihapus'];
        }
        return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal dihapus']);
    }
}
