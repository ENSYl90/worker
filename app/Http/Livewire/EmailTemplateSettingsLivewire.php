<?php

namespace App\Http\Livewire;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;

class EmailTemplateSettingsLivewire extends BaseLivewireComponent
{
    public $model = EmailTemplate::class;
    public $templates = [];

    public function mount()
    {
        $this->loadTemplates();
    }

    public function loadTemplates()
    {
        $this->templates = EmailTemplate::orderBy('mailable')->orderBy('key')->get()
            ->groupBy('mailable')
            ->map(fn ($rows) => $rows->toArray())
            ->toArray();
    }

    public function render()
    {
        return view('livewire.settings.email-templates');
    }

    public function resetTemplate($key)
    {
        $this->isDemo();

        try {
            DB::beginTransaction();
            $template = EmailTemplate::where('key', $key)->first();
            $template->subject = null;
            $template->body = null;
            $template->save();
            $this->loadTemplates();
            $this->showSuccessAlert(__('Reverted to default'));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorAlert($e->getMessage() ?? __('Reset failed!'));
        }
    }
}
