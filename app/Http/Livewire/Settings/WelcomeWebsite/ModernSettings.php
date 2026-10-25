<?php

namespace App\Http\Livewire\Settings\WelcomeWebsite;

use App\Http\Livewire\BaseLivewireComponent;
use Exception;

class ModernSettings extends BaseLivewireComponent
{
    // App settings
    public $websiteHeaderTitle;
    public $websiteHeaderSubtitle;
    public $websitePhoneSubtitle;
    public $websiteFooterBrief;
    //social links
    public $fbLink;
    public $igLink;
    public $twLink;
    public $yuLink;
    public $liLink;
    public $enableBackendButton = true;



    public function loadContents()
    {
        //
        $this->websiteHeaderTitle = setting('website.modern.websiteHeaderTitle', '');
        $this->websiteHeaderSubtitle = setting('website.modern.websiteHeaderSubtitle', '');
        $this->websitePhoneSubtitle = setting('website.modern.websitePhoneSubtitle', '');
        $this->websiteFooterBrief = setting('website.modern.websiteFooterBrief', '');
        $this->fbLink = setting('website.modern.social.fbLink', '');
        $this->igLink = setting('website.modern.social.igLink', '');
        $this->twLink = setting('website.modern.social.twLink', '');
        $this->yuLink = setting('website.modern.social.yuLink', '');
        $this->liLink = setting('website.modern.social.liLink', '');
        $this->enableBackendButton = (bool) setting('website.modern.enable_backend_button', true);
    }


    public function render()
    {
        return view('livewire.settings.welcome-website.modern-settings');
    }




    public function saveAppSettings()
    {


        try {

            $this->isDemo();
            $websiteSettings = [
                'website.modern.websiteHeaderTitle' =>  $this->websiteHeaderTitle,
                'website.modern.websiteHeaderSubtitle' =>  $this->websiteHeaderSubtitle,
                'website.modern.websiteFooterBrief' =>  $this->websiteFooterBrief,
                'website.modern.websitePhoneSubtitle' =>  $this->websitePhoneSubtitle,
                'website.modern.enable_backend_button' =>  $this->enableBackendButton,
                "website.modern.social" => [
                    'fbLink' =>  $this->fbLink,
                    'igLink' =>  $this->igLink,
                    'twLink' =>  $this->twLink,
                    'yuLink' =>  $this->yuLink,
                    'liLink' =>  $this->liLink,
                ],
            ];


            // update the site name
            setting($websiteSettings)->save();
            $this->showSuccessAlert(__("Website Settings saved successfully!"));
        } catch (Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Website Settings save failed!"));
        }
    }
}
