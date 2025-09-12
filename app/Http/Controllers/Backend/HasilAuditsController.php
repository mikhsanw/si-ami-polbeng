<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class HasilAuditsController extends Controller
{
    public function index(Request $request)
    {
        // Eager load relasi yang dibutuhkan untuk performa
        $auditperiodes = \App\Models\AuditPeriode::with('unit', 'instrumenTemplate.kriterias')
            ->where('status', true)
            ->get();

        // Loop melalui setiap periode audit untuk menghitung progresnya
        foreach ($auditperiodes as $periode) {
            $template = $periode->instrumenTemplate;
            if (!$template) {
                $periode->progress = 0;
                $periode->statusText = 'Instrumen Tidak Ditemukan';
                $periode->statusClass = 'text-bg-secondary';
                continue;
            }

            // 1. Hitung Total Indikator secara akurat
            // Ambil semua ID kriteria yang terhubung dengan template ini
            $kriteriaIds = $template->kriterias->pluck('id');
            // Hitung semua indikator yang terkait dengan kriteria-kriteria tersebut
            $totalIndikator = \App\Models\Indikator::whereIn('kriteria_id', $kriteriaIds)->count();

            // 2. Hitung Indikator yang Selesai
            $indikatorSelesai = $this->model::where('audit_periode_id', $periode->id)
                ->where('status_terkini', 'SELESAI') // Sesuaikan dengan nama status Anda
                ->count();

            // 3. Kalkulasi Persentase
            $progress = ($totalIndikator > 0) ? round(($indikatorSelesai / $totalIndikator) * 100) : 0;

            // 4. Tambahkan properti dinamis ke objek periode audit
            $periode->progress = $progress;
            
            // Tentukan status dinamis berdasarkan progres
            if ($progress == 100) {
                $periode->statusText = 'Selesai';
                $periode->statusClass = 'text-bg-success';
            } elseif ($progress > 0) {
                $periode->statusText = 'Sedang Berlangsung';
                $periode->statusClass = 'text-bg-warning';
            } else {
                $periode->statusText = 'Belum Dikerjakan';
                $periode->statusClass = 'text-bg-light';
            }
        }
        return view($this->view.'.index', compact('auditperiodes'));
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

    private function calculateScore(\App\Models\Indikator $indikator, array $inputData): ?int
    {
        // 1. Buat map dari nama variabel ke nilainya
        $variableValues = [];
        foreach ($indikator->indikatorInputs as $field) {
            // $inputData memiliki key berupa ID, jadi kita perlu mencocokkannya
            if (isset($inputData[$field->id])) {
                $variableValues[$field->nama_variable] = (float) $inputData[$field->id];
            }
        }

        // 2. Loop melalui setiap rubrik (dari skor tertinggi ke terendah)
        foreach ($indikator->rubrikPenilaians->sortByDesc('skor') as $rubrik) {
            $formula = $rubrik->formula_kondisi;

            // 3. Ganti nama variabel di formula dengan nilainya
            foreach ($variableValues as $variable => $value) {
                $formula = str_replace($variable, $value, $formula);
            }
            
            // 4. Evaluasi formula yang sudah diganti (gunakan parser yang aman)
            // Catatan: Ini adalah evaluator sederhana. Untuk formula kompleks, gunakan library seperti "symfony/expression-language".
            if ($this->evaluateFormula($formula)) {
                return $rubrik->skor; // Kembalikan skor jika kondisi terpenuhi
            }
        }

        return null; // Kembalikan null jika tidak ada rubrik yang cocok
    }

    private function evaluateFormula(string $formula): bool
    {
        // Hapus karakter yang tidak diizinkan untuk keamanan
        $safeFormula = preg_replace('/[^0-9\.\+\-\*\/\(\)\s\<\>\=\!]/', '', $formula);
        
        // Gunakan @eval untuk menekan error jika formula tidak valid, dan kembalikan false
        // PERINGATAN: Ini masih berisiko. Gunakan library parser di lingkungan produksi.
        try {
            return (bool) @eval("return ($safeFormula);");
        } catch (\ParseError $e) {
            return false;
        }
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
        $indikator = \App\Models\Indikator::with('indikatorInputs', 'rubrikPenilaians')->findOrFail($id);

        // 2. Aturan Validasi Dinamis
        $rules = [
            'audit_periode_id' => 'required|exists:audit_periodes,id',
            'upload_file.*'    => 'nullable|file|max:2048',
        ];

        if ($indikator->tipe === 'LED') {
            $rules['skor_auditee'] = 'required|integer|between:1,4';
        } 
        elseif ($indikator->tipe === 'LKPS') {
            $rules['lkps_data'] = 'required|array';
            foreach ($indikator->indikatorInputs as $field) {
                $rules['lkps_data.' . $field->id] = 'required|numeric';
            }
        }

        $validated = $request->validate($rules);
        $skorAuditee = null;
        
        if ($indikator->tipe === 'LED') {
            $skorAuditee = $validated['skor_auditee'];
        } 
        elseif ($indikator->tipe === 'LKPS') {
            // Panggil helper method untuk menghitung skor dari data LKPS
            $skorAuditee = $this->calculateScore($indikator, $validated['lkps_data']);
            if ($skorAuditee === null) {
                // Jika data tidak cocok dengan rubrik manapun, lempar error validasi
                throw \Illuminate\Validation\ValidationException::withMessages(['lkps_data' => 'Data yang diinput tidak cocok dengan rubrik penilaian manapun.']);
            }
        }

        DB::beginTransaction();
        try {
            // updateOrCreate data utama
            $data = $this->model::updateOrCreate(
                [
                    'audit_periode_id' => $request->input('audit_periode_id'),
                    'indikator_id'     => $id,
                ],
                [
                    'skor_auditee'   => $skorAuditee,
                    'status_terkini' => config('master.content.hasil_audit.status_terkini.Diajukan'),
                ]
            );

            // simpan input LKPS
            if ($indikator->tipe === 'LKPS') {
                foreach ($request->input('lkps_data') as $fieldId => $value) {
                    $data->dataAuditInput()->updateOrCreate(
                        ['indikator_input_id' => $fieldId],
                        ['nilai_variable' => $value]
                    );
                }
            }

            // simpan file
            if ($request->hasFile('upload_file')) {
                foreach ($request->file('upload_file') as $file) {
                    if ($file) {
                        $path = Storage::disk(config('filesystems.default'))
                            ->putFile($this->code . '/' . date('Y') . '/' . date('m') . '/' . date('d'), $file);

                        $data->file()->create([
                            'alias' => 'bukti_penilaian',
                            'data'  => [
                                'name'   => basename($path),
                                'disk'   => config('filesystems.default'),
                                'target' => $path,
                            ],
                        ]);
                    }
                }
            }

            $data->logAktivitasAudit()->create([
                    'tipe_aksi' => config('master.content.log_aktivitas_audit.tipe_aksi.SUBMIT_AWAL'),
                    'user_id' => auth()->id(),
                    'catatan_aksi' => 'Auditee memperbarui data evaluasi diri.'
                ]
            );

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Data berhasil diperbarui', 'redirect' => route($this->code.'.audit-kriteria', $request->input('audit_periode_id'))]);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                return response()->json(['status' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }
            return response()->json(['status' => false, 'message' => 'Data gagal diperbarui: ' . $e->getMessage()], 500);
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
