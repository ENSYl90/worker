<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunUpgrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-upgrades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all pending App\Upgrades\UpgradeN classes in sequence, from the current appVerisonCode up to the latest available class';

    public function handle()
    {
        $currentCode = (int) setting('appVerisonCode', 1);

        $this->info("Current version code: {$currentCode}");

        $ranAny = false;

        while (true) {
            $nextCode = $currentCode + 1;
            $upgradeClassName = "App\\Upgrades\\Upgrade{$nextCode}";

            if (!class_exists($upgradeClassName)) {
                break;
            }

            $this->info("Running {$upgradeClassName}...");

            try {
                $upgradeClass = new $upgradeClassName();
                $upgradeClass->run();
                $upgradeClass->update();
            } catch (\Throwable $e) {
                $this->error("Upgrade {$upgradeClassName} failed: {$e->getMessage()}");
                logger()->error("RunUpgrades failed at {$upgradeClassName}", ['exception' => $e]);
                return self::FAILURE;
            }

            $this->info("{$upgradeClassName} completed. Now at version code {$nextCode}.");

            $currentCode = $nextCode;
            $ranAny = true;
        }

        if (!$ranAny) {
            $this->info('No pending upgrades. Already up to date.');
        } else {
            $this->info("All upgrades applied. Final version code: {$currentCode}.");
        }

        return self::SUCCESS;
    }
}
