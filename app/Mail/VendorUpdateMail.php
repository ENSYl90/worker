<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vendor;
    public $previewOverride;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vendor, $previewOverride = null)
    {
        //
        $this->vendor = $vendor;
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
        $key = $this->vendor->is_active ? 'vendor_update_active' : 'vendor_update_deactivated';
        $override = $this->previewOverride !== null
            ? (object) $this->previewOverride
            : EmailTemplate::override($key);
        $subject = $override?->subject ?: __("Account Updated");
        $bodyOverride = $override?->body ? strtr($override->body, [
            '{{vendor_name}}' => $this->vendor->name,
            '{{app_name}}' => env('APP_NAME', ''),
            '{{login_url}}' => route('login'),
            '{{contact_url}}' => route('contact'),
        ]) : null;

        return $this->subject($subject)->view('view.emails.vendor_update', [
            'bodyOverride' => $bodyOverride,
        ]);
    }
}
