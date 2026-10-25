<?php

namespace App\Http\Livewire;

use App\Models\NotificationSetting;
use App\Models\NotificationSoundSetting;

class NotificationSettingsLivewire extends BaseLivewireComponent
{
    public $settings = [];
    public $models = [];
    public $actions = [];

    // Sound settings
    public $soundEnabled;
    public $soundFile;
    public $soundVolume;
    public $availableSounds = [];
    public $customSoundUpload;

    public function mount()
    {
        $this->models = NotificationSetting::getAvailableModels();
        $this->actions = NotificationSetting::getAvailableActions();
        $this->availableSounds = $this->getUploadedSounds();
        $this->loadSettings();
        $this->loadSoundSettings();
    }

    public function loadSettings()
    {
        // Load all existing settings
        $existingSettings = NotificationSetting::all();

        // Create an associative array for easy access
        foreach ($this->models as $modelName => $modelClass) {
            foreach ($this->actions as $action) {
                $key = $modelName . '_' . $action;
                $setting = $existingSettings->where('model_type', $modelName)
                    ->where('action', $action)
                    ->first();

                $this->settings[$key] = $setting ? $setting->is_enabled : false;
            }
        }
    }

    public function toggleSetting($modelName, $action)
    {
        try {
            $this->isDemo();
            $key = $modelName . '_' . $action;
            $newValue = !$this->settings[$key];

            // Update or create the setting
            NotificationSetting::updateOrCreate(
                [
                    'model_type' => $modelName,
                    'action' => $action,
                ],
                [
                    'is_enabled' => $newValue,
                ]
            );

            $this->settings[$key] = $newValue;
            $this->alert('success', "", [
                'position'  =>  'top-end',
                'text' => __('Notification setting updated successfully'),
                "title" => "",
                'toast'  =>  true,
                "timer" => 3000,
                "showCloseButton" => false,
                "showDenyButton" => false,
                "showCancelButton" => false,
            ]);
        } catch (\Exception $e) {
            $this->showErrorAlert(__('Failed to update notification setting:') . " " . $e->getMessage());
        }
    }

    public function loadSoundSettings()
    {
        $soundSettings = NotificationSoundSetting::current();
        $this->soundEnabled = $soundSettings->sound_enabled;
        $this->soundFile = $soundSettings->sound_file;
        $this->soundVolume = $soundSettings->sound_volume;
    }

    public function saveSoundSettings()
    {
        try {
            $this->isDemo();
            $soundSettings = NotificationSoundSetting::current();
            $soundSettings->update([
                'sound_enabled' => $this->soundEnabled,
                'sound_file' => $this->soundFile,
                'sound_volume' => $this->soundVolume,
            ]);

            $this->alert('success', "", [
                'position'  =>  'top-end',
                'text' => __('Sound settings saved successfully'),
                "title" => "",
                'toast'  =>  true,
                "timer" => 3000,
                "showCloseButton" => false,
                "showDenyButton" => false,
                "showCancelButton" => false,
            ]);
        } catch (\Exception $e) {
            $this->showErrorAlert(__('Failed to save sound settings:') . " " . $e->getMessage());
        }
    }

    public function testSound()
    {
        // Emit event to play test sound
        $this->emit('playTestSound', [
            'file' => $this->soundFile,
            'volume' => $this->soundVolume,
        ]);
    }

    public function uploadCustomSound()
    {
        $this->validate([
            'customSoundUpload' => 'required|mimes:mp3,wav,ogg|max:1024', // max 1MB
        ]);

        try {
            $this->isDemo();
            // Store the file in public/sounds directory
            $filename = 'custom-' . time() . '.' . $this->customSoundUpload->extension();
            $this->customSoundUpload->storeAs('sounds', $filename, 'public');

            // Copy to public/sounds for direct access
            $sourcePath = storage_path('app/public/sounds/' . $filename);
            $destPath = public_path('sounds/' . $filename);

            // Create directory if it doesn't exist
            if (!file_exists(public_path('sounds'))) {
                mkdir(public_path('sounds'), 0755, true);
            }

            copy($sourcePath, $destPath);

            // Set as current sound
            $this->soundFile = $filename;

            // Save settings
            $this->saveSoundSettings();

            $this->alert('success', "", [
                'position'  =>  'top-end',
                'text' => __('Custom sound uploaded successfully'),
                "title" => "",
                'toast'  =>  true,
                "timer" => 3000,
                "showCloseButton" => false,
                "showDenyButton" => false,
                "showCancelButton" => false,
            ]);

            // Reset upload field
            $this->customSoundUpload = null;
        } catch (\Exception $e) {
            $this->showErrorAlert(__('Failed to upload sound:') . " " . $e->getMessage());
        }
    }

    public function getUploadedSounds()
    {
        $soundsPath = public_path('sounds');
        $defaultSounds = NotificationSoundSetting::getAvailableSounds();

        if (!file_exists($soundsPath)) {
            return $defaultSounds;
        }

        $files = glob($soundsPath . '/custom-*.{mp3,wav,ogg}', GLOB_BRACE);
        $customSounds = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $customSounds[$filename] = 'Custom: ' . $filename;
        }

        return array_merge($defaultSounds, $customSounds);
    }

    public function deleteCustomSound($filename)
    {
        try {
            // Only allow deleting custom sounds
            if (strpos($filename, 'custom-') !== 0) {
                throw new \Exception('Can only delete custom sounds');
            }

            $filePath = public_path('sounds/' . $filename);
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Also delete from storage
            $storagePath = storage_path('app/public/sounds/' . $filename);
            if (file_exists($storagePath)) {
                unlink($storagePath);
            }

            // If this was the selected sound, reset to default
            if ($this->soundFile === $filename) {
                $this->soundFile = 'notification-1.mp3';
                $this->saveSoundSettings();
            }

            $this->alert('success', "", [
                'position'  =>  'top-end',
                'text' => __('Sound deleted successfully'),
                "title" => "",
                'toast'  =>  true,
                "timer" => 3000,
                "showCloseButton" => false,
                "showDenyButton" => false,
                "showCancelButton" => false,
            ]);
        } catch (\Exception $e) {
            $this->showErrorAlert(__('Failed to delete sound:') . " " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.notification-settings-livewire');
    }
}
