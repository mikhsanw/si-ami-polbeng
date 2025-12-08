<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupDatabaseToGoogle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 menit

    public function handle()
    {
        Log::info('🚀 Memulai backup database...');

        $connection = config('database.connections.mysql');
        $dbName = $connection['database'];
        $username = $connection['username'];
        $password = $connection['password'];
        $host = $connection['host'];

        $timestamp = Carbon::now()->format('Ymd_His');
        $backupName = "backup_db_{$dbName}_{$timestamp}.sql";
        $localPath = storage_path("app/{$backupName}");

        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s',
            $host,
            $username,
            $password,
            $dbName,
            storage_path("app/{$backupName}")
        );

        exec($command, $output, $result);

        if ($result !== 0) {
            Log::error("❌ Backup DB gagal: {$e->getMessage()}");

            return;
        }

        Log::info("📁 Dump sukses: {$backupName}");

        // Upload ke Google Drive
        $fileContent = Storage::disk('local')->get($localPath);
        $googlePath = "database/{$backupName}";

        Storage::disk('google')->put($googlePath, $fileContent);

        Log::info('📤 Upload backup DB ke Google Drive selesai!');
    }
}
