<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabaseToGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database to Google Drive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Memulai backup database ...');

        $connection = config('database.connections.mysql');

        $dbName = $connection['database'];
        $username = $connection['username'];
        $password = $connection['password'];
        $host = $connection['host'];

        $timestamp = Carbon::now()->format('Ymd_His');
        $backupName = "backup_db_{$dbName}_{$timestamp}.sql";
        $localPath = "{$backupName}";

        // Perintah dump DB
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s',
            $host,
            $username,
            $password,
            $dbName,
            storage_path("app/{$localPath}")
        );

        exec($command, $output, $result);

        if ($result !== 0) {
            $this->error('❌ Gagal dump database!');

            return Command::FAILURE;
        }

        $this->info("📁 Dump selesai: {$localPath}");

        // Upload ke Google Drive
        $fileContent = Storage::disk('local')->get($localPath);
        $googlePath = "database/{$backupName}";

        Storage::disk('google')->put($googlePath, $fileContent);

        $this->info('📤 Database berhasil diupload ke Google Drive!');

        return Command::SUCCESS;
    }
}
