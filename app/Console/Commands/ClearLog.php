<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear log file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //
        $logPath = storage_path('logs');
        $files = File::glob($logPath . '/*.log'); // Get all .log files
        $totalLogFiles = count($files);
        $clearedLogFiles = 0;
        foreach ($files as $file) {
            try {
                file_put_contents($file, ''); // Empty the log files
                $clearedLogFiles++;
            } catch (\Exception $e) {
                // 
            }
        }
        $this->info("Cleared $clearedLogFiles out of $totalLogFiles log files");
        $this->info('All log files have been cleared.');
        /*
        file_put_contents(storage_path('logs/laravel.log'),'');
        $this->comment('Logs have been cleared!');
        */
        return 0;
    }
}
