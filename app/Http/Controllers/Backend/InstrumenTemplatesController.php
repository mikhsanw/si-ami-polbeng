<?php

namespace App\Http\Controllers\Backend;

use App\Models\Indikator;
use Illuminate\Http\Request;
use App\Models\TemplateKriteria;
use App\Models\TemplateIndikator;
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

    public function getIndikatorTree(Request $request)
    {
        $lembagaId = $request->input('lembaga_id');
        $templateId = $request->input('template_id'); // ID template saat edit

        // Ambil kriteria level teratas yang terhubung dengan lembaga ini
        $kriterias = \App\Models\Kriteria::with([
                'children' => function($query) {
                    $query->with('indikators'); // Eager load indikator untuk semua anak kriteria
                },
                'indikators' // Eager load indikator untuk kriteria level atas itu sendiri
            ])
            ->whereNull('parent_id') // Ambil kriteria level atas
            ->where('lembaga_akreditasi_id', $lembagaId) // Filter berdasarkan lembaga yang terkait dengan template
            ->get();

        $selectedIndikatorIds = [];
        if ($templateId !== 'null' && $templateId) {
            // Jika dalam mode edit, ambil ID indikator yang sudah terpilih untuk template ini
            $template = $this->model::with('templateIndikators.indikator')->find($templateId);
            if ($template) {
                $selectedIndikatorIds = $template->templateIndikators->pluck('indikator_id')->toArray();
            }
        }
        
        // Pastikan variabel $backend dan $page tersedia jika partial membutuhkannya
        $backend = 'backend'; // Ganti dengan path folder backend Anda
        $page = (object)['code' => 'instrumen_template']; // Sesuaikan dengan code page Anda

        return view($this->view.'._kriteria_item', compact('kriterias', 'selectedIndikatorIds', 'backend', 'page'));
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

   public function updateRancangan(Request $request, $id)
    {
        $validated = $request->validate([
            'selected_indikators' => 'nullable|json', // Hidden input dari JS
            'bobot'               => 'nullable|array', // Semua bobot yang diinput
            'bobot.*'             => 'nullable|numeric|min:0|max:999.99', // Sesuaikan max dengan presisi DB
        ], [
            'bobot.*.numeric'   => 'Bobot harus berupa angka.',
            'bobot.*.min'       => 'Bobot tidak boleh kurang dari 0.',
            'bobot.*.max'       => 'Bobot melebihi batas maksimum.',
        ]);

        DB::beginTransaction();
        try {
            $instrumenTemplate = $this->model::findOrFail($id); // Gunakan nama model yang benar

            $selectedIndikatorIds = json_decode($request->input('selected_indikators', '[]'), true);
            if (!is_array($selectedIndikatorIds)) {
                $selectedIndikatorIds = [];
            }
            $inputBobots = $request->input('bobot', []);

            // --- 1. Hapus semua relasi template_indikator dan template_kriteria yang lama ---
            $instrumenTemplate->templateIndikators()->delete();
            $instrumenTemplate->templateKriterias()->delete();

            $kriteriaIdsToSync = []; // Untuk menyimpan Kriteria ID yang unik untuk templateKriterias

            // --- 2. Proses dan Buat Relasi template_indikator baru ---
            foreach ($selectedIndikatorIds as $indikatorId) {
                $indikator = Indikator::with('kriteria.parentRecursive')->find($indikatorId); // Load relasi kriteria dan parent rekursifnya
                if (!$indikator || !$indikator->kriteria) {
                    // Log error atau lewati jika indikator atau kriteria induk tidak ditemukan
                    continue;
                }

                $bobot = $inputBobots[$indikatorId] ?? 0; // Default bobot 0 jika tidak diinput
                
                // Buat entri di template_indikator
                TemplateIndikator::create([
                    'instrumen_template_id' => $instrumenTemplate->id,
                    'indikator_id' => $indikator->id,
                    'bobot' => (float) $bobot, // Pastikan di-cast ke float/decimal
                ]);

                // --- 3. Kumpulkan Kriteria ID untuk template_kriteria (Otomatis membangun hirarki) ---
                $currentKriteria = $indikator->kriteria;
                while ($currentKriteria) {
                    // Tambahkan Kriteria ID ke daftar yang akan di-sync, pastikan unik
                    $kriteriaIdsToSync[$currentKriteria->id] = true;
                    $currentKriteria = $currentKriteria->parentRecursive;
                }
            }

            // --- 4. Buat Relasi template_kriteria baru berdasarkan indikator yang dipilih ---
            foreach (array_keys($kriteriaIdsToSync) as $kriteriaId) {
                TemplateKriteria::create([
                    'instrumen_template_id' => $instrumenTemplate->id,
                    'kriteria_id' => $kriteriaId,
                    'bobot' => null, // Bobot di level kriteria diabaikan, hanya untuk struktur
                ]);
            }

            DB::commit(); // Jika semua berhasil, simpan perubahan

            $response = [
                'status'  => TRUE,
                'message' => 'Rancangan instrumen berhasil diperbarui.'
            ];
            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack(); // Jika ada error, batalkan semua perubahan
            
            $response = [
                'status'  => FALSE,
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
