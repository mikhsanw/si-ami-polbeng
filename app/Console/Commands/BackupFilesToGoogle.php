<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupFilesToGoogle extends Command
{
    protected $signature = 'backup:google';

    protected $description = 'Backup semua file dari storage lokal ke Google Drive';

    public function handle()
    {
        $files = File::all();
        $this->info("Total file ditemukan: {$files->count()}");

        foreach ($files as $file) {
            $disk = $file->disk;
            $source = $file->target;

            if (! Storage::disk($disk)->exists($source)) {
                $this->warn("SKIP: File tidak ditemukan → {$source}");

                continue;
            }

            $content = Storage::disk($disk)->get($source);

            // Upload ke Google Drive
            Storage::disk('google')->put($source, $content);

            // Update metadata disk
            $data = $file->data;
            $data['disk'] = 'google';
            $file->update(['data' => $data]);

            $this->info("Migrasi OK: {$source}");
        }

        $this->info('=== Backup selesai tanpa error kritis ===');

        return Command::SUCCESS;
    }
}
