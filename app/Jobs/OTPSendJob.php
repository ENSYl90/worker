<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\OTPService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OTPSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone;
    public $message;
    public $user;
    public $code;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($phone, $message, $user = null, $code = null)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $otpService = new OTPService();
            $otpService->sendOTP($this->phone, $this->message, null, $this->user, $this->code);
        } catch (\Exception $ex) {
            \Log::error("OTPSendJob Error: " . $ex->getMessage());
            // Re-throw to allow for job retries if configured
            throw $ex;
        }
    }
}
