<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditPeriode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class HasilAuditsController extends Controller
{
    public function index(Request $request)
    {
        $userUnitId = optional(auth()->user()->unit)->id;
        if (! $userUnitId || ! auth()->user()->hasPermissionTo($this->code.' list')) {
            $auditperiodes = collect();

            return view($this->view.'.index', compact('auditperiodes'));
        }

        $auditperiodes = AuditPeriode::with([
            'unit',
            'instrumenTemplate.templateIndikators',
            'hasilAudits',
        ])
            ->where('status', true)
            ->where('unit_id', $userUnitId)
            ->get();

        foreach ($auditperiodes as $periode) {
            $template = $periode->instrumenTemplate;

            // Inisialisasi default
            $periode->total_indikator = 0;

            // Inisialisasi status_counts sebagai array lokal
            $statusCounts = [ // <--- DEKLARASIKAN SEBAGAI VARIABEL LOKAL
                'belum_dikerjakan' => 0,
                'draft_dikerjakan' => 0,
                'diajukan' => 0,
                'revisi' => 0, // Asumsi ada status revisi
                'selesai' => 0, // Ini yang benar-benar final/diterima
                'total_terisi' => 0,
            ];

            $periode->overall_progress = 0;
            $periode->statusText = 'Belum Dikerjakan';
            $periode->statusClass = 'text-white bg-danger';

            if (! $template || $template->templateIndikators->isEmpty()) {
                $periode->statusText = 'Instrumen Tidak Ditemukan';
                $periode->statusClass = 'text-bg-secondary';
                // SET KEMBALI status_counts KE DEFAULT PADA OBJEK PERIODE JUGA
                $periode->status_counts = $statusCounts;

                continue;
            }

            $totalIndikatorDalamTemplate = $template->templateIndikators->count();
            $periode->total_indikator = $totalIndikatorDalamTemplate;

            $allHasilAudits = $periode->hasilAudits;

            // Hitung status untuk setiap indikator yang sudah ada hasil auditnya
            foreach ($allHasilAudits as $hasilAudit) {
                switch ($hasilAudit->status_terkini) {
                    case 'Draft':
                        $statusCounts['draft_dikerjakan']++; // <--- MODIFIKASI VARIABEL LOKAL
                        break;
                    case 'Diajukan':
                        $statusCounts['diajukan']++;       // <--- MODIFIKASI VARIABEL LOKAL
                        break;
                    case 'Revisi':
                        $statusCounts['revisi']++;         // <--- MODIFIKASI VARIABEL LOKAL
                        break;
                    case 'Selesai':
                        $statusCounts['selesai']++;        // <--- MODIFIKASI VARIABEL LOKAL
                        break;
                    default:
                        $statusCounts['draft_dikerjakan']++; // Default untuk status yang tidak dikenal
                        break;
                }
            }

            // Hitung total indikator yang sudah diisi (apapun statusnya)
            $statusCounts['total_terisi'] =
                $statusCounts['draft_dikerjakan'] +
                $statusCounts['diajukan'] +
                $statusCounts['revisi'] +
                $statusCounts['selesai'];

            // Indikator yang belum dikerjakan adalah total dikurangi yang sudah terisi
            $statusCounts['belum_dikerjakan'] =
                $totalIndikatorDalamTemplate - $statusCounts['total_terisi'];

            // Setelah semua perhitungan selesai, tetapkan array lokal ini ke properti objek
            $periode->status_counts = $statusCounts; // <--- TETAPKAN KEMBALI KE PROPERTI OBJEK

            // Kalkulasi Overall Progress (berdasarkan yang sudah terisi)
            $periode->overall_progress = ($totalIndikatorDalamTemplate > 0)
                                        ? round(($periode->status_counts['total_terisi'] / $totalIndikatorDalamTemplate) * 100)
                                        : 0;

            // --- Logika Penentuan Status Utama (untuk teks di kartu) ---
            if ($periode->overall_progress == 0) {
                $periode->statusText = 'Belum Dikerjakan';
                $periode->statusClass = 'text-white bg-danger';
            } elseif ($periode->status_counts['selesai'] == $totalIndikatorDalamTemplate) {
                $periode->statusText = 'Selesai & Diterima';
                $periode->statusClass = 'text-white bg-success';
            } elseif ($periode->status_counts['diajukan'] == $totalIndikatorDalamTemplate) { // Semua diajukan
                $periode->statusText = 'Menunggu Verifikasi (100% Diajukan)';
                $periode->statusClass = 'text-white bg-info';
            } elseif ($periode->status_counts['total_terisi'] > 0) { // Ada yang sudah terisi tapi belum 100% selesai/diajukan
                $periode->statusText = 'Sedang Berlangsung';
                $periode->statusClass = 'text-white bg-warning';
            } else {
                $periode->statusText = 'Tidak Diketahui';
                $periode->statusClass = 'text-bg-secondary';
            }
        }

        return view($this->view.'.index', compact('auditperiodes'));
    }

    public function auditKriteriaIndex(Request $request, $id)
    {
        $auditPeriode = \App\Models\AuditPeriode::with('unit', 'instrumenTemplate')->findOrFail($id);
        $template = $auditPeriode->instrumenTemplate;

        if (! $template) {
            return back()->with('error', 'Instrumen audit tidak dapat ditemukan untuk periode ini.');
        }

        // Ambil semua Kriteria ID yang termasuk dalam template ini dari tabel template_kriteria
        $kriteriaIdsInTemplate = $template->templateKriterias->pluck('kriteria_id')->toArray();

        // Ambil semua Indikator ID yang termasuk dalam template ini dari tabel template_indikator
        // Beserta bobotnya, dan kuncikan berdasarkan id_indikator untuk akses mudah di view
        $templateIndikators = $template->templateIndikators()->with('indikator')->get()->keyBy('indikator_id');
        $indikatorIdsInTemplate = $templateIndikators->pluck('indikator_id')->toArray();

        // --- DEFINISIKAN CLOSURE REKURSIF UNTUK EAGER LOADING KRITERIA & INDIKATOR ---
        $withRecursiveChildrenAndIndikators = function ($query) use (&$withRecursiveChildrenAndIndikators, $kriteriaIdsInTemplate, $indikatorIdsInTemplate, $auditPeriode) {
            $query->whereIn('id', $kriteriaIdsInTemplate) // Filter anak kriteria yang ada di template
                ->with([
                    'children' => $withRecursiveChildrenAndIndikators, // Rekursif ke level bawah
                    'indikators' => function ($q) use ($indikatorIdsInTemplate, $auditPeriode) {
                        $q->whereIn('id', $indikatorIdsInTemplate) // Filter indikator yang ada di template
                            ->with(['hasilAudits' => function ($haQuery) use ($auditPeriode) {
                                $haQuery->where('audit_periode_id', $auditPeriode->id);
                            }]);
                    },
                ]);
        };
        // --- AKHIR DEFINISI CLOSURE REKURSIF ---

        // Ambil kriteria level teratas yang ada di template
        $kriterias = \App\Models\Kriteria::whereNull('parent_id') // Mulai dari kriteria level tertinggi
            ->whereIn('id', $kriteriaIdsInTemplate) // Filter kriteria level tertinggi yang ada di template
            ->with([
                'children' => $withRecursiveChildrenAndIndikators, // Muat anak kriteria secara rekursif
                'indikators' => function ($query) use ($indikatorIdsInTemplate, $auditPeriode) {
                    $query->whereIn('id', $indikatorIdsInTemplate) // Muat indikator level atas yang ada di template
                        ->with(['hasilAudits' => function ($haQuery) use ($auditPeriode) {
                            $haQuery->where('audit_periode_id', $auditPeriode->id);
                        }]);
                },
            ])
            ->get();

        return view($this->view.'.auditkriteria', compact('kriterias', 'auditPeriode', 'template', 'templateIndikators'));
    }

    public function create()
    {
        $data = [
            'audit_periode_id' => \App\Models\AuditPeriode::pluck('nama', 'id'),
            'indikator_id' => \App\Models\Indikator::pluck('nama', 'id'),
        ];

        return view($this->view.'.form', $data);
    }

    public function show(Request $request, $id)
    {
        $auditPeriodeId = $request->get('audit_periode_id');

        $data = [
            'data' => \App\Models\Indikator::findOrFail($id),
            'auditPeriode' => \App\Models\AuditPeriode::findOrFail($auditPeriodeId),
        ];

        return view($this->view.'.show', $data);
    }

    public function edit(Request $request, $id)
    {
        $auditPeriodeId = $request->get('audit_periode_id');

        $data = [
            'data' => \App\Models\Indikator::findOrFail($id),
            'auditPeriode' => \App\Models\AuditPeriode::findOrFail($auditPeriodeId),
        ];

        return view($this->view.'.form', $data);
    }

    private function calculateScore(\App\Models\Indikator $indikator, array $inputData): ?float
    {
        if (empty($indikator->formula_penilaian)) {
            \Log::warning("Indikator ID {$indikator->id} (LKPS) tidak memiliki formula_penilaian.");

            return null;
        }

        $variableValues = [];
        foreach ($indikator->indikatorInputs as $field) {
            $rawValue = $inputData[$field->id] ?? null;

            // Pre-process rawValue based on tipe_data for ExpressionLanguage
            if ($rawValue !== null) {
                if ($field->tipe_data === 'number') {
                    $variableValues[$field->nama_variable] = (float) $rawValue;
                } elseif ($field->tipe_data === 'date') {
                    // Konversi tanggal ke Carbon object atau timestamp agar bisa dioperasikan (jika rumus menghendaki)
                    // Atau biarkan string jika rumus hanya untuk perbandingan string tanggal
                    try {
                        $variableValues[$field->nama_variable] = Carbon::parse($rawValue)->timestamp; // atau ->toDateString() untuk perbandingan string
                    } catch (\Exception $e) {
                        // Jika format tanggal salah setelah validasi, ini adalah fallback
                        $variableValues[$field->nama_variable] = 0; // atau null, tergantung bagaimana formula akan menangani
                        \Log::warning("Failed to parse date '{$rawValue}' for variable '{$field->nama_variable}' in Indikator ID {$indikator->id}. Using 0.");
                    }
                } else { // text
                    $variableValues[$field->nama_variable] = (string) $rawValue;
                }
            } else {
                // Default value jika input kosong
                $variableValues[$field->nama_variable] = ($field->tipe_data === 'number') ? 0.0 : '';
            }
        }

        $language = new ExpressionLanguage();

        // Mendaftarkan fungsi kustom jika diperlukan, contoh:
        // $language->register('some_custom_function', function ($arg) { /* ... */ }, function (array $values, $arg) { /* ... */ });

        try {
            $result = $language->evaluate($indikator->formula_penilaian, $variableValues);

            // ExpressionLanguage bisa mengembalikan boolean, string, atau angka.
            // Jika formula adalah kondisi (misal A > B), hasilnya boolean (true/false)
            // Anda harus memutuskan bagaimana mengubah boolean atau string menjadi skor numerik (0-4)
            if (is_bool($result)) {
                // Contoh: true = 4, false = 0
                return $result ? 4.0 : 0.0;
            } elseif (is_string($result) && is_numeric($result)) {
                // Jika ExpressionLanguage mengembalikan string numerik
                return (float) $result;
            } elseif (is_numeric($result)) {
                // Jika hasil sudah numerik
                return (float) $result;
            } else {
                // Jika hasilnya string non-numerik atau tipe lain yang tidak diharapkan
                \Log::error("Formula penilaian untuk Indikator ID {$indikator->id} menghasilkan nilai non-numerik atau tidak dapat diinterpretasikan sebagai skor: ".gettype($result).' -> '.print_r($result, true));

                return null; // Tidak dapat diinterpretasikan sebagai skor
            }

        } catch (\Symfony\Component\ExpressionLanguage\SyntaxError $e) {
            \Log::error("Syntax Error in formula for Indikator ID {$indikator->id}: ".$e->getMessage().' - Formula: '.$indikator->formula_penilaian);

            return null;
        } catch (\Exception $e) {
            \Log::error("Error evaluating formula for Indikator ID {$indikator->id}: ".$e->getMessage().' - Formula: '.$indikator->formula_penilaian);

            return null;
        }
    }

    public function update(Request $request, $id)
    {
        $indikator = \App\Models\Indikator::with('indikatorInputs')->findOrFail($id);
        // 2. Aturan Validasi Dinamis
        // Ambil data hasil audit yang sudah ada (jika ada)
        $dataExisting = $this->model::where('indikator_id', $id)
            ->where('audit_periode_id', $request->input('audit_periode_id'))
            ->with('file')
            ->first();

        // Tentukan apakah file wajib atau tidak
        $fileRequired = ! ($dataExisting && $dataExisting->file()->exists());

        // Aturan Validasi Dinamis
        $rules = [
            'audit_periode_id' => 'required|exists:audit_periodes,id',
            'upload_file' => ($fileRequired ? 'required' : 'nullable').'|array|min:0',
            'upload_file.*' => [
                'nullable',
                'file',
                new \App\Rules\FileAllowed(),
                'max:40960',
                new \App\Rules\SafeFile,
            ],
        ];

        if ($indikator->tipe === 'LED') {
            $rules['skor_auditee'] = 'required|integer|between:1,4';
        } elseif ($indikator->tipe === 'LKPS') {
            $rules['lkps_data'] = 'required|array';
            foreach ($indikator->indikatorInputs as $field) {
                $rules['lkps_data.'.$field->id] = 'required';
            }
        }

        $validated = $request->validate($rules,
            [
                'upload_file.required' => 'File bukti penilaian minimal 1 file harus diunggah.',
                'upload_file.*.max' => 'Ukuran maksimal file bukti penilaian adalah 20MB per file.',
            ]
        );
        $skorAuditee = null;

        if ($indikator->tipe === 'LED') {
            $skorAuditee = $validated['skor_auditee'];
        } elseif ($indikator->tipe === 'LKPS') {
            if ($indikator->formula_penilaian === null || trim($indikator->formula_penilaian) === '') {
                throw \Illuminate\Validation\ValidationException::withMessages(['lkps_data' => 'Formula penilaian untuk indikator LKPS ini belum ditetapkan. Silakan hubungi administrator sistem (P4MP).']);
            }
            // Panggil helper method untuk menghitung skor dari data LKPS
            $skorAuditee = $this->calculateScore($indikator, $validated['lkps_data']);
            if ($skorAuditee === null || is_nan($skorAuditee) || is_infinite($skorAuditee)) {
                // Pesan error jika formula tidak valid atau menghasilkan nilai tak terhingga
                throw \Illuminate\Validation\ValidationException::withMessages(['lkps_data' => 'Formula penilaian menghasilkan nilai tidak valid atau tidak dapat dihitung. Pastikan semua input data LKPS sudah benar dan sesuai dengan rumus.']);
            }
        }

        DB::beginTransaction();
        try {
            // updateOrCreate data utama
            $data = $this->model::updateOrCreate(
                [
                    'audit_periode_id' => $request->input('audit_periode_id'),
                    'indikator_id' => $id,
                ],
                [
                    'skor_auditee' => $skorAuditee,
                    'status_terkini' => config('master.content.hasil_audit.status_terkini.Draft'),
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
                        // Pastikan ekstensi tidak kosong
                        $ext = $file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'dat';
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                        // Tambahkan ekstensi ke nama hasil hash
                        $path = Storage::disk(config('filesystems.default'))
                            ->putFileAs(
                                $this->code.'/'.date('Y').'/'.date('m').'/'.date('d'),
                                $file,
                                $file->hashName().'.'.$ext
                            );

                        $data->file()->create([
                            'alias' => $originalName ?? 'file_'.time(),
                            'data' => [
                                'name' => basename($path),
                                'disk' => config('filesystems.default'),
                                'target' => $path,
                            ],
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Data berhasil diperbarui', 'redirect' => route($this->code.'.audit-kriteria', $request->input('audit_periode_id'))]);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                return response()->json(['status' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }

            return response()->json(['status' => false, 'message' => 'Data gagal diperbarui: '.$e->getMessage()], 500);
        }
    }

    public function deleteFile(Request $request)
    {
        // Pastikan request adalah AJAX dan user memiliki izin
        if (! $request->ajax() || ! auth()->user()->hasRole('Auditee')) { // Sesuaikan role atau permission
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $fileId = $request->input('file_id');
        $file = \App\Models\File::find($fileId); // Asumsi Anda punya model File untuk menyimpan detail file

        if (! $file) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {
            if (Storage::disk($file->disk)->exists($file->path)) {
                Storage::disk($file->disk)->delete($file->path);
            }

            $file->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'File berhasil dihapus.']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal menghapus file: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus file.'], 500);
        }
    }

    public function updateStatusIndikator(Request $request)
    {
        $request->validate([
            'indikator_id' => 'required|exists:indikators,id',
            'audit_periode_id' => 'required|exists:audit_periodes,id',
            'status' => 'required|in:Draft,Diajukan,Revisi,Selesai', // Sesuaikan dengan status yang Anda izinkan
        ]);

        DB::beginTransaction();
        try {
            $hasilAudit = $this->model::where('indikator_id', $request->indikator_id)
                ->where('audit_periode_id', $request->audit_periode_id)->first();
            if ($hasilAudit->status_terkini === 'Draft') {
                $catatan = 'Auditee mengajukan evaluasi diri.';
            } elseif ($hasilAudit->status_terkini === 'Revisi') {
                $catatan = 'Auditee memperbarui data evaluasi diri.';
            } else {
                $catatan = 'Status evaluasi diri diperbarui.';
            }

            $hasilAudit->update(['status_terkini' => $request->status]);

            $hasilAudit->logAktivitasAudit()->create([
                'tipe_aksi' => config('master.content.log_aktivitas_audit.tipe_aksi.SUBMIT_AWAL'),
                'user_id' => auth()->id(),
                'catatan_aksi' => $catatan,
            ]
            );
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Status indikator evaluasi berhasil diperbarui menjadi '.$request->status.'.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan saat memperbarui status.',
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
