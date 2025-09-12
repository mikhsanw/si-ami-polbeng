<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PenugasanAuditsController extends Controller
{
    public function index(Request $request)
    {
       $penugasanAuditor = \Illuminate\Support\Facades\Auth::user()->penugasanAuditors()
            ->with([
                'auditPeriode.unit', 
                'auditPeriode.instrumenTemplate.kriterias'
            ])
            ->whereHas('auditPeriode', function ($query) {
                $query->where('status', true);
            })
            ->get();

        // 2. Loop melalui setiap penugasan untuk menghitung progres
        foreach ($penugasanAuditor as $penugasan) {
            $periode = $penugasan->auditPeriode;
            $template = $periode->instrumenTemplate;

            if (!$template) {
                $periode->progress = 0;
                $periode->statusText = 'Instrumen Tidak Ditemukan';
                $periode->statusClass = 'text-bg-secondary';
                continue;
            }

            // Hitung total indikator
            $kriteriaIds = $template->kriterias->pluck('id');
            $totalIndikator = \App\Models\Indikator::whereIn('kriteria_id', $kriteriaIds)->count();

            // Hitung indikator yang sudah divalidasi (status SELESAI)
            $indikatorSelesai = $this->model::where('audit_periode_id', $periode->id)
                ->where('status_terkini', 'SELESAI')
                ->count();
            
            // Kalkulasi persentase
            $progress = ($totalIndikator > 0) ? round(($indikatorSelesai / $totalIndikator) * 100) : 0;
            
            $periode->progress = $progress;

            // Tentukan status dinamis
            if ($progress == 100) {
                $periode->statusText = 'Validasi Selesai';
                $periode->statusClass = 'text-bg-success';
            } elseif ($progress > 0) {
                $periode->statusText = 'Validasi Berlangsung';
                $periode->statusClass = 'text-bg-warning';
            } else {
                $periode->statusText = 'Menunggu Pengerjaan Auditee';
                $periode->statusClass = 'text-bg-light';
            }
        }

        return view($this->view.'.index', compact('penugasanAuditor'));
    }

    public function auditKriteriaIndex(Request $request, $id)
    {
        $auditPeriode = \App\Models\AuditPeriode::with('unit', 'instrumenTemplate')->findOrFail($id);

        $template = $auditPeriode->instrumenTemplate;

        if (!$template) {
            return back()->with('error', 'Instrumen audit tidak dapat ditemukan untuk periode ini.');
        }

        $kriterias = $template->kriterias()
            ->whereNull('parent_id') // Mulai dari kriteria level tertinggi (induk).
            ->with([
                'childrenRecursive',
                'indikators',
                'indikators.hasilAudit' => function ($query) use ($auditPeriode) {
                    $query->where('id_audit', $auditPeriode->id);
                },
                'childrenRecursive.indikators.hasilAudit' => function ($query) use ($auditPeriode) {
                    $query->where('id_audit', $auditPeriode->id);
                }
            ])
            ->get();
        return view($this->view.'.auditkriteria', compact('kriterias', 'auditPeriode', 'template'));
    }

    public function create()
    {
		$data=[
			'audit_periode_id'	=> \App\Models\AuditPeriode::pluck('nama','id'),
			'indikator_id'	=> \App\Models\Indikator::pluck('nama','id'),
		];

        return view($this->view.'.form' ,$data);
    }

    public function store(Request $request)
    {
        $rules = [
            'upload_file.*' => 'nullable|file|max:2048', // max 2MB per file
            'skor_auditee' => 'required|'.config('master.app.regex.number'),
            'audit_periode_id' => 'required',
            'indikator_id' => 'required',
        ];

        if ($request->has('lkps_data')) {
            foreach ($request->input('lkps_data') as $id => $value) {
                $rules["lkps_data.$id"] = 'required'; // Bisa disesuaikan tipe datanya
            }
        }

        $validated = $request->validate($rules);

        if ($data = $this->model::create([
                'skor_auditee' => $request->input('skor_auditee'),
                'audit_periode_id' => $request->input('audit_periode_id'),
                'indikator_id' => $request->input('indikator_id'),
                'status_terkini' => config('master.hasil_audit.status_terkini.Diajukan'),
            ])
        ) {
            if ($request->hasFile('upload_file')) {
                foreach ($request->file('upload_file') as $file) {
                    if ($file) {
                        $data->file()->create([
                            'alias' => 'bukti_penilaian', // bisa disesuaikan
                            'data'  => [
                                'name'   => $file->hashName(),
                                'disk'   => config('filesystems.default'),
                                'target' => Storage::disk(config('filesystems.default'))->putFile(
                                    $this->code . '/' . date('Y') . '/' . date('m') . '/' . date('d'),
                                    $file
                                ),
                            ],
                        ]);
                    }
                }
            }
            if ($request->has('lkps_data')) {
                foreach ($request->input('lkps_data') as $id => $value) {
                    $data->dataAuditInput()->create([
                        'indikator_id' => $id,
                        'nilai_variable' => $value,
                    ]);
                }
            }
            $response=[ 'status'=>TRUE, 'message'=>'Data berhasil disimpan'];
        }
        return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal disimpan']);
    }

    public function show(Request $request, $id)
    {
        $auditPeriodeId = $request->get('audit_periode_id');

        $data = [
            'data'          => \App\Models\Indikator::findOrFail($id),
            'auditPeriode'  => \App\Models\AuditPeriode::findOrFail($auditPeriodeId),
        ];
        return view($this->view.'.show', $data);
    }

    public function edit(Request $request, $id)
    {
        $auditPeriodeId = $request->get('audit_periode_id');

        $data = [
            'data'          => \App\Models\Indikator::findOrFail($id),
            'auditPeriode'  => \App\Models\AuditPeriode::findOrFail($auditPeriodeId),
        ];
        return view($this->view.'.form', $data);
    }

    public function update(Request $request, $id)
    {
        // 1. Aturan Validasi Dinamis berdasarkan Aksi yang Dipilih
        $rules = [
            'action' => ['required', \Illuminate\Validation\Rule::in(['finalisasi', 'minta_revisi'])],
            'catatan_auditor' => 'nullable|string|max:5000',
        ];

        // Tambahkan aturan kondisional
        if ($request->input('action') === 'finalisasi') {
            $rules['skor_final'] = 'required|integer|between:1,4';
        }
        if ($request->input('action') === 'minta_revisi') {
            $rules['catatan_auditor'] = 'required|string|max:5000';
        }

        // Gunakan $request->validate() yang akan otomatis handle response error AJAX
        $validated = $request->validate($rules, [
            'catatan_auditor.required' => 'Catatan wajib diisi saat meminta revisi.'
        ]);

        // 2. Gunakan Transaction untuk memastikan integritas data
        DB::beginTransaction();
        try {
            // 3. Ambil record HasilAudit yang akan divalidasi
            $hasilAudit = $this->model::where('audit_periode_id', $request->input('audit_periode_id'))
                                    ->where('indikator_id', $request->input('indikator_id'))
                                    ->firstOrFail(); // Gagal jika auditee belum mengisi

            $catatan = $validated['catatan_auditor'] ?? null;
            
            // 4. Proses data berdasarkan Aksi yang Dipilih
            if ($validated['action'] === 'finalisasi') {
                $hasilAudit->skor_final = $validated['skor_final'];
                $hasilAudit->catatan_final = $catatan; // Catatan akhir
                $hasilAudit->status_terkini = 'Selesai';
                $tipeAksiLog = 'FINALISASI_SKOR';
            } 
            else { // Aksi adalah 'minta_revisi'
                $hasilAudit->status_terkini = 'Revisi';
                $tipeAksiLog = 'MINTA_REVISI';
            }

            // Simpan perubahan pada HasilAudit
            $hasilAudit->save();

            // 5. Buat entri baru di log aktivitas
            $hasilAudit->logAktivitasAudit()->create([
                'user_id'  => \Illuminate\Support\Facades\Auth::id(), // ID Auditor yang sedang login
                'tipe_aksi'     => $tipeAksiLog,
                'catatan_aksi'  => $catatan,
            ]);

            DB::commit(); // Konfirmasi semua perubahan jika berhasil

            $response = [
                'status'  => true,
                'message' => 'Keputusan validasi berhasil disimpan.',
                'redirect' => route($this->code.'.audit-kriteria', $request->input('audit_periode_id')) // Redirect kembali ke dasbor proses
            ];
            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua jika terjadi error
            
            $response = [
                'status'  => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server.'
            ];
            return response()->json($response, 500);
        }
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
