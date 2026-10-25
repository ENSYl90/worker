<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $documentRequest;
    public $isVendor;
    public $previewOverride;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($documentRequest, $previewOverride = null)
    {
        //
        $this->documentRequest = $documentRequest;
        $this->isVendor = $documentRequest->model_type == Vendor::class;
        $this->previewOverride = $previewOverride;
        $this->locale = setting('localeCode', 'en');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->documentRequest->status == 'rejected') {
            $key = 'document_request_rejected';
        } elseif ($this->documentRequest->status == 'approved') {
            $key = 'document_request_approved';
        } else {
            $key = 'document_request_resubmit';
        }

        $override = $this->previewOverride !== null
            ? (object) $this->previewOverride
            : EmailTemplate::override($key);
        $subject = $override?->subject ?: __("Document Request");
        $bodyOverride = $override?->body ? strtr($override->body, [
            '{{user_name}}' => $this->documentRequest->model->name,
            '{{login_url}}' => route('login'),
        ]) : null;

        return $this->subject($subject)->view('view.emails.document_request', [
            'bodyOverride' => $bodyOverride,
        ]);
    }
}
