<?php

namespace App\Traits;

use App\Models\UserToken;

trait UserFirebaseTokenAttributeTrait
{

    public function getNotificationTokensAttribute()
    {
        return $this->firebaseTokens->pluck('token')->toArray();
    }

    public function firebaseTokens()
    {
        return $this->hasMany(UserToken::class);
    }


    public function syncFCMTokens($tokens = null)
    {
        //handle with device_uuid
        if ($tokens == null && request()->has("token") && request()->has("deviceId")) {
            //if no tokens in user_tokens with device_uuid, delete all
            $userTokensWithIds = UserToken::whereNotNull("device_uuid")->where("user_id", $this->id)->get();
            if ($userTokensWithIds->count() <= 0) {
                $this->firebaseTokens()->delete();
            }
            $this->firebaseTokens()->updateOrCreate(
                [
                    'user_id' => $this->id,
                    'device_uuid' => request()->deviceId,
                ],
                [
                    'token' => request()->token,
                    'device_uuid' => request()->deviceId,
                    'updated_at' => now()
                ]
            );
            return;
        }


        if ($tokens == null) {
            $tokens = request()->tokens ?? [];
        }
        //
        if ($tokens == null || empty($tokens)) {
            return;
        }

        //if tokens is not array, attach it
        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }

        //
        $userTokens = [];
        foreach ($tokens as $token) {
            $userTokens[] = new UserToken(['token' => $token]);
        }
        //if token is array, sync it
        try {
            $this->firebaseTokens()->saveMany($userTokens);
        } catch (\Exception $e) {
            logger("Error syncing tokens: ", [$e]);
        }
    }

    public function deListTokens($tokens = null)
    {
        if ($tokens == null) {
            $tokens = request()->tokens ?? [];
        }
        if ($tokens == null || empty($tokens)) {
            return;
        }
        $this->firebaseTokens()->detach($tokens);
    }
}
