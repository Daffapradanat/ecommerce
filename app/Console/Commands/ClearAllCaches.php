<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

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
        $this->call('view:clear');

        $this->info('All caches and optimizations have been cleared and listed.');

        return 0;
    }
}

// php artisan clear:all
