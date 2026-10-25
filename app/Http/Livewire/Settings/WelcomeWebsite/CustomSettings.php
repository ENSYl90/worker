<?php

namespace App\Http\Livewire\Settings\WelcomeWebsite;

use App\Http\Livewire\BaseLivewireComponent;
use Exception;

class CustomSettings extends BaseLivewireComponent
{
    // App settings
    public $websiteCustomCode;
    public function loadContents()
    {
        //
        $filePath = base_path("resources/views/livewire/website/custom-welcome.blade.php");
        $this->websiteCustomCode = file_get_contents($filePath);
    }


    public function render()
    {
        return view('livewire.settings.welcome-website.custom-settings');
    }




    public function saveAppSettings()
    {

        try {
            $this->isDemo();
            $filePath = base_path("resources/views/livewire/website/custom-welcome.blade.php");
            file_put_contents($filePath, $this->websiteCustomCode);
            $this->showSuccessAlert(__("Website Settings saved successfully!"));
        } catch (Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Website Settings save failed!"));
        }
    }
}
