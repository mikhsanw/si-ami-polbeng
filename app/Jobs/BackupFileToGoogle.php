<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BackupFileToGoogle implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public File $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function handle()
    {
        if (! $this->file->exists) {
            return;
        }

        $content = Storage::disk($this->file->disk)->get($this->file->target);
        Storage::disk('google')->put($this->file->target, $content);

        $data = $this->file->data;
        $data['backup'] = [
            'disk' => 'google',
            'path' => $this->file->target,
            'status' => 'done',
            'synced_at' => now()->toDateTimeString(),
        ];

        $this->file->update(['data' => $data]);
    }
}
