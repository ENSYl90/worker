<?php

namespace App\Http\Livewire\Website;


class WelcomeLivewire extends WebsiteBaseLivewireComponent
{

    public function render()
    {
        $customWebsite = setting("welcomeWebsiteType", "modern") == "custom";
        if (!$customWebsite) {
            $oldWelcomeWebsite = setting("welcomeWebsiteType", "modern") == "base";
            if ($oldWelcomeWebsite) {
                return view('livewire.website.base-welcome')->layout('layouts.website');
            } else if (setting("welcomeWebsiteType", "modern") == "clean") {
                return view('livewire.website.clean-welcome')->layout('layouts.website');
            } else {
                return view('livewire.website.modern-welcome')->layout('layouts.website');
            }
        } else {
            return view('livewire.website.custom-welcome')->layout('layouts.website');
        }
    }
}
