<?php

namespace App\Http\Livewire;

use App\Models\ApiKey;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use GeoSot\EnvEditor\Facades\EnvEditor;

class ApiSecuritySettingsLivewire extends BaseLivewireComponent
{
    public $enforceApiKey = false;
    public $keyCacheMinutes = 10;
    public $keyName;
    public $plainApiKey;

    protected $rules = [
        'keyName' => 'nullable|string|max:100',
        'enforceApiKey' => 'boolean',
        'keyCacheMinutes' => 'required|integer|min:1|max:1440',
    ];

    public function mount()
    {
        $this->enforceApiKey = (bool) config('api_security.enforce_api_key', false);
        $this->keyCacheMinutes = (int) config('api_security.key_cache_minutes', 10);
    }

    public function render()
    {
        return view('livewire.api-security-settings', [
            'apiKeys' => ApiKey::latest()->get(),
        ]);
    }

    public function saveSettings()
    {
        try {
            $this->isDemo();
            $this->validate([
                'enforceApiKey' => 'boolean',
                'keyCacheMinutes' => 'required|integer|min:1|max:1440',
            ]);

            $value = $this->enforceApiKey ? 'true' : 'false';

            if (EnvEditor::keyExists('API_SECURITY_ENFORCE_API_KEY')) {
                EnvEditor::editKey('API_SECURITY_ENFORCE_API_KEY', $value);
            } else {
                EnvEditor::addKey('API_SECURITY_ENFORCE_API_KEY', $value);
            }

            if (EnvEditor::keyExists('API_SECURITY_KEY_CACHE_MINUTES')) {
                EnvEditor::editKey('API_SECURITY_KEY_CACHE_MINUTES', $this->keyCacheMinutes);
            } else {
                EnvEditor::addKey('API_SECURITY_KEY_CACHE_MINUTES', $this->keyCacheMinutes);
            }

            Config::set('api_security.enforce_api_key', (bool) $this->enforceApiKey);
            Config::set('api_security.key_cache_minutes', (int) $this->keyCacheMinutes);
            Artisan::call('config:clear');

            $this->showSuccessAlert(__('API security settings saved successfully!'));
        } catch (Exception $error) {
            logger('error', [$error]);
            $this->showErrorAlert($error->getMessage() ?? __('Failed to save API security settings!'));
        }
    }

    public function generateKey()
    {
        try {
            $this->isDemo();
            $this->validateOnly('keyName');

            $plainKey = ApiKey::generatePlainKey();

            ApiKey::create([
                'user_id' => Auth::id(),
                'name' => $this->keyName,
                'key_hash' => ApiKey::hashKey($plainKey),
                'key_prefix' => ApiKey::previewPrefix($plainKey),
                'last_four' => ApiKey::previewLastFour($plainKey),
                'is_active' => true,
            ]);

            $this->plainApiKey = $plainKey;
            $this->keyName = null;
            $this->showSuccessAlert(__('API key generated successfully! Copy it now.'));
        } catch (Exception $error) {
            logger('error', [$error]);
            $this->showErrorAlert($error->getMessage() ?? __('API key generation failed!'));
        }
    }

    public function toggleKeyStatus($id)
    {
        try {
            $this->isDemo();
            $apiKey = ApiKey::findOrFail($id);
            $apiKey->is_active = !$apiKey->is_active;
            $apiKey->save();
            Cache::forget(ApiKey::cacheKeyForHash($apiKey->key_hash));

            $this->showSuccessAlert(__('API key status updated successfully!'));
        } catch (Exception $error) {
            logger('error', [$error]);
            $this->showErrorAlert($error->getMessage() ?? __('API key status update failed!'));
        }
    }

    public function deleteKey($id)
    {
        try {
            $this->isDemo();
            $apiKey = ApiKey::findOrFail($id);
            Cache::forget(ApiKey::cacheKeyForHash($apiKey->key_hash));
            $apiKey->delete();
            $this->showSuccessAlert(__('API key revoked successfully!'));
        } catch (Exception $error) {
            logger('error', [$error]);
            $this->showErrorAlert($error->getMessage() ?? __('API key revoke failed!'));
        }
    }

    public function clearPlainApiKey()
    {
        $this->plainApiKey = null;
    }

    public function getGeneratedKeyPrefixProperty()
    {
        return ApiKey::plainKeyPrefix();
    }
}
