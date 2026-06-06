<?php

namespace App\Http\Controllers\Backend\Laporan;

use App\Http\Controllers\Controller;
use App\Models\AuditPeriode;
use App\Models\HasilAudit;
use App\Models\LogAktivitasAudit;
use App\Models\PenilaianAuditor;
use App\Models\PenugasanAuditor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformaAuditorController extends Controller
{
    // ───────────────────────────────────────────────────────────────
    // Daftar auditor + metrik ringkasan
    // ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $auditors = User::role('Auditor')->get(['id', 'name', 'email']);

            $rows = $auditors->map(function ($user) {
                $periodeIds   = PenugasanAuditor::where('user_id', $user->id)->pluck('audit_periode_id');
                $hasilIds     = HasilAudit::whereIn('audit_periode_id', $periodeIds)->pluck('id');
                $metrik       = $this->hitungMetrik($user->id, $hasilIds);

                $avgPenilaian = PenilaianAuditor::where('auditor_id', $user->id)
                    ->whereNull('deleted_at')
                    ->avg('skor_keseluruhan');

                return [
                    'id'              => $user->id,
                    'nama'            => $user->name,
                    'email'           => $user->email,
                    'total_penugasan' => $periodeIds->count(),
                    'pct_responsivitas' => $metrik['pct_responsivitas'],
                    'avg_hari_respon' => $metrik['avg_hari_respon'],
                    'pct_kecepatan'   => $metrik['pct_kecepatan'],
                    'pct_catatan'     => $metrik['pct_catatan'],
                    'skor_keseluruhan' => $metrik['skor_keseluruhan'],
                    'avg_penilaian'   => $avgPenilaian ? round($avgPenilaian, 1) : null,
                ];
            });

            return datatables()->of($rows)
                ->addColumn('action', function ($row) {
                    return '<a href="'.url(config('master.app.url.backend').'/performaauditors/'.$row['id'].'/show').'"
                        class="btn btn-sm btn-light-primary">
                        <i class="ki-duotone ki-eye fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        Detail
                    </a>';
                })
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make();
        }

        return view($this->view.'.index');
    }

    // ───────────────────────────────────────────────────────────────
    // Halaman detail per auditor
    // ───────────────────────────────────────────────────────────────

    public function show($userId)
    {
        $auditor    = User::findOrFail($userId);
        $periodeIds = PenugasanAuditor::where('user_id', $userId)->pluck('audit_periode_id');

        // Metrik otomatis keseluruhan
        $allHasilIds  = HasilAudit::whereIn('audit_periode_id', $periodeIds)->pluck('id');
        $metrikTotal  = $this->hitungMetrik($userId, $allHasilIds);
        $totalPenugasan = $periodeIds->count();

        // Rincian per periode
        $periodeDetail = AuditPeriode::with(['unit', 'hasilAudits'])
            ->whereIn('id', $periodeIds)
            ->latest()
            ->get()
            ->map(function ($periode) use ($userId) {
                $hasilIds = $periode->hasilAudits->pluck('id');
                $m = $this->hitungMetrik($userId, $hasilIds);

                return (object) [
                    'tahun_akademik'    => $periode->tahun_akademik,
                    'unit'              => $periode->unit->nama ?? '-',
                    'total_hasil'       => $hasilIds->count(),
                    'pct_responsivitas' => $m['pct_responsivitas'],
                    'avg_hari_respon'   => $m['avg_hari_respon'],
                    'pct_kecepatan'     => $m['pct_kecepatan'],
                    'pct_catatan'       => $m['pct_catatan'],
                    'skor_keseluruhan'  => $m['skor_keseluruhan'],
                ];
            });

        // Daftar penilaian tersimpan
        $penilaians = PenilaianAuditor::with(['penilai', 'auditPeriode.unit'])
            ->where('auditor_id', $userId)
            ->latest()
            ->get();

        // Opsi periode untuk form
        $periodeOptions = AuditPeriode::with('unit')
            ->whereIn('id', $periodeIds)
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => ($p->unit->nama ?? '?').' — '.$p->tahun_akademik]);

        return view($this->view.'.show', compact(
            'auditor',
            'totalPenugasan',
            'metrikTotal',
            'periodeDetail',
            'penilaians',
            'periodeOptions',
        ));
    }

    // ───────────────────────────────────────────────────────────────
    // CRUD penilaian
    // ───────────────────────────────────────────────────────────────

    public function create()
    {
        $auditors = User::role('Auditor')->get(['id', 'name']);

        // Jika auditor sudah dipilih (dari URL ?auditor_id=), tampilkan hanya periode yang ditugaskan ke auditor tersebut
        $auditorId = request()->query('auditor_id');
        if ($auditorId) {
            $periodeIds = PenugasanAuditor::where('user_id', $auditorId)->pluck('audit_periode_id');
            $periodes   = AuditPeriode::with('unit')->whereIn('id', $periodeIds)->latest()->get();
        } else {
            $periodes = AuditPeriode::with('unit')->latest()->get();
        }

        return view($this->view.'.form', compact('auditors', 'periodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'auditor_id'       => 'required|exists:users,id',
            'audit_periode_id' => 'required|exists:audit_periodes,id',
            'catatan'          => 'nullable|string|max:2000',
        ]);

        $hasilIds = HasilAudit::where('audit_periode_id', $request->audit_periode_id)->pluck('id');
        $metrik   = $this->hitungMetrik($request->auditor_id, $hasilIds);

        PenilaianAuditor::create([
            'auditor_id'        => $request->auditor_id,
            'penilai_id'        => auth()->id(),
            'audit_periode_id'  => $request->audit_periode_id,
            'pct_responsivitas' => $metrik['pct_responsivitas'],
            'avg_hari_respon'   => $metrik['avg_hari_respon'],
            'pct_kecepatan'     => $metrik['pct_kecepatan'],
            'pct_catatan'       => $metrik['pct_catatan'],
            'skor_keseluruhan'  => $metrik['skor_keseluruhan'],
            'catatan'           => $request->catatan,
        ]);

        return response()->json([
            'status'   => true,
            'message'  => 'Penilaian berhasil disimpan.',
            'redirect' => url(config('master.app.url.backend').'/performaauditors/'.$request->auditor_id.'/show'),
        ]);
    }

    public function edit($id)
    {
        $penilaian = PenilaianAuditor::findOrFail($id);
        $auditors  = User::role('Auditor')->get(['id', 'name']);

        // Tampilkan periode yang ditugaskan ke auditor yang sedang diedit
        $periodeIds = PenugasanAuditor::where('user_id', $penilaian->auditor_id)->pluck('audit_periode_id');
        $periodes   = AuditPeriode::with('unit')->whereIn('id', $periodeIds)->latest()->get();

        return view($this->view.'.form', compact('penilaian', 'auditors', 'periodes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'auditor_id'       => 'required|exists:users,id',
            'audit_periode_id' => 'required|exists:audit_periodes,id',
            'catatan'          => 'nullable|string|max:2000',
        ]);

        $penilaian = PenilaianAuditor::findOrFail($id);

        $hasilIds = HasilAudit::where('audit_periode_id', $request->audit_periode_id)->pluck('id');
        $metrik   = $this->hitungMetrik($request->auditor_id, $hasilIds);

        $penilaian->update([
            'auditor_id'        => $request->auditor_id,
            'audit_periode_id'  => $request->audit_periode_id,
            'pct_responsivitas' => $metrik['pct_responsivitas'],
            'avg_hari_respon'   => $metrik['avg_hari_respon'],
            'pct_kecepatan'     => $metrik['pct_kecepatan'],
            'pct_catatan'       => $metrik['pct_catatan'],
            'skor_keseluruhan'  => $metrik['skor_keseluruhan'],
            'catatan'           => $request->catatan,
        ]);

        return response()->json([
            'status'   => true,
            'message'  => 'Penilaian berhasil diperbarui.',
            'redirect' => url(config('master.app.url.backend').'/performaauditors/'.$request->auditor_id.'/show'),
        ]);
    }

    public function delete($id)
    {
        $penilaian = PenilaianAuditor::with(['auditor', 'auditPeriode.unit'])->findOrFail($id);

        return view($this->view.'.delete', compact('penilaian'));
    }

    public function destroy($id)
    {
        $penilaian = PenilaianAuditor::findOrFail($id);
        $auditorId = $penilaian->auditor_id;
        $penilaian->delete();

        return response()->json([
            'status'   => true,
            'message'  => 'Penilaian berhasil dihapus.',
            'redirect' => url(config('master.app.url.backend').'/performaauditors/'.$auditorId.'/show'),
        ]);
    }

    // ───────────────────────────────────────────────────────────────
    // Preview AJAX untuk form (hitung metrik sebelum disimpan)
    // ───────────────────────────────────────────────────────────────

    public function preview(Request $request)
    {
        $request->validate([
            'auditor_id'       => 'required|exists:users,id',
            'audit_periode_id' => 'required|exists:audit_periodes,id',
        ]);

        $hasilIds = HasilAudit::where('audit_periode_id', $request->audit_periode_id)->pluck('id');
        $metrik   = $this->hitungMetrik($request->auditor_id, $hasilIds);

        return response()->json($metrik);
    }

    // ───────────────────────────────────────────────────────────────
    // Hitung 3 metrik performa (% Responsivitas, % Kecepatan, % Catatan)
    // berdasarkan kumpulan hasil_audit_id dan user_id auditor.
    // ───────────────────────────────────────────────────────────────

    private function hitungMetrik($userId, $hasilAuditIds): array
    {
        $ids   = collect($hasilAuditIds);
        $total = $ids->count();

        if ($total === 0) {
            return [
                'pct_responsivitas' => 0.0,
                'avg_hari_respon'   => null,
                'pct_kecepatan'     => 0.0,
                'pct_catatan'       => 0.0,
                'skor_keseluruhan'  => 0.0,
            ];
        }

        $aksiAuditor = ['MINTA_REVISI', 'VALIDASI', 'FINALISASI_SKOR'];

        // 1. % Responsivitas — berapa hasil_audit yang sudah direspon auditor
        $direspon = LogAktivitasAudit::where('user_id', $userId)
            ->whereIn('tipe_aksi', $aksiAuditor)
            ->whereIn('hasil_audit_id', $ids)
            ->distinct('hasil_audit_id')
            ->count('hasil_audit_id');

        $pctResponsivitas = round($direspon / $total * 100, 1);

        // 2. Durasi respon: selisih hari antara SUBMIT_AWAL → aksi pertama auditor
        $submitLogs = LogAktivitasAudit::whereIn('hasil_audit_id', $ids)
            ->where('tipe_aksi', 'SUBMIT_AWAL')
            ->select('hasil_audit_id', DB::raw('MIN(created_at) as t'))
            ->groupBy('hasil_audit_id')
            ->pluck('t', 'hasil_audit_id');

        $responseLogs = LogAktivitasAudit::whereIn('hasil_audit_id', $ids)
            ->where('user_id', $userId)
            ->whereIn('tipe_aksi', $aksiAuditor)
            ->select('hasil_audit_id', DB::raw('MIN(created_at) as t'))
            ->groupBy('hasil_audit_id')
            ->pluck('t', 'hasil_audit_id');

        $durations = [];
        foreach ($responseLogs as $hId => $respTime) {
            if (isset($submitLogs[$hId])) {
                $hari = Carbon::parse($submitLogs[$hId])->diffInHours(Carbon::parse($respTime)) / 24;
                if ($hari >= 0) {
                    $durations[] = $hari;
                }
            }
        }

        $avgHari = count($durations) > 0
            ? round(array_sum($durations) / count($durations), 1)
            : null;

        // Skor kecepatan: ≤2 hari = 100%, ≥14 hari = 0%
        $pctKecepatan = $avgHari !== null
            ? (float) max(0.0, round(100.0 - (max(0.0, $avgHari - 2.0) / 12.0 * 100.0), 1))
            : 0.0;

        // 3. % Catatan — berapa aksi auditor yang disertai catatan_aksi
        $totalAksi = LogAktivitasAudit::where('user_id', $userId)
            ->whereIn('tipe_aksi', $aksiAuditor)
            ->whereIn('hasil_audit_id', $ids)
            ->count();

        $aksiDenganCatatan = LogAktivitasAudit::where('user_id', $userId)
            ->whereIn('tipe_aksi', $aksiAuditor)
            ->whereIn('hasil_audit_id', $ids)
            ->whereNotNull('catatan_aksi')
            ->where('catatan_aksi', '!=', '')
            ->count();

        $pctCatatan = $totalAksi > 0
            ? round($aksiDenganCatatan / $totalAksi * 100, 1)
            : 0.0;

        $skor = round(($pctResponsivitas + $pctKecepatan + $pctCatatan) / 3, 1);

        return [
            'pct_responsivitas' => $pctResponsivitas,
            'avg_hari_respon'   => $avgHari,
            'pct_kecepatan'     => $pctKecepatan,
            'pct_catatan'       => $pctCatatan,
            'skor_keseluruhan'  => $skor,
        ];
    }
}
