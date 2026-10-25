<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReferralRewardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $referringUser;
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $referringUser, User $user)
    {
        $this->referringUser = $referringUser;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $enableOnRegistrationReferReward = (bool) setting('enableOnRegistrationReferReward', 0);
        $referRewardAmount = (float) setting('referRewardAmount', "0");

        // Set creator id
        $this->user->creator_id = $this->referringUser->id;
        $this->user->saveQuietly();

        // Create the referral record
        $referral = new Referral();
        $referral->user_id = $this->referringUser->id;
        $referral->referred_user_id = $this->user->id;
        $referral->amount = $referRewardAmount;

        // Topup the referring user wallet and confirm the referral if the setting is enabled
        if ($enableOnRegistrationReferReward) {
            try {
                $this->referringUser->topupWallet($referRewardAmount);
                $referral->confirmed = true;
            } catch (\Exception $ex) {
                \Log::error("ReferralRewardJob Error: " . $ex->getMessage());
            }
        }
        $referral->save();
    }
}
