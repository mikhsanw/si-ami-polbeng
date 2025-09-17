<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class KriteriasController extends Controller
{
    public function index()
    {
        $kriterias = $this->model::whereNull('parent_id')
                              ->with(['childrenRecursive', 'indikators', 'children'])
                              ->get();
        
        return view($this->view.'.index',compact('kriterias'));
    }

    public function create()
    {
        return view($this->view.'.form' );
    }
    public function createChild($id)
    {
        $data = [
            'parent' => $this->model::find($id),
        ];
        return view($this->view.'.form', $data);
    }
    public function createIndikator($id)
    {
        $data = [
            'parent' => $this->model::find($id),
            'existingRubrikDeskripsi' => [],
            'existingRubrikFormula' => [],
            'inputFields' => []
        ];
        return view($this->view.'.formIndikator', $data);
    }

    //editIndikator
    public function editIndikator($id)
    {
        $data = \App\Models\Indikator::find($id);

        foreach ($data->rubrikPenilaians as $rubrik) {
            $existingRubrikDeskripsi[$rubrik->skor] = $rubrik->deskripsi;
            $existingRubrikFormula[$rubrik->skor] = $rubrik->formula_kondisi;
        }
        $indikatorInputs = $data->indikatorInputs->map(function ($input) {
            return [
                'label' => $input->label_input,
                'variable' => $input->nama_variable,
                'tipe_data' => $input->tipe_data,
            ];
        });
        $data = [
            'data' => $data,
            'parent' => $data->kriteria,
            'existingRubrikDeskripsi' => $existingRubrikDeskripsi ?? [],
            'existingRubrikFormula' => $existingRubrikFormula ?? [],
            'inputFields' => $indikatorInputs ?? []
        ];
        return view($this->view.'.formIndikator', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
					'kode' => 'required|'.config('master.regex.json'),
					'nama' => 'required|'.config('master.regex.json'),
        ]);
        if ($data = $this->model::create($request->all())) {
            $response = [
                'status' => TRUE,
                'message' => 'Data berhasil disimpan',
                'redirect' => $request->input('redirect') ?? route('kriterias.index')
            ];
        }
        return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal disimpan']);
    }

    public function storeIndikator(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => ['required', Rule::in(config('master.content.kriteria.tipe'))],
            'parent_id' => 'nullable|exists:kriterias,id',
            'rubrik_manual_deskripsi' => 'required_if:tipe,LED|array',
            'rubrik_manual_deskripsi.*' => 'nullable|string',
            'rubrik_otomatis_deskripsi' => 'required_if:tipe,LKPS|array',
            'rubrik_otomatis_deskripsi.*' => 'nullable|string',
            'rubrik_formula' => 'nullable|array',
            'rubrik_formula.*' => 'nullable|string',
        ]);

        $validator = Validator::make($request->all(), []);
        $validator->after(function ($validator) use ($request) {
            if ($request->input('tipe') === 'LKPS') {
                $fields = $request->input('input_fields', []);
                foreach ($fields as $index => $field) {
                    if (empty($field['label'])) {
                        $validator->errors()->add("input_fields.$index.label", 'Label input wajib diisi.');
                    }
                    if (empty($field['variable'])) {
                        $validator->errors()->add("input_fields.$index.variable", 'Nama variabel wajib diisi.');
                    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $field['variable'])) {
                        $validator->errors()->add("input_fields.$index.variable", 'Nama variabel hanya boleh mengandung huruf, angka, strip, dan underscore.');
                    }
                    if (empty($field['tipe_data'])) {
                        $validator->errors()->add("input_fields.$index.tipe_data", 'Tipe data wajib dipilih.');
                    } elseif (!in_array($field['tipe_data'], ['ANGKA', 'PERSENTASE', 'TEKS'])) {
                        $validator->errors()->add("input_fields.$index.tipe_data", 'Tipe data tidak valid.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first(),
            ]);
        }

        DB::beginTransaction();
        try {
            // Simpan indikator utama
            $indikator = \App\Models\Indikator::create([
                'kriteria_id' => $request->input('parent_id'),
                'nama' => $request->input('nama'),
                'tipe' => $request->input('tipe'),
            ]);

            // Manual
            if ($request->input('tipe') === 'LED') {
                foreach ($request->input('rubrik_manual_deskripsi', []) as $skor => $deskripsi) {
                    if (trim($deskripsi)) {
                        $indikator->rubrikPenilaians()->create([
                            'skor' => $skor,
                            'deskripsi' => $deskripsi,
                        ]);
                    }
                }
            }

            // LKPS
            if ($request->input('tipe') === 'LKPS') {
                foreach ($request->input('input_fields', []) as $field) {
                    $indikator->indikatorInputs()->create([
                        'label_input' => $field['label'],
                        'nama_variable' => $field['variable'],
                        'tipe_data' => $field['tipe_data'],
                    ]);
                }

                $rubrik = $request->input('rubrik_otomatis_deskripsi', []);
                $formula = $request->input('rubrik_formula', []);

                foreach ($rubrik as $skor => $deskripsi) {
                    $indikator->rubrikPenilaians()->create([
                        'skor' => $skor,
                        'deskripsi' => $deskripsi,
                        'formula_kondisi' => $formula[$skor] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil disimpan.',
                'redirect' => $request->input('redirect') ?? route('kriterias.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ]);
        }
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
        ];
        return view($this->view.'.form', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
					'kode' => 'required|'.config('master.regex.json'),
					'nama' => 'required|'.config('master.regex.json'),
        ]);

        $data=$this->model::find($id);
        if($data->update($request->all())){
            $response=[ 'status'=>TRUE, 'message'=>'Data berhasil disimpan','redirect' => $request->input('redirect') ?? route('kriterias.index')];
        }
        return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal disimpan']);
    }

    public function updateIndikator(Request $request, $id)
    {
        // Validasi awal (langsung tampil error jika gagal)
        $request->validate([
            'nama' => 'required|string',
            'tipe' => ['required', Rule::in(config('master.content.kriteria.tipe'))],
            'parent_id' => 'nullable|exists:kriterias,id',
            'rubrik_manual_deskripsi' => 'required_if:tipe,LED|array',
            'rubrik_manual_deskripsi.*' => 'nullable|string',
            'rubrik_otomatis_deskripsi' => 'required_if:tipe,LKPS|array',
            'rubrik_otomatis_deskripsi.*' => 'nullable|string',
            'rubrik_formula' => 'nullable|array',
            'rubrik_formula.*' => 'nullable|string',
        ]);

        // Validasi lanjutan untuk input_fields jika tipe LKPS
        $validator = Validator::make($request->all(), []);
        $validator->after(function ($validator) use ($request) {
            if ($request->input('tipe') === "LKPS" ) {
                $fields = $request->input('input_fields', []);
                foreach ($fields as $index => $field) {
                    if (empty($field['label'])) {
                        $validator->errors()->add("input_fields.$index.label", 'Label input wajib diisi.');
                    }
                    if (empty($field['variable'])) {
                        $validator->errors()->add("input_fields.$index.variable", 'Nama variabel wajib diisi.');
                    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $field['variable'])) {
                        $validator->errors()->add("input_fields.$index.variable", 'Nama variabel hanya boleh mengandung huruf, angka, strip, dan underscore.');
                    }
                    if (empty($field['tipe_data'])) {
                        $validator->errors()->add("input_fields.$index.tipe_data", 'Tipe data wajib dipilih.');
                    } elseif (!in_array($field['tipe_data'], ['ANGKA', 'PERSENTASE', 'TEKS'])) {
                        $validator->errors()->add("input_fields.$index.tipe_data", 'Tipe data tidak valid.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first(),
            ]);
        }

        DB::beginTransaction();
        try {
            $indikator = \App\Models\Indikator::findOrFail($id);

            // Update indikator utama
            $indikator->update([
                'kriteria_id' => $request->input('parent_id'),
                'nama' => $request->input('nama'),
                'tipe' => $request->input('tipe'),
            ]);

            // Bersihkan relasi lama
            $indikator->rubrikPenilaians()->delete();
            $indikator->indikatorInputs()->delete();

            // LED
            if ($request->input('tipe') === 'LED') {
                foreach ($request->input('rubrik_manual_deskripsi', []) as $skor => $deskripsi) {
                    if (trim($deskripsi)) {
                        $indikator->rubrikPenilaians()->create([
                            'skor' => $skor,
                            'deskripsi' => $deskripsi,
                        ]);
                    }
                }
            }

            // LKPS
            if ($request->input('tipe') === 'LKPS') {
                foreach ($request->input('input_fields', []) as $field) {
                    $indikator->indikatorInputs()->create([
                        'label_input' => $field['label'],
                        'nama_variable' => $field['variable'],
                        'tipe_data' => $field['tipe_data'],
                    ]);
                }

                $rubrik = $request->input('rubrik_otomatis_deskripsi', []);
                $formula = $request->input('rubrik_formula', []);

                foreach ($rubrik as $skor => $deskripsi) {
                    $indikator->rubrikPenilaians()->create([
                        'skor' => $skor,
                        'deskripsi' => $deskripsi,
                        'formula_kondisi' => $formula[$skor] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diperbaharui.',
                'redirect' => $request->input('redirect') ?? route('kriterias.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbaharui data: ' . $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        $data=$this->model::find($id);
        return view($this->view.'.delete', compact('data'));
    }

    public function deleteIndikator($id)
    {
        $data=\App\Models\Indikator::find($id);
        return view($this->view.'.deleteIndikator', compact('data'));
    }

    public function destroy($id)
    {
        $data=$this->model::find($id);
        if ($data->indikators()->count() > 0 || $data->children()->count() > 0) {
            return response()->json([
                'status'  => FALSE,
                'message' => 'Data indikator tidak dapat dihapus karena memiliki relasi'
            ]);
        }
        if($data->delete()){
            $response=[ 'status'=>TRUE, 'message'=>'Data berhasil dihapus', 'redirect' => route('kriterias.index')];
        }
        return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal dihapus']);
    }

    public function destroyIndikator($id)
    {
        $data = \App\Models\Indikator::find($id);
        if (!$data) {
            return response()->json([
                'status'  => FALSE,
                'message' => 'Data indikator tidak ditemukan'
            ]);
        }

        // if ($data->rubrikPenilaians()->count() > 0 || $data->indikatorInputs()->count() > 0) {
        //    return response()->json([
        //        'status'  => FALSE,
        //        'message' => 'Data indikator tidak dapat dihapus karena memiliki relasi'
        //    ]);
        // }

        if ($data->delete()) {
            return response()->json([
                'status'   => TRUE,
                'message'  => 'Data berhasil dihapus',
                'redirect' => route('kriterias.index')
            ]);
        }

        return response()->json([
            'status'  => FALSE,
            'message' => 'Data gagal dihapus'
        ]);
    }
}
