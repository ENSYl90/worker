<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncMigrationRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrations:sync-records {--dry-run : List missing records without inserting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record migration files that exist locally but are missing from the migrations table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $table = config('database.migrations', 'migrations');

        if (! Schema::hasTable($table)) {
            $this->error("The {$table} table does not exist. Run the normal migrations first.");

            return self::FAILURE;
        }

        $migrationFiles = glob(database_path('migrations/*_*.php')) ?: [];
        $migrationNames = collect($migrationFiles)
            ->map(fn (string $file) => pathinfo($file, PATHINFO_FILENAME))
            ->sort()
            ->values();

        $recordedMigrations = DB::table($table)->pluck('migration');
        $missingMigrations = $migrationNames->diff($recordedMigrations)->values();

        if ($missingMigrations->isEmpty()) {
            $this->info('Every local migration file is already recorded.');

            return self::SUCCESS;
        }

        $this->warn("Found {$missingMigrations->count()} migration file(s) missing from the {$table} table:");
        foreach ($missingMigrations as $migration) {
            $this->line("  - {$migration}");
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run complete; no migration records were inserted.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($table, $missingMigrations): void {
            $batch = ((int) DB::table($table)->max('batch')) + 1;

            DB::table($table)->insert(
                $missingMigrations
                    ->map(fn (string $migration) => [
                        'migration' => $migration,
                        'batch' => $batch,
                    ])
                    ->all()
            );
        });

        $this->info("Recorded {$missingMigrations->count()} migration file(s) in batch ".(((int) DB::table($table)->max('batch'))).'.');

        return self::SUCCESS;
    }
}
