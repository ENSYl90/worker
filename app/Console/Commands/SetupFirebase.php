<?php

namespace App\Console\Commands;

use App\Traits\FirebaseAuthTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetupFirebase extends Command
{
    use FirebaseAuthTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-firebase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactively configure Firebase / notification settings (same fields as Settings > Notifications)';

    public function handle()
    {
        $appId = $this->ask('App ID', setting('appId', ''));
        $apiKey = $this->ask('API Key', setting('apiKey', ''));
        $projectId = $this->ask('Project ID', setting('projectId', ''));
        $firestoreDatabase = $this->ask('Firestore Database', setting('firestoreDatabase', '(default)'));
        $firestoreRelatedPrefix = $this->ask('Firestore Related Prefix', setting('firestoreRelatedPrefix', ''));
        $resourceLocation = $this->ask('Default GCP resource location', setting('resourceLocation', 'us-central1'));
        $messagingSenderId = $this->ask('Message Sender ID', setting('messagingSenderId', ''));
        $vapidKey = $this->ask('Web Push Key Pair (VAPID key)', setting('vapidKey', ''));

        $notifyAdmin = $this->confirm('Notify Admin for order updates?', (bool) setting('notifyAdmin', false));
        $notifyCityAdmin = $this->confirm('Notify CityAdmin for order updates?', (bool) setting('notifyCityAdmin', false));

        $useFCMJob = $this->confirm(
            'Communicate via Jobs (only enable if you are on a VPS with supervisor set up)?',
            (bool) setting('useFCMJob', false)
        );
        $delayFCMJobSeconds = $useFCMJob
            ? (int) $this->ask('Delay Time (seconds)', setting('delayFCMJobSeconds', 10))
            : (int) setting('delayFCMJobSeconds', 10);

        $firebaseSettingsArray = [
            'appId' => $appId,
            'apiKey' => $apiKey,
            'projectId' => $projectId,
            'firestoreDatabase' => $firestoreDatabase ?: '(default)',
            'firestoreRelatedPrefix' => $firestoreRelatedPrefix,
            'resourceLocation' => $resourceLocation,
            'messagingSenderId' => $messagingSenderId,
            'vapidKey' => $vapidKey,
            'notifyAdmin' => $notifyAdmin ? 1 : 0,
            'notifyCityAdmin' => $notifyCityAdmin ? 1 : 0,
            'useFCMJob' => $useFCMJob ? 1 : 0,
            'delayFCMJobSeconds' => $delayFCMJobSeconds,
        ];

        if ($this->confirm('Paste/update the Firebase Service Account JSON now?', true)) {
            $serviceKeyPath = $this->promptForServiceAccountJson();

            if ($serviceKeyPath === null) {
                return self::FAILURE;
            }

            if ($serviceKeyPath !== '') {
                $firebaseSettingsArray['serviceKeyPath'] = $serviceKeyPath;
            }
        }

        setting($firebaseSettingsArray)->save();

        $this->patchServiceWorker($messagingSenderId);

        $this->info('Firebase settings saved successfully.');

        return self::SUCCESS;
    }

    /**
     * Prompt for the service account JSON content (pasted, not uploaded),
     * validate it, and store it the same way the Notification Livewire
     * component does: storage/app/vault/firebase_service.json.
     *
     * @return string|null Relative storage path on success, "" if skipped, null on failure.
     */
    protected function promptForServiceAccountJson(): ?string
    {
        $this->line('Paste the full contents of the Firebase service account JSON file below.');
        $this->line('End input with a line containing only: EOF');

        $lines = [];
        while (true) {
            $line = $this->ask('');
            if ($line === null || trim((string) $line) === 'EOF') {
                break;
            }
            $lines[] = $line;
        }

        $json = trim(implode("\n", $lines));

        if ($json === '') {
            $this->warn('No content provided, skipping service account update.');
            return '';
        }

        json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('That is not valid JSON: ' . json_last_error_msg());
            return null;
        }

        $this->line('');
        $this->line('You provided the following content:');
        $this->line('---');
        $this->line($json);
        $this->line('---');

        $action = $this->choice('Continue with this content?', ['continue', 'cancel', 'reenter'], 0);

        if ($action === 'cancel') {
            $this->warn('Service account update cancelled.');
            return '';
        }

        if ($action === 'reenter') {
            return $this->promptForServiceAccountJson();
        }

        $nameChoice = $this->choice(
            'How should the service account file be named?',
            ['Use default name (firebase_service.json)', 'Generate a random name', 'Enter a custom name'],
            0
        );

        if ($nameChoice === 'Generate a random name') {
            $fileName = Str::random(32) . '.json';
        } elseif ($nameChoice === 'Enter a custom name') {
            $fileName = trim((string) $this->ask('File name to save as (will be placed under vault/)', 'firebase_service.json'));
        } else {
            $fileName = 'firebase_service.json';
        }

        if ($fileName === '') {
            $fileName = 'firebase_service.json';
        }

        //stores the service account on disk and in the database (fallback)
        $serviceKeyPath = $this->storeFirebaseServiceAccount($json, $fileName);

        $this->info("Service account saved to storage/app/{$serviceKeyPath}");

        // Clear the settings cache immediately so the read-back below (and
        // any other process reading settings) sees the new path/value
        // rather than a stale cached one.
        setting()->load(true);

        $serviceAccount = $this->getFirebaseServiceAccount();

        if ($serviceAccount === null) {
            $this->error("Could not read back the service account. Firebase will fail to initialize until this is fixed.");
        } else {
            $this->info('Verified: service account content was read back successfully.');
        }

        return $serviceKeyPath;
    }

    protected function patchServiceWorker(string $messagingSenderId): void
    {
        $fileName = base_path() . '/public/firebase-messaging-sw.js';

        if (!file_exists($fileName)) {
            $this->warn("Skipped service worker update: {$fileName} not found.");
            return;
        }

        $file = explode("\n", rtrim(file_get_contents($fileName)));
        $file[11] = "messagingSenderId: '" . $messagingSenderId . "',";
        file_put_contents($fileName, implode("\n", $file));

        $this->info('Updated public/firebase-messaging-sw.js with the new Message Sender ID.');
    }
}
