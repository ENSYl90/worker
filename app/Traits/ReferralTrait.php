<?php

namespace App\Traits;

use Exception;
use App\Models\User;
use App\Models\Referral;
use App\Jobs\ReferralRewardJob;

trait ReferralTrait
{

    //TODO: Handle the referral process
    //
    public function handleControllerReferralRegistration($referralCode, $user)
    {
        $enableReferSystem = (bool) setting('enableReferSystem', "0");
        if ($enableReferSystem && !empty($referralCode)) {
            $referringUser = User::where('code', $referralCode)->first();
            if (!empty($referringUser)) {
                //dispatch job
                ReferralRewardJob::dispatch($referringUser, $user);
            } else {
                throw new Exception(__("Invalid referral code"), 1);
            }
        }
    }


    //
    public function handlePartnerControllerReferral($referralCode, $user)
    {
        $this->handleControllerReferralRegistration($referralCode, $user);
    }

    //
    public function handleDriverRegLivewireReferral($referralCode, $user)
    {
        $this->handleControllerReferralRegistration($referralCode, $user);
    }
}