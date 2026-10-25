<?php

namespace App\Http\Livewire;

use App\Models\EmailTemplate;
use App\Services\EmailTemplatePreviewService;
use Illuminate\Support\Facades\DB;

class EmailTemplateEditLivewire extends BaseLivewireComponent
{
    public $model = EmailTemplate::class;

    public $editingKey;
    public $editingLabel;
    public $editingPlaceholders = [];
    public $subject;
    public $body;
    public $notFound = false;
    public $previewHtml = '';

    protected $rules = [
        'subject' => 'nullable|string|max:255',
        'body' => 'nullable|string',
    ];

    public function mount($key)
    {
        $template = EmailTemplate::where('key', $key)->first();
        if (!$template) {
            $this->notFound = true;
            return;
        }

        $this->editingKey = $template->key;
        $this->editingLabel = $template->label;
        $this->editingPlaceholders = EmailTemplate::placeholdersFor($template->key);
        $this->subject = $template->subject;
        $this->body = $template->body;

        $this->refreshPreview();
    }

    public function render()
    {
        return view('livewire.settings.email-template-edit');
    }

    public function refreshPreview()
    {
        if ($this->notFound) {
            return;
        }

        try {
            $mail = EmailTemplatePreviewService::make($this->editingKey, [
                'subject' => $this->subject,
                'body' => $this->body,
            ]);
            $this->previewHtml = $mail->render();
        } catch (\Exception $e) {
            $this->previewHtml = '<p style="font-family:sans-serif;padding:1rem;color:#b91c1c;">' . e(__('Preview unavailable')) . '</p>';
        }
    }

    public function save($forceSave = false)
    {
        $this->isDemo();
        $this->validate();

        if (
            !$forceSave
            && $this->editingKey === 'otp'
            && !str_contains($this->body ?? '', '{{otp}}')
        ) {
            $this->emit(
                'confirmMissingOtpPlaceholder',
                __('This email body does not include the {{otp}} placeholder — users will not receive their verification code. Save anyway?')
            );
            return;
        }

        try {
            DB::beginTransaction();
            $template = EmailTemplate::where('key', $this->editingKey)->first();
            $template->subject = $this->subject;
            $template->body = $this->body;
            $template->save();
            $this->refreshPreview();
            $this->showSuccessAlert(__('Updated successfully'));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorAlert($e->getMessage() ?? __('Update failed!'));
        }
    }

    public function resetTemplate()
    {
        $this->isDemo();

        try {
            DB::beginTransaction();
            $template = EmailTemplate::where('key', $this->editingKey)->first();
            $template->subject = null;
            $template->body = null;
            $template->save();
            $this->subject = null;
            $this->body = null;
            $this->emit('loadSummerNote', 'emailTemplateBody', '');
            $this->refreshPreview();
            $this->showSuccessAlert(__('Reverted to default'));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorAlert($e->getMessage() ?? __('Reset failed!'));
        }
    }
}
