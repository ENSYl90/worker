<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $previewOverride;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $password = null, $previewOverride = null)
    {
        $this->user = $user;
        $this->password = $password;
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
            : EmailTemplate::override('new_account');
        $subject = $override?->subject ?: __("New Account");
        $bodyOverride = $override?->body ? strtr($override->body, [
            '{{user_name}}' => $this->user->name,
            '{{user_email}}' => $this->user->email,
            '{{password}}' => $this->password ?? '',
            '{{app_name}}' => env('APP_NAME', ''),
            '{{website_logo}}' => url(setting('websiteLogo', asset('images/logo.png'))),
            '{{website_color}}' => setting('websiteColor', '#246fd0'),
            '{{android_link}}' => setting('androidDownloadLink', ''),
            '{{ios_link}}' => setting('iosDownloadLink', ''),
        ]) : null;

        return $this->subject($subject)->view('view.emails.new_account', [
            'bodyOverride' => $bodyOverride,
        ]);
    }
}
