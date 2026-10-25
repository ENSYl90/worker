<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwitchEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:switch-env {mode? : local, staging, or production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Switch APP_ENV between local, staging, and production, then clear config/route/view/cache';

    protected array $allowedModes = ['local', 'staging', 'production'];

    public function handle()
    {
        $mode = $this->argument('mode');

        if ($mode === null) {
            $mode = $this->choice('Which environment do you want to switch to?', $this->allowedModes, array_search(
                app()->environment(),
                $this->allowedModes,
                true
            ) ?: 0);
        }

        if (!in_array($mode, $this->allowedModes, true)) {
            $this->error("Invalid mode \"{$mode}\". Expected one of: " . implode(', ', $this->allowedModes));
            return self::FAILURE;
        }

        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->error('.env file not found at ' . $envPath);
            return self::FAILURE;
        }

        $current = app()->environment();

        if ($current === $mode) {
            $this->info("APP_ENV is already \"{$mode}\".");
        } else {
            $contents = file_get_contents($envPath);

            if (preg_match('/^APP_ENV=.*$/m', $contents)) {
                $contents = preg_replace('/^APP_ENV=.*$/m', "APP_ENV={$mode}", $contents);
            } else {
                $contents = rtrim($contents) . "\nAPP_ENV={$mode}\n";
            }

            file_put_contents($envPath, $contents);

            $this->info("APP_ENV switched: {$current} -> {$mode}");
        }

        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('cache:clear');

        if ($mode === 'production') {
            $this->call('config:cache');
            $this->call('route:cache');
            $this->call('view:cache');
            $this->info('Re-cached config/routes/views for production.');
        }

        return self::SUCCESS;
    }
}
