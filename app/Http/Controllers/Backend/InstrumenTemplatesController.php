<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\TemplateKriteria;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class InstrumenTemplatesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = $request->user();
            $data=$this->model::with('lembagaAkreditasi')->get();
            return datatables()->of($data)
                ->addColumn('lembagaAkreditasi', function($data) {
                    return $data->lembagaAkreditasi ? $data->lembagaAkreditasi->nama : 'N/A';
                })
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('rancangan', function ($data) {
                    return '<button type="button" class="btn-action btn btn-sm btn-light-primary" data-title="Lihat Rancangan" data-action="rancangan" data-url="'.$this->url.'/edit-rancangan/'.$data->id.'" data-id="'.$data->id.'" title="Lihat Rancangan"><i class="fa fa-gear text-info"></i></button>';
                })
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
                ->rawColumns(['action', 'lembagaAkreditasi', 'is_active', 'rancangan'])
                ->make();
        }
        return view($this->view.'.index');
    }

    public function create()
    {
		$data=[
			'lembaga_akreditasi_id'	=> \App\Models\LembagaAkreditasi::pluck('nama','id'),
		];

        return view($this->view.'.form' ,$data);
    }

    public function store(Request $request)
    {
        $request->validate([
					'nama' => 'required|'.config('master.regex.json'),
					'deskripsi' => 'required|'.config('master.regex.json'),
					'is_active' => 'nullable|'.config('master.regex.json'),
					'lembaga_akreditasi_id' => 'required|'.config('master.regex.json'),
        ]);
        if ($data = $this->model::create($request->all())) {
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
        $data=[
            'data'    => $this->model::find($id),
			'lembaga_akreditasi_id'	=> \App\Models\LembagaAkreditasi::pluck('nama','id'),

        ];
        return view($this->view.'.form', $data);
    }

    public function editRancangan($id)
    {
        $data=[
            'data'    => $this->model::find($id),
			'template_kriterias'	=> \App\Models\TemplateKriteria::where('instrumen_template_id', $id)->get(),
            'kriterias' => \App\Models\Kriteria::with(['childrenRecursive'])->whereNull('parent_id')->get()
        ];
        return view($this->view.'.form-rancangan', $data);
    }

    public function updateRancangan(Request $request,$id)
    {
        $validated = $request->validate([
            'kriteria'   => 'required|array',
            'kriteria.*' => 'exists:kriterias,id',
            'bobot'      => 'required|array',
            'bobot.*'    => 'nullable|numeric|min:0',
        ], [
            'kriteria.required' => 'Kriteria harus dipilih.',
            'bobot.required'    => 'Bobot harus diisi untuk setiap kriteria yang dipilih.',
            'bobot.*.numeric'   => 'Bobot harus berupa angka.',
            'bobot.*.min'       => 'Bobot tidak boleh kurang dari 0.',
        ]);

        DB::beginTransaction();
        try {
            // Ambil model template instrumen berdasarkan ID
            $instrumenTemplate = $this->model::findOrFail($id);
            
            $kriteriaToSync = [];
            
            if (!empty($validated['kriteria'])) {
                // Loop pada kriteria yang dicentang oleh pengguna
                foreach ($validated['kriteria'] as $kriteriaId) {
                    $kriteria = \App\Models\Kriteria::with('parentRecursive')->find($kriteriaId);
                    if (!$kriteria) continue;

                    // Simpan kriteria yang dipilih beserta bobotnya
                    $bobot = $validated['bobot'][$kriteriaId] ?? 0;
                    $kriteriaToSync[$kriteria->id] = ['bobot' => $bobot];

                    // Telusuri semua induknya ke atas dan tambahkan ke daftar sinkronisasi
                    $parent = $kriteria->parentRecursive;
                    while ($parent) {
                        if (!array_key_exists($parent->id, $kriteriaToSync)) {
                            $kriteriaToSync[$parent->id] = ['bobot' => null];
                        }
                        $parent = $parent->parentRecursive;
                    }
                }
            }
            
            // --- SINKRONISASI MANUAL (Alternatif untuk sync()) ---

            $existingIds = $instrumenTemplate->kriterias()->pluck('kriteria_id')->toArray();
            $newIds = array_keys($kriteriaToSync);

            // Hapus relasi yang tidak lagi dicentang
            $idsToDelete = array_diff($existingIds, $newIds);
            if (!empty($idsToDelete)) {
                TemplateKriteria::where('instrumen_template_id', $instrumenTemplate->id)
                    ->whereIn('kriteria_id', $idsToDelete)
                    ->delete();
            }

            // Update atau buat relasi yang baru/sudah ada
            foreach ($kriteriaToSync as $kriteriaId => $pivotData) {
                TemplateKriteria::updateOrCreate(
                    [
                        'instrumen_template_id' => $instrumenTemplate->id,
                        'kriteria_id' => $kriteriaId,
                    ],
                    [
                        'bobot' => $pivotData['bobot'] ?? null,
                    ]
                );
            }

            DB::commit(); // Jika semua berhasil, simpan perubahan

            $response = [
                'status'  => TRUE,
                'message' => 'Rancangan instrumen berhasil diperbarui.',
                // Sesuaikan nama route ini dengan route index Anda
                'redirect' => route($this->code.'.index') 
            ];
            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack(); // Jika ada error, batalkan semua perubahan
            
            $response = [
                'status'  => FALSE,
                // Tampilkan pesan error yang lebih informatif saat mode debug aktif
                'message' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server.'
            ];
            return response()->json($response, 500);
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
					'nama' => 'required|'.config('master.regex.json'),
					'deskripsi' => 'required|'.config('master.regex.json'),
					'is_active' => 'required|boolean',
					'lembaga_akreditasi_id' => 'required|'.config('master.regex.json'),
        ]);

        $data=$this->model::find($id);
        if($data->update($request->all())){
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
