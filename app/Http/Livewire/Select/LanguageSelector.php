<?php

namespace App\Http\Livewire\Select;

use Livewire\Component;
use App\Models\User;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Exception;

class LanguageSelector extends Component
{
    use LivewireAlert;
    public $languages = [];
    public $lan = "en";

    public $link;
    //
    public function mount()
    {
        //
    }

    public function render()
    {
        if (empty($this->languages)) {
            $this->loadLanguages();
        }
        return view('livewire.component.language-selector');
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


        $this->lan = session('lan', config('app.locale', 'en'));

    }

    public function updatedLan($value)
    {
        $this->onLanSelected($value);
    }

    public function onLanSelected($value)
    {
        try {
            // Note: we do not block language changes in demo mode as it is a safe preference change.
            // $this->isDemo(); 

            // We rely on the UserLang middleware to update the authenticated user's language
            // based on the 'lan' query parameter in the redirect below.
            // This avoids code duplication and logic errors.

            $this->showSuccessAlert(__('Language updated successfully!'));
            session(['lan' => $value]);

            $referer = request()->header('Referer');
            if (empty($referer)) {
                return redirect('/');
            }

            return redirect($referer);
        } catch (Exception $ex) {
            logger("error", [$ex]);
            $this->showErrorAlert($ex->getMessage() ?? __('Error updating language'));
        }
    }


    //MISCS.
    public function isDemo()
    {
        if (!App::environment('production')) {
            throw new Exception(__("App is in demo version. Some changes can't be made"));
        }
        ;
    }

    //Alert
    public function showSuccessAlert($message = "", $time = 3000)
    {
        $this->alert('success', "", [
            'position' => 'center',
            'text' => $message,
            'toast' => false,
            "timer" => $time,
            'cancelButtonText' => __('Cancel'),
        ]);
    }

    public function showWarningAlert($message = "", $time = 3000)
    {
        $this->alert('warning', "", [
            'position' => 'center',
            'text' => $message,
            'toast' => false,
            "timer" => $time,
            'cancelButtonText' => __('Cancel'),
        ]);
    }

    public function showErrorAlert($message = "", $time = 3000)
    {
        $this->alert('error', "", [
            'position' => 'center',
            'text' => $message,
            'toast' => false,
            "timer" => $time,
            'cancelButtonText' => __('Cancel'),
        ]);
    }

    public function showSelect2($selectorID, $data, $onChange, $options = null)
    {
        $this->emit('showSelect2', [
            $selectorID,
            $data,
            $onChange,
            $options
        ], );
    }
}