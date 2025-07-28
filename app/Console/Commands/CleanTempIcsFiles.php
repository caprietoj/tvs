<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanTempIcsFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:clean-temp-ics {--older-than=1 : Remove files older than X hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean temporary ICS files used for calendar invitations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tempDir = storage_path('app/temp');
        $olderThan = (int) $this->option('older-than');
        
        if (!File::exists($tempDir)) {
            $this->info('No temp directory found. Nothing to clean.');
            return 0;
        }

        $files = File::glob($tempDir . '/*.ics');
        $cleaned = 0;
        $cutoffTime = now()->subHours($olderThan);

        foreach ($files as $file) {
            $fileModified = \Carbon\Carbon::createFromTimestamp(filemtime($file));
            
            if ($fileModified->lt($cutoffTime)) {
                File::delete($file);
                $cleaned++;
                $this->line("Deleted: " . basename($file));
            }
        }

        if ($cleaned > 0) {
            $this->info("Cleaned {$cleaned} temporary ICS files older than {$olderThan} hour(s).");
        } else {
            $this->info("No files to clean.");
        }

        return 0;
    }
}
