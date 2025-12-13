<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\BackupDatabaseToGoogle;
use App\Jobs\BackupFileToGoogle;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'tahun_akademik' => $this->help->generateTahunAkademikOptions(),
            'tahun_akademik_active' => (date('Y') - 1).'/'.date('Y'),
            'status' => \App\Models\AuditPeriode::where('tahun_akademik', (date('Y') - 1).'/'.date('Y'))->where('status', 0)->exists() ? 0 : 1,
        ];
        $backups = \App\Models\File::whereNotNull('data->backup')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($f) {
                return [
                    'id' => $f->id,
                    'nama' => $f->name,
                    'path' => $f->target,
                    'status' => $f->data['backup']['status'] ?? 'unknown',
                    'time' => $f->data['backup']['time'] ?? $f->updated_at,
                ];
            });

        //tambahkan file backup database terakhir, langsung check di storage google
        // cek jika tidak ada folder database maka skip
        $latestDbBackup = null;
        if (Storage::disk('google')->exists('/database/')) {
            $googleFiles = Storage::disk('google')->files('/database/');
            foreach ($googleFiles as $filePath) {
                if (str_starts_with(basename($filePath), 'backup_db_')) {
                    if ($latestDbBackup === null || $filePath > $latestDbBackup['path']) {
                        $latestDbBackup = [
                            'id' => null,
                            'nama' => basename($filePath),
                            'path' => $filePath,
                            'status' => 'done',
                            'time' => Storage::disk('google')->lastModified($filePath),
                        ];
                    }
                }
            }
            if ($latestDbBackup) {
                $backups->prepend($latestDbBackup);
            }
        }

        return view('backend.settings.index', compact('backups', 'settings'));
    }

    public function backupFiles(Request $request)
    {
        //backup database
        BackupDatabaseToGoogle::dispatch();

        // backup file
        $files = File::whereNull('data->backup->status')->get();

        if ($files->isEmpty()) {
            return response()->json(['message' => 'Semua file sudah dibackup']);
        }

        $jobs = $files->map(fn ($f) => new BackupFileToGoogle($f))->toArray();
        Bus::batch($jobs)->dispatch();

        return response()->json([
            'status' => 'success',
            'message' => 'Backup dalam proses...',
            'total' => count($jobs),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'tahun_akademik' => 'required|string',
            'status' => 'nullable|in:0,1',
        ]);

        $tahunAkademik = $request->tahun_akademik;
        $status = $request->status;

        // Update status baru jika dipilih
        if ($status !== null) {
            \App\Models\AuditPeriode::where('tahun_akademik', $tahunAkademik)
                ->update(['status' => $status]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan.',
        ]);
    }
}
