<?php

namespace App\Http\Livewire;

use Exception;

class DynamicLinkSettingsLivewire extends BaseLivewireComponent
{
    // App settings
    public $prefix;

    public $scheme;

    public $android;

    public $android_sha256_key;

    public $android_sha256_debug_key;

    public $android_sha256_release_key;

    public $ios;

    public $ios_id;

    public $rules = [
        'prefix' => 'required|url',
        'scheme' => 'required',
        'android_sha256_key' => 'required',
    ];

    public function mount()
    {
        $this->prefix = env('dynamic_link.prefix');
        $this->scheme = env('dynamic_link.scheme');
        $this->android = env('dynamic_link.android');
        $this->android_sha256_key = env('dynamic_link.android_sha256');
        $this->android_sha256_debug_key = env('dynamic_link.android_sha256_debug');
        $this->android_sha256_release_key = env('dynamic_link.android_sha256_release');
        $this->ios = env('dynamic_link.ios');
        $this->ios_id = env('dynamic_link.ios_id');
    }

    public function render()
    {
        return view('livewire.settings.dynamic-link-settings');
    }

    public function saveSettings()
    {

        try {

            $this->isDemo();
            $this->setEnvKey('dynamic_link.prefix', $this->prefix);
            $this->setEnvKey('dynamic_link.scheme', $this->scheme);
            $this->setEnvKey('dynamic_link.android', $this->android);
            $this->setEnvKey('dynamic_link.android_sha256', $this->android_sha256_key);
            $this->setEnvKey('dynamic_link.android_sha256_debug', $this->android_sha256_debug_key);
            $this->setEnvKey('dynamic_link.android_sha256_release', $this->android_sha256_release_key);
            $this->setEnvKey('dynamic_link.ios', $this->ios);
            $this->setEnvKey('dynamic_link.ios_id', $this->ios_id);
            $this->setEnvKey('dynamic_link.ios_app_id', $this->ios_id);
            $this->updateAssetLinksFile();
            $this->showSuccessAlert(__('Dynamic Link Settings saved successfully!'));
            $this->reset();
            $this->mount();
        } catch (Exception $error) {
            logger('error', [$error]);
            $this->showErrorAlert($error->getMessage() ?? __('Dynamic Link Settings save failed!'));
        }
    }

    public function updateAssetLinksFile()
    {

        $androidPackage = env('dynamic_link.android', 'online.edentech.fuodz');
        $sha256Fingerprint = env('dynamic_link.android_sha256', 'REPLACE_WITH_YOUR_APP_SHA256_FINGERPRINT');
        $sha256DebugFingerprint = env('dynamic_link.android_sha256_debug', 'REPLACE_WITH_YOUR_APP_SHA256_FINGERPRINT');
        $sha256ReleaseFingerprint = env('dynamic_link.android_sha256_release', 'REPLACE_WITH_YOUR_APP_SHA256_FINGERPRINT');

        $assetLinks = [
            [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => $androidPackage,
                    'sha256_cert_fingerprints' => [
                        $sha256Fingerprint,
                        $sha256DebugFingerprint,
                        $sha256ReleaseFingerprint,
                    ],
                ],
            ],
        ];

        $filePath = public_path('.well-known/assetlinks.json');

        // Ensure directory exists
        if (! file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Write the JSON file with pretty formatting
        $jsonContent = json_encode($assetLinks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (file_put_contents($filePath, $jsonContent)) {
            return true;
        }

        return false;
    }
}
