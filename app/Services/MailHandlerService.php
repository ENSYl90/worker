<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;


class MailHandlerService
{

    public static function sendMail($mailable, $email, $ccs = [])
    {

        //disable mail unless in production
        if (env('APP_ENV') != 'production') {
            return;
        }


        if (!MailHandlerService::canSendMail()) {
            Log::info('Mail sending skipped - configuration incomplete');
            return false;
        }

        try {
            $mail = Mail::to($email);

            try {
                if (!empty($ccs)) {
                    $mail = $mail->cc($ccs);
                }
            } catch (Exception $ex) {
            }

            try {
                $adminEmails = explode(",", env('SYSTEM_EMAIL', ''));
                if (!empty($adminEmails)) {
                    $mail = $mail->bcc($adminEmails);
                }
            } catch (Exception $ex) {
                //
            }

            //
            if (!delayFCMJob()) {
                $mail->send($mailable);
            } else {
                $mail->queue($mailable);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }


    public static function canSendMail(): bool
    {
        $requiredEnvVars = [
            'MAIL_HOST',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
        ];

        foreach ($requiredEnvVars as $envVar) {
            if (empty(env($envVar))) {
                return false;
            }
        }

        return true;
    }
}