<?php

namespace App\Http\Livewire;

class WebsiteSettingsLivewire extends BaseLivewireComponent
{

    public $website_style = "base";

    public function mount()
    {
        $this->website_style = setting("welcomeWebsiteType", "modern");
    }

    public function render()
    {
        return view('livewire.settings.website-settings');
    }


    public function saveSettings()
    {
        try {
            $this->isDemo();
            setting([
                "welcomeWebsiteType" => $this->website_style,
            ])->save();
            $this->showSuccessAlert(__("Website Settings saved successfully!"));
        } catch (\Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Website Settings save failed!"));
        }
    }
}
