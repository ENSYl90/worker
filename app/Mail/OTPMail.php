<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;
    public $previewOverride;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp, $user, $previewOverride = null)
    {
        $this->user = $user;
        $this->otp = $otp;
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
        $this->user->refresh();

        $override = $this->previewOverride !== null
            ? (object) $this->previewOverride
            : EmailTemplate::override('otp');
        $subject = $override?->subject ?: __("OTP Verification");
        $bodyOverride = $override?->body ? strtr($override->body, [
            '{{user_name}}' => $this->user->name,
            '{{otp}}' => $this->otp,
            '{{website_logo}}' => url(setting('websiteLogo', asset('images/logo.png'))),
            '{{website_color}}' => setting('websiteColor', '#246fd0'),
            '{{android_link}}' => setting('androidDownloadLink', ''),
            '{{ios_link}}' => setting('iosDownloadLink', ''),
        ]) : null;

        return $this->subject($subject)->view('view.emails.otp', [
            'bodyOverride' => $bodyOverride,
        ]);
    }
}