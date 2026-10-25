<?php

namespace App\Observers;

use App\Models\User;
use App\Mail\NewAccountMail;
use App\Services\JobHandlerService;
use App\Services\MailHandlerService;
use App\Traits\FirebaseAuthTrait;
use Illuminate\Support\Facades\Schema;

class UserObserver
{

    use FirebaseAuthTrait;

    public function creating(User $user)
    {
        //
        $user->code = \Str::random(3) . "" . $user->id . "" . \Str::random(2);
        //add generating uuid for the user
        if (Schema::hasColumn('users', 'uuid')) {
            $user->uuid = \Str::uuid()->toString();
        }
    }

    public function created(User $user)
    {
        //update wallet
        $user->createWallet();

        //send mail
        try {
            // \Mail::to($user->email)->send(new NewAccountMail($user));
            MailHandlerService::sendMail(new NewAccountMail($user), $user->email);
        } catch (\Exception $ex) {
            // logger("Mail Error", [$ex]);
            logger("Mail Error: please check your mail server settings");
        }

        //set vehicle type id, if any to firebase
        $this->updateDriverVehicleType($user);
        //enforce user with client role, this will be overrite later if role is assigned again
        if (empty($user->roles())) {
            $user->syncRoles("client");
        }
    }

    public function updating(User $user)
    {
        $isAdmin = false;
        if (\Auth::user() != null) {
            $isAdmin = \Auth::user()->hasAnyRole('admin') ?? false;
        }
        $dirty = $user->getDirty();
        $countryCodeUnchanged = isset($dirty['country_code']) && $dirty['country_code'] == $user->getOriginal('country_code');
        if ($countryCodeUnchanged) {
            unset($dirty['country_code']);
        }

        $onlyOnlineStatusChanged = array_keys($dirty) === ['is_online'];
        if ($onlyOnlineStatusChanged && $user->hasRole('driver')) {
            return;
        }

        $profileUpdateDisabled = (bool) setting('enableProfileUpdate', 1) == false;
        if ($profileUpdateDisabled && !$isAdmin) {
            $preventColumns = ["name", "email", "phone", "country_code"];
            foreach ($dirty as $key => $value) {
                if (in_array($key, $preventColumns)) {
                    throw new \Exception(__("Profile update is disabled") . ":: $key");
                }
            }
        }
    }

    public function updated(User $user)
    {
        //set vehicle type id, if any to firebase
        $this->updateDriverVehicleType($user);
    }

    public function deleting(User $model)
    {
    }



    // UPDATE DRIVER DATA TO FIRESTORE
    public function updateDriverVehicleType(User $user)
    {

        //driver user
        if (!$user->hasRole('driver')) {
            return;
        }

        //
        (new JobHandlerService())->driverVehicleTypeJob($user);
    }
}
