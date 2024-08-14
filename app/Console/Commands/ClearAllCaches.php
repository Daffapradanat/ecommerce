<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class ClearAllCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all caches and other optimization tasks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('event:clear');
        $this->call('optimize:clear');
        $this->call('clear-compiled');
        $this->call('view:clear');

        $queueDriver = Config::get('queue.default');
        if ($queueDriver !== 'sync') {
            $this->call('queue:clear');
        } else {
            $this->info('Skipping queue:clear since the queue driver is sync.');
        }

        $this->call('route:cache');
        $this->call('route:clear');
        $this->call('route:list');
        $this->call('telescope:clear');

        $this->info('Clearing application logs...');
        $logFiles = File::files(storage_path('logs'));

        foreach ($logFiles as $file) {
            if (File::isFile($file)) {
                File::delete($file);
            }
        }

        $this->info('Application logs cleared.');

        $this->info('Running composer dump-autoload...');
        exec('composer dump-autoload', $output, $returnVar);

        if ($returnVar === 0) {
            $this->info('Composer dump-autoload completed successfully.');
        } else {
            $this->error('Composer dump-autoload failed.');
        }

        $this->info('All caches and optimizations have been cleared and listed.');

        return 0;
    }
}

// php artisan clear:all
