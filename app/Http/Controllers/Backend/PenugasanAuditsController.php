<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditPeriode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenugasanAuditsController extends Controller
{
    public function index(Request $request)
    {
        // Pengecekan izin untuk auditor
        if (! auth()->user()->hasPermissionTo($this->code.' list')) {
            $auditperiodes = collect();

            return view($this->view.'.index', compact('auditperiodes'));
        }

        // --- Perbedaan Utama untuk Auditor ---
        // Auditor melihat audit dari SEMUA unit, bukan hanya unitnya sendiri.
        // Jika auditor hanya bertanggung jawab atas unit tertentu, filter di sini.
        // Contoh: Jika user auditor punya relasi `auditedUnits`
        // $auditedUnitIds = auth()->user()->auditedUnits()->pluck('id')->toArray();
        // $query = AuditPeriode::whereIn('unit_id', $auditedUnitIds);

        // Untuk contoh ini, kita asumsikan auditor melihat semua periode audit aktif
        $query = AuditPeriode::where('status', true)->where(function ($q) {
            // Hanya tampilkan periode audit yang memiliki penugasan auditor
            $q->whereHas('penugasanAuditors', function ($subQ) {
                $subQ->where('user_id', auth()->id());
            });
        });

        // Ambil periode audit dengan eager load yang sama
        $auditperiodes = $query->with([
            'unit',
            'instrumenTemplate.templateIndikators',
            'hasilAudits',
        ])
            ->get();

        foreach ($auditperiodes as $periode) {
            $template = $periode->instrumenTemplate;

            // Inisialisasi default
            $periode->total_indikator = 0;
            $statusCounts = [
                'belum_dikerjakan' => 0,
                'draft_dikerjakan' => 0,
                'diajukan' => 0,
                'revisi' => 0,
                'selesai' => 0,
                'total_terisi' => 0,
            ];
            $periode->overall_progress = 0;

            // Default status untuk auditor, mungkin lebih fokus ke 'Menunggu Aksi'
            $periode->statusText = 'Belum Ada Pengajuan';
            $periode->statusClass = 'text-bg-secondary'; // Warna abu-abu

            if (! $template || $template->templateIndikators->isEmpty()) {
                $periode->statusText = 'Instrumen Tidak Ditemukan';
                $periode->statusClass = 'text-bg-secondary';
                $periode->status_counts = $statusCounts;

                continue;
            }

            $totalIndikatorDalamTemplate = $template->templateIndikators->count();
            $periode->total_indikator = $totalIndikatorDalamTemplate;

            $allHasilAudits = $periode->hasilAudits;

            foreach ($allHasilAudits as $hasilAudit) {
                switch ($hasilAudit->status_terkini) {
                    case 'DRAFT':
                        $statusCounts['draft_dikerjakan']++;
                        break;
                    case 'DIAJUKAN':
                        $statusCounts['diajukan']++;
                        break;
                    case 'REVISI':
                        $statusCounts['revisi']++;
                        break;
                    case 'SELESAI':
                        $statusCounts['selesai']++;
                        break;
                    default:
                        // Indikator tanpa status (atau status tidak dikenal) dianggap draft
                        $statusCounts['draft_dikerjakan']++;
                        break;
                }
            }

            $statusCounts['total_terisi'] =
                $statusCounts['draft_dikerjakan'] +
                $statusCounts['diajukan'] +
                $statusCounts['revisi'] +
                $statusCounts['selesai'];

            $statusCounts['belum_dikerjakan'] =
                $totalIndikatorDalamTemplate - $statusCounts['total_terisi'];

            $periode->status_counts = $statusCounts;

            $periode->overall_progress = ($totalIndikatorDalamTemplate > 0)
                                        ? round(($periode->status_counts['total_terisi'] / $totalIndikatorDalamTemplate) * 100)
                                        : 0;

            // --- Logika Penentuan Status UTAMA untuk AUDITOR ---
            if ($periode->status_counts['revisi'] > 0) {
                // Prioritas tertinggi: ada yang harus direvisi oleh auditee
                $periode->statusText = 'Ada Revisi untuk Unit';
                $periode->statusClass = 'text-white bg-danger'; // Merah, butuh perhatian
            } elseif ($periode->status_counts['diajukan'] > 0) {
                // Prioritas kedua: ada yang menunggu verifikasi dari auditor
                $periode->statusText = 'Menunggu Verifikasi Anda';
                $periode->statusClass = 'text-white bg-info'; // Biru, butuh aksi auditor
            } elseif ($periode->status_counts['selesai'] == $totalIndikatorDalamTemplate && $totalIndikatorDalamTemplate > 0) {
                // Semua indikator sudah selesai/diterima
                $periode->statusText = 'Audit Selesai & Diterima';
                $periode->statusClass = 'text-white bg-success'; // Hijau, aman
            } elseif ($periode->overall_progress > 0) {
                // Ada progres tapi belum diajukan/revisi/selesai
                $periode->statusText = 'Unit Sedang Bekerja';
                $periode->statusClass = 'text-white bg-warning'; // Kuning, sedang berlangsung
            } else {
                // Belum ada aksi sama sekali dari unit
                $periode->statusText = 'Belum Ada Progres';
                $periode->statusClass = 'text-bg-secondary'; // Abu-abu
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
                            'data' => [
                                'name' => $file->hashName(),
                                'disk' => config('filesystems.default'),
                                'target' => Storage::disk(config('filesystems.default'))->putFile(
                                    $this->code.'/'.date('Y').'/'.date('m').'/'.date('d'),
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
            $response = ['status' => true, 'message' => 'Data berhasil disimpan'];
        }

        return response()->json($response ?? ['status' => false, 'message' => 'Data gagal disimpan']);
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
            'catatan_auditor.required' => 'Catatan wajib diisi saat meminta revisi.',
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
            } else { // Aksi adalah 'minta_revisi'
                $hasilAudit->status_terkini = 'Revisi';
                $tipeAksiLog = 'MINTA_REVISI';
            }

            // Simpan perubahan pada HasilAudit
            $hasilAudit->save();

            // 5. Buat entri baru di log aktivitas
            $hasilAudit->logAktivitasAudit()->create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(), // ID Auditor yang sedang login
                'tipe_aksi' => $tipeAksiLog,
                'catatan_aksi' => $catatan,
            ]);

            DB::commit(); // Konfirmasi semua perubahan jika berhasil

            $response = [
                'status' => true,
                'message' => 'Keputusan validasi berhasil disimpan.',
                'redirect' => route($this->code.'.audit-kriteria', $request->input('audit_periode_id')), // Redirect kembali ke dasbor proses
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua jika terjadi error

            $response = [
                'status' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server.',
            ];

            return response()->json($response, 500);
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
