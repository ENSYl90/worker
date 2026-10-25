<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewVendorMail extends Mailable
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
        $override = $this->previewOverride !== null
            ? (object) $this->previewOverride
            : EmailTemplate::override('new_vendor');
        $subject = $override?->subject ?: __("New Account");
        $bodyOverride = $override?->body ? strtr($override->body, [
            '{{vendor_name}}' => $this->vendor->name,
            '{{vendor_email}}' => $this->vendor->email ?? '',
            '{{app_name}}' => env('APP_NAME', ''),
            '{{login_url}}' => route('login'),
        ]) : null;

        return $this->subject($subject)->view('view.emails.new_vendor', [
            'bodyOverride' => $bodyOverride,
        ]);
    }
}
