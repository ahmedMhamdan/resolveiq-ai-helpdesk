<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        $attachments = DB::table('ticket_attachments')->select(['id', 'file_path'])->lazyById();

        foreach ($attachments as $attachment) {
            $path = $attachment->file_path;

            if (! $public->exists($path)) {
                continue;
            }

            $stream = $public->readStream($path);

            if (! is_resource($stream)) {
                throw new RuntimeException("Could not read attachment: {$path}");
            }

            try {
                if (! $private->put($path, $stream)) {
                    throw new RuntimeException("Could not copy attachment: {$path}");
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            if (! $public->delete($path)) {
                throw new RuntimeException("Could not remove public attachment: {$path}");
            }
        }
    }

    public function down(): void
    {
        // Keep attachments private when rolling back database changes.
    }
};
