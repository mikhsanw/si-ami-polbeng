<?php

namespace App\Http\Controllers\Backend\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class RingkasanTemuanAuditController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $user = $request->user();
        if ($request->ajax()) {
            $id = $id ?? $request->get('id');
            $data = $this->model::with(['indikator', 'auditPeriode', 'logAktivitasAudit', 'indikator.kriteria'])
                ->whereHas('auditPeriode', function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->where(function ($q) {
                    $q->whereHas('auditPeriode.instrumenTemplate.lembagaAkreditasi', function ($sub) {
                        $sub->where('singkatan', '!=', 'LAMEMBA');
                    })->where('skor_final', '<', 4)
                        ->orWhereHas('auditPeriode.instrumenTemplate.lembagaAkreditasi', function ($sub) {
                            $sub->where('singkatan', 'LAMEMBA');
                        })->where('skor_final', 0);
                })
                ->get();

            return datatables()->of($data)
                ->addColumn('periode', function ($data) {
                    return $data->auditPeriode->periode_unit;
                })
                ->addColumn('action', function ($data) use ($user) {
                    $button = '';
                    $button .= '<button type="button" class="btn-action btn btn-sm btn-light-primary" data-title="Detail" data-action="show" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Tampilkan"><i class="fa fa-eye text-info"></i></button>';
                    if (in_array('Super Admin', $user->getRoleNames()->toArray() ?? [])) {
                        if (auth()->user()->hasRole('Super Admin')) {
                            $button .= '<a type="button" class="btn btn-sm btn-light-warning btn-action" data-title="Edit" data-action="edit" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Edit"> <i class="fa fa-edit text-warning"></i> </a> ';
                            $button .= '<button type="button" class="btn-action btn btn-sm btn-light-danger" data-title="Delete" data-action="delete" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Delete"> <i class="fa fa-trash text-danger"></i> </button>';
                        }
                    } else {
                        if ($user->hasPermissionTo($this->code.' edit')) {
                            $button .= '<a type="button" class="btn btn-sm btn-light-warning btn-action" data-title="Edit" data-action="edit" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Edit"> <i class="fa fa-edit text-warning"></i> </a> ';
                        }
                        if ($user->hasPermissionTo($this->code.' delete')) {
                            $button .= '<button type="button" class="btn-action btn btn-sm btn-light-danger" data-title="Delete" data-action="delete" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Delete"> <i class="fa fa-trash text-danger"></i> </button>';
                        }
                    }

                    return "<div class='btn-group'>".$button.'</div>';
                })
                ->editColumn('status_terkini', function ($data) {
                    return $data->status_terkini === 'Selesai' ? '<span class="badge badge-light-success">Selesai</span>' : ($data->status_terkini === 'Revisi' ? '<span class="badge badge-light-danger">Revisi Diperlukan</span>' : ($data->status_terkini === 'Diajukan' ? '<span class="badge badge-light-warning">Menunggu Validasi</span>' : '<span class="badge badge-light">Belum Dikerjakan</span>'));
                })
                ->addIndexColumn()
                ->rawColumns(['action', 'status_terkini'])
                ->make();
        }

        if ($user->hasRole(['Super Admin', 'Admin'])) {
            // Ambil semua audit periode
            $data = \App\Models\AuditPeriode::orderBy('created_at')
                ->get()
                ->pluck('periode_unit', 'id')
                ->toArray();
        } else {
            // Ambil hanya audit periode yang ditugaskan ke user
            $data = \App\Models\AuditPeriode::orderBy('created_at')
                ->whereHas('penugasanAuditors', fn ($query) => $query->where('user_id', $user->id))
                ->get()
                ->pluck('periode_unit', 'id')
                ->toArray();
        }
        $filterOptions = ['' => 'Pilih Periode Unit'] + $data;

        return view('backend.ringkasantemuanaudits.index', compact('filterOptions', 'id'));
    }

    public function generateForm4($id)
    {
        // Simulasi data dari database (bisa pakai model Audit)
        $user = auth()->user();
        $auditPeriode = \App\Models\AuditPeriode::find($id);
        $data = $this->model::with(['indikator', 'auditPeriode', 'auditPeriode.unit', 'logAktivitasAudit', 'indikator.kriteria'])
            ->whereHas('auditPeriode', function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->where(function ($q) use ($user) {
                $q->whereHas('auditPeriode.penugasanAuditors', fn ($query) => $query->where('user_id', $user->id));
                // ->orWhereHas('auditPeriode.unit', fn ($query2) => $query2->where('user_id', $user->id));
            })
            ->where(function ($q) {
                $q->whereHas('auditPeriode.instrumenTemplate.lembagaAkreditasi', function ($sub) {
                    $sub->where('singkatan', '!=', 'LAMEMBA');
                })->where('skor_final', '<', 4)
                    ->orWhereHas('auditPeriode.instrumenTemplate.lembagaAkreditasi', function ($sub) {
                        $sub->where('singkatan', 'LAMEMBA');
                    })->where('skor_final', 0);
            })
            ->get();

        $dasar = [
            'auditi' => $auditPeriode->unit->nama,
            'kriteria' => 'Standar -',
            'prodi' => $auditPeriode->unit->nama,
            'lokasi' => 'Politeknik Negeri Bengkalis',
            'ruang_lingkup' => $auditPeriode->periode_unit,
            'tanggal_audit' => date('d F Y'),
            'wakil_auditi' => $auditPeriode->unit->user->name ?? 'N/A',
            'auditor_ketua' => $auditPeriode->penugasanAuditors()->first()->user->name ?? 'N/A',
            'auditor_anggota' => $auditPeriode->penugasanAuditors()->skip(1)->take(2)->get()->pluck('user.name')->implode("\n"),
            'distribusi_auditi' => '✔',
            'distribusi_auditor' => '✔',
            'distribusi_lpm' => '✔',
            'distribusi_arsip' => '✔',
        ];

        $temuan = [];
        // Tabel temuan dinamis (bisa ambil dari tabel audit_temuan)
        foreach ($data as $key => $value) {
            $q['no'] = $key + 1;
            $q['urutan'] = $value->indikator->kriteria->kode;
            $q['temuan'] = $value->catatan_final;
            $q['kategori'] = 'KTS'; // atau 'KTS'
            $temuan[] = $q;
        }

        // Load template
        $template = new TemplateProcessor(storage_path('app/templates/Form4-template.docx'));

        // Set field tunggal
        foreach ($dasar as $key => $value) {
            $template->setValue($key, $value);
        }

        // Clone tabel temuan
        $template->cloneRow('no', count($temuan));
        foreach ($temuan as $index => $item) {
            $i = $index + 1;
            $template->setValue("no#{$i}", $item['no']);
            $template->setValue("urutan#{$i}", $item['urutan']);
            $template->setValue("temuan#{$i}", htmlspecialchars($item['temuan']));
            $template->setValue("kategori#{$i}", $item['kategori']);
        }
        // Simpan hasil
        $outputPath = storage_path('app/public/form4_'.time().'.docx');
        $template->saveAs($outputPath);
        unset($template);

        ob_end_clean();

        return response()->download($outputPath);
    }
}
