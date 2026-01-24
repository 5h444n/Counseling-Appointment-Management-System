<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupOrphanedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cams:cleanup-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove orphaned appointment documents and resources from storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting orphaned file cleanup...');

        // 1. Cleanup Appointment Documents
        $this->cleanupFolder('appointment_documents', \App\Models\AppointmentDocument::class, 'file_path');

        // 2. Cleanup Resources (Library)
        $this->cleanupFolder('resources', \App\Models\Resource::class, 'file_path');

        $this->info('Cleanup complete.');
    }

    private function cleanupFolder($folder, $modelClass, $column)
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        
        if (!$disk->exists($folder)) {
            $this->warn("Folder '$folder' does not exist. Skipping.");
            return;
        }

        $files = $disk->allFiles($folder);
        $count = 0;

        $dbFiles = $modelClass::pluck($column)->toArray();

        foreach ($files as $file) {
            // Check if file exists in DB
            if (!in_array($file, $dbFiles)) {
                $disk->delete($file);
                $this->line("Deleted orphan: $file");
                $count++;
            }
        }

        $this->info("Cleaned up $count orphaned files from '$folder'.");
    }
}
