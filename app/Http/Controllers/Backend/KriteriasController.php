<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class KriteriasController extends Controller
{
    public function index($id = null)
    {

        $kriterias = $id != null ? $this->model::with(['childrenRecursive', 'indikators', 'children'])->whereNull('parent_id')->whereLembagaAkreditasiId($id)->get() : [];
        $data = \App\Models\LembagaAkreditasi::pluck('nama', 'id')->toArray();
        $filterOptions = ['' => 'Pilih Lembaga Akreditasi'] + $data;

        return view($this->view.'.index', compact('kriterias', 'filterOptions', 'id'));
    }

    public function create()
    {
        $data = [
            'lembagaAkreditasiOptions' => \App\Models\LembagaAkreditasi::pluck('nama', 'id')->toArray(),
        ];

        return view($this->view.'.form', $data);
    }

    public function createChild($id)
    {
        $data = [
            'parent' => $this->model::find($id),
            'lembagaAkreditasiOptions' => \App\Models\LembagaAkreditasi::pluck('nama', 'id')->toArray(),
        ];

        return view($this->view.'.form', $data);
    }

    public function createIndikator($id)
    {
        $data = [
            'parent' => $this->model::find($id),
            'existingRubrikDeskripsi' => [],
            'existingRubrikFormula' => [],
            'inputFields' => [],
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
        $indikatorInputs = $data->indikatorInputs->sortBy('urutan')->map(function ($input) {
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
            'inputFields' => $indikatorInputs ?? [],
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
                'status' => true,
                'message' => 'Data berhasil disimpan',
                'redirect' => $request->input('redirect') ?? route('kriterias.index'),
            ];
        }

        return response()->json($response ?? ['status' => false, 'message' => 'Data gagal disimpan']);
    }

    public function storeIndikator(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'tipe' => ['required', Rule::in(config('master.content.kriteria.tipe'))],
            'parent_id' => 'nullable|exists:kriterias,id',
            'rubrik_manual_deskripsi' => 'required_if:tipe,LED|array',
            'rubrik_manual_deskripsi.*' => 'nullable|string',
            'formula_penilaian' => 'required_if:tipe,LKPS|string',
        ]);

        // Validasi lanjutan untuk input_fields jika tipe LKPS
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
                    } elseif (! preg_match('/^[a-zA-Z0-9_-]+$/', $field['variable'])) {
                        $validator->errors()->add("input_fields.$index.variable", 'Nama variabel hanya boleh mengandung huruf, angka, strip, dan underscore.');
                    }
                    if (empty($field['tipe_data'])) {
                        $validator->errors()->add("input_fields.$index.tipe_data", 'Tipe data wajib dipilih.');
                    } elseif (! in_array($field['tipe_data'], ['ANGKA', 'PERSENTASE', 'TEKS'])) {
                        $validator->errors()->add("input_fields.$index.tipe_data", 'Tipe data tidak valid.');
                    }
                }

                $formulas = $request->input('formula_penilaian', '');
                if (trim($formulas)) {
                    // Cegah pakai "x" untuk kali
                    if (preg_match('/(?<=\S)\sx\s(?=\S)/', $formulas)) {
                        $validator->errors()->add('formula_penilaian', 'Gunakan * untuk perkalian, bukan x.');
                    }

                    if (! preg_match('/^[0-9A-Za-z_\s\.\,\+\-\*\/\<\>\=\!\&\|\?\:\(\)\[\]]+$/', $formulas)) {
                        $validator->errors()->add(
                            'formula_penilaian',
                            'Formula penilaian mengandung karakter/operator yang tidak valid. 
                            Hanya boleh gunakan huruf, angka, _ , + - * / < > = ! && || ? : () [] dan spasi.'
                        );
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
                'formula_penilaian' => $request->input('formula_penilaian', null),
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
                foreach ($request->input('input_fields', []) as $key => $field) {
                    $indikator->indikatorInputs()->create([
                        'label_input' => $field['label'],
                        'nama_variable' => $field['variable'],
                        'tipe_data' => $field['tipe_data'],
                        'urutan' => $key + 1,
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
                'message' => 'Gagal menyimpan data: '.$e->getMessage(),
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
        $data = [
            'data' => $this->model::find($id),
            'lembagaAkreditasiOptions' => \App\Models\LembagaAkreditasi::pluck('nama', 'id')->toArray(),
        ];

        return view($this->view.'.form', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode' => 'required|'.config('master.regex.json'),
            'nama' => 'required|'.config('master.regex.json'),
        ]);

        $data = $this->model::find($id);
        if ($data->update($request->all())) {
            $response = ['status' => true, 'message' => 'Data berhasil disimpan', 'redirect' => $request->input('redirect') ?? route('kriterias.index')];
        }

        return response()->json($response ?? ['status' => false, 'message' => 'Data gagal disimpan']);
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
            'formula_penilaian' => 'required_if:tipe,LKPS|string',
        ]);

        // Validasi lanjutan untuk input_fields jika tipe LKPS
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
                    } elseif (! preg_match('/^[a-zA-Z0-9_-]+$/', $field['variable'])) {
                        $validator->errors()->add("input_fields.$index.variable", 'Nama variabel hanya boleh mengandung huruf, angka, strip, dan underscore.');
                    }
                    if (empty($field['tipe_data'])) {
                        $validator->errors()->add("input_fields.$index.tipe_data", 'Tipe data wajib dipilih.');
                    } elseif (! in_array($field['tipe_data'], ['ANGKA', 'PERSENTASE', 'TEKS'])) {
                        $validator->errors()->add("input_fields.$index.tipe_data", 'Tipe data tidak valid.');
                    }
                }

                $formulas = $request->input('formula_penilaian', '');
                if (trim($formulas)) {
                    // Cegah pakai "x" untuk kali
                    if (preg_match('/(?<=\S)\sx\s(?=\S)/', $formulas)) {
                        $validator->errors()->add('formula_penilaian', 'Gunakan * untuk perkalian, bukan x.');
                    }

                    if (! preg_match('/^[0-9A-Za-z_\s\.\,\+\-\*\/\<\>\=\!\&\|\?\:\(\)\[\]]+$/', $formulas)) {
                        $validator->errors()->add(
                            'formula_penilaian',
                            'Formula penilaian mengandung karakter/operator yang tidak valid. 
                            Hanya boleh gunakan huruf, angka, _ , + - * / < > = ! && || ? : () [] dan spasi.'
                        );
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

            // Cek apakah ada perubahan di kolom utama
            $indikator->fill([
                'kriteria_id' => $request->input('parent_id'),
                'nama' => $request->input('nama'),
                'tipe' => $request->input('tipe'),
                'formula_penilaian' => $request->input('formula_penilaian'),
            ]);

            if ($indikator->isDirty()) {
                $indikator->save();
            }

            // ---- LED ----
            if ($request->input('tipe') === 'LED') {
                foreach ($request->input('rubrik_manual_deskripsi', []) as $skor => $deskripsi) {
                    if (trim($deskripsi)) {
                        $indikator->rubrikPenilaians()
                            ->updateOrCreate(
                                ['skor' => $skor],
                                ['deskripsi' => $deskripsi]
                            );
                    }
                }
            }

            // ---- LKPS ----
            if ($request->input('tipe') === 'LKPS') {
                $existingInputs = $indikator->indikatorInputs()->get();

                foreach ($request->input('input_fields', []) as $key => $field) {
                    $indikator->indikatorInputs()
                        ->updateOrCreate(
                            ['nama_variable' => $field['variable']],
                            [
                                'label_input' => $field['label'],
                                'tipe_data' => $field['tipe_data'],
                                'urutan' => $key + 1,
                            ]
                        );
                }

                // Hapus input lama yang tidak lagi ada
                $currentVariables = collect($request->input('input_fields'))->pluck('variable')->toArray();
                $indikator->indikatorInputs()
                    ->whereNotIn('nama_variable', $currentVariables)
                    ->delete();
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
                'message' => 'Gagal memperbaharui data: '.$e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        $data = $this->model::find($id);

        return view($this->view.'.delete', compact('data'));
    }

    public function deleteIndikator($id)
    {
        $data = \App\Models\Indikator::find($id);

        return view($this->view.'.deleteIndikator', compact('data'));
    }

    public function destroy($id)
    {
        $data = $this->model::find($id);
        if ($data->indikators()->count() > 0 || $data->children()->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Data indikator tidak dapat dihapus karena memiliki relasi',
            ]);
        }
        if ($data->delete()) {
            $response = ['status' => true, 'message' => 'Data berhasil dihapus', 'redirect' => route('kriterias.index').'/'.$data->lembaga_akreditasi_id];
        }

        return response()->json($response ?? ['status' => false, 'message' => 'Data gagal dihapus']);
    }

    public function destroyIndikator($id)
    {
        $data = \App\Models\Indikator::find($id);
        if (! $data) {
            return response()->json([
                'status' => false,
                'message' => 'Data indikator tidak ditemukan',
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
                'status' => true,
                'message' => 'Data berhasil dihapus',
                'redirect' => route('kriterias.index').'/'.$data->kriteria->lembaga_akreditasi_id,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data gagal dihapus',
        ]);
    }

    public function cekFormula(Request $request)
    {
        $formula = $request->input('formula');
        $variables = $request->input('variables', []);

        if (empty($formula)) {
            return response()->json([
                'valid' => false,
                'message' => 'Formula tidak boleh kosong.',
            ]);
        }

        if (empty($variables)) {
            return response()->json([
                'valid' => false,
                'message' => 'Belum ada variabel input yang didefinisikan.',
            ]);
        }

        if (! preg_match('/^[0-9A-Za-z_\s\.\,\+\-\*\/\<\>\=\!\&\|\?\:\(\)\[\]]+$/', $formula)) {
            return response()->json([
                'valid' => false,
                'message' => 'Formula penilaian mengandung karakter/operator yang tidak valid. 
                Hanya boleh gunakan huruf, angka, _ , + - * / < > = ! && || ? : () [] dan spasi.',
            ]);
        }

        $language = new ExpressionLanguage();

        try {
            // 1️⃣ Parsing untuk cek sintaks formula
            $language->parse($formula, $variables);

            // 2️⃣ Siapkan data dummy sesuai jumlah variabel
            $dummyData = collect($variables)->mapWithKeys(fn ($v) => [$v => rand(1, 5)])->toArray();

            // 3️⃣ Jalankan evaluasi contoh
            $result = $language->evaluate($formula, $dummyData);

            return response()->json([
                'valid' => true,
                'message' => "Formula valid ✅. Contoh hasil evaluasi: {$result}",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Formula tidak valid ❌: '.$e->getMessage(),
            ]);
        }
    }
}
