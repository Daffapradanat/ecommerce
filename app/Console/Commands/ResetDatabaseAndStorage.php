<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class ResetDatabaseAndStorage extends Command
{
    protected $signature = 'reset:all';
    protected $description = 'Reset database and clear storage files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Resetting database...');
        Artisan::call('migrate:fresh');
        $this->info('Database has been reset.');

        $this->info('Clearing storage...');
        $this->clearStorage();
        $this->info('Storage has been cleared.');
    }

    protected function clearStorage()
    {
        // Define the directories to clear
        $directories = [
            'public' // Adjust this based on your storage structure
        ];

        foreach ($directories as $directory) {
            $fullPath = storage_path('app/' . $directory);

            // Delete all files and directories
            $this->deleteDirectory($fullPath);
        }
    }

    protected function deleteDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = array_diff(scandir($directory), ['.', '..']);

        foreach ($files as $file) {
            $filePath = $directory . DIRECTORY_SEPARATOR . $file;

            if (is_dir($filePath)) {
                $this->deleteDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }

        rmdir($directory);
    }
}

// php artisan reset:all
