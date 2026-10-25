<?php

namespace App\Http\Livewire\Header;

use App\Http\Livewire\BaseLivewireComponent;

class Profile extends BaseLivewireComponent
{

    public $locale;
    public $localeCode;
    public $selectedLanguage;
    public $languages = [];
    public $languageCodes = [];

    protected $listeners = [
    ];

    public function mount()
    {
        $this->loadLanguages();
    }

    public function render()
    {
        if (empty($this->languages)) {
            $this->loadLanguages();
        }
        return view('livewire.header.profile');
    }

    public function loadLanguages()
    {
        $languagesList = config('backend.languages', []);
        $languageCodesList = config('backend.languageCodes', []);
        $this->languages = [];
        foreach ($languagesList as $key => $language) {
            $this->languages[] = [
                "id" => $languageCodesList[$key],
                "name" => $language,
            ];
        }

        $this->locale = session('lan', config('app.locale', 'en'));
    }

    public function updatedLocale($value)
    {
        try {
            session(['lan' => $value]);
            $this->showSuccessAlert(__('Language updated successfully!'));
            return redirect(request()->header('Referer'));
        } catch (\Exception $ex) {
            $this->showErrorAlert($ex->getMessage() ?? __('Error updating language'));
        }
    }




}
