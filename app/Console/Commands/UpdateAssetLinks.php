<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateAssetLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamic-link:update-assetlinks
                            {--package= : Android package name}
                            {--sha256= : SHA256 certificate fingerprint}
                            {--interactive : Prompt for values interactively}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update assetlinks.json file with current environment configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating assetlinks.json file...');

        // Check if interactive mode is requested
        if ($this->option('interactive') || (!$this->option('package') && !$this->option('sha256'))) {
            $packageName = $this->getPackageNameInteractively();
            $sha256Fingerprint = $this->getSha256FingerprintInteractively();
        } else {
            // Get values from options or environment
            $packageName = $this->option('package') ?: env('dynamic_link.android', 'online.edentech.fuodz');
            $sha256Fingerprint = $this->option('sha256') ?: env('dynamic_link.android_sha256', 'REPLACE_WITH_YOUR_APP_SHA256_FINGERPRINT');

            // Validate SHA256 format if provided
            if ($this->option('sha256') && !$this->isValidSha256($sha256Fingerprint)) {
                $this->error('Invalid SHA256 fingerprint format. Expected format: AA:BB:CC:...');
                return Command::FAILURE;
            }
        }

        $result = $this->updateAssetLinksWithValues($packageName, $sha256Fingerprint);

        if ($result['success']) {
            $this->info($result['message']);
            $this->line('File path: ' . $result['path']);

            $this->newLine();
            $this->info('Configuration used:');
            $this->line('Package: ' . $packageName);
            $this->line('SHA256: ' . $sha256Fingerprint);

            if ($sha256Fingerprint === 'REPLACE_WITH_YOUR_APP_SHA256_FINGERPRINT' || empty($sha256Fingerprint)) {
                $this->newLine();
                $this->warn('⚠️  Using placeholder SHA256 fingerprint. Update with actual value:');
                $this->line('php artisan dynamic-link:update-assetlinks --sha256=YOUR_ACTUAL_SHA256');
            }

            if ($this->option('package') || $this->option('sha256')) {
                $this->newLine();
                $this->info('💡 To make these values permanent, update your .env file:');
                if ($this->option('package')) {
                    $this->line('dynamic_link.android=' . $packageName);
                }
                if ($this->option('sha256')) {
                    $this->line('dynamic_link.android_sha256=' . $sha256Fingerprint);
                }
            }
        } else {
            $this->error($result['message']);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Update assetlinks.json with specific values
     */
    private function updateAssetLinksWithValues($packageName, $sha256Fingerprint)
    {
        $assetLinks = [
            [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => $packageName,
                    'sha256_cert_fingerprints' => [
                        $sha256Fingerprint
                    ]
                ]
            ]
        ];

        $filePath = public_path('.well-known/assetlinks.json');

        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Write the JSON file with pretty formatting
        $jsonContent = json_encode($assetLinks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (file_put_contents($filePath, $jsonContent)) {
            return [
                'success' => true,
                'message' => 'assetlinks.json updated successfully',
                'path' => $filePath,
                'content' => $assetLinks
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update assetlinks.json',
            'path' => $filePath
        ];
    }

    /**
     * Validate SHA256 fingerprint format
     */
    private function isValidSha256($fingerprint)
    {
        // SHA256 fingerprint format: XX:XX:XX:... (32 pairs of hex digits separated by colons)
        return preg_match('/^([A-Fa-f0-9]{2}:){31}[A-Fa-f0-9]{2}$/', $fingerprint);
    }

    /**
     * Get package name interactively
     */
    private function getPackageNameInteractively()
    {
        $currentPackage = env('dynamic_link.android', 'online.edentech.fuodz');

        $this->newLine();
        $this->info('📱 Android Package Configuration');
        $this->line('Current package: ' . $currentPackage);

        $packageName = $this->ask(
            'Enter Android package name (or press Enter to use current)',
            $currentPackage
        );

        if (empty($packageName)) {
            $packageName = $currentPackage;
        }

        return $packageName;
    }

    /**
     * Get SHA256 fingerprint interactively
     */
    private function getSha256FingerprintInteractively()
    {
        $currentSha256 = env('dynamic_link.android_sha256', 'REPLACE_WITH_YOUR_APP_SHA256_FINGERPRINT');

        $this->newLine();
        $this->info('🔑 SHA256 Certificate Fingerprint Configuration');
        $this->line('Current SHA256: ' . $currentSha256);

        $this->newLine();
        $this->comment('💡 To get your SHA256 fingerprint, run:');
        $this->line('   Debug: keytool -list -v -keystore ~/.android/debug.keystore -alias androiddebugkey -storepass android -keypass android');
        $this->line('   Release: keytool -list -v -keystore path/to/release.keystore -alias your_alias');

        do {
            $sha256Fingerprint = $this->ask(
                'Enter SHA256 fingerprint (format: AA:BB:CC:... or press Enter to use current)',
                $currentSha256
            );

            if (empty($sha256Fingerprint)) {
                $sha256Fingerprint = $currentSha256;
                break;
            }

            if ($this->isValidSha256($sha256Fingerprint)) {
                break;
            } else {
                $this->error('Invalid SHA256 format. Expected: AA:BB:CC:DD:EE:FF:... (32 pairs of hex digits)');
                $this->line('Example: AA:BB:CC:DD:EE:FF:00:11:22:33:44:55:66:77:88:99:AA:BB:CC:DD:EE:FF:00:11:22:33:44:55:66:77:88:99');
            }
        } while (true);

        return $sha256Fingerprint;
    }
}
