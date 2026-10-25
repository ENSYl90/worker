<?php

namespace App\Services;

use anlutro\LaravelSettings\Facades\Setting;
use App\Models\User;

class DriverSubscriptionService
{
    public function enabled(): bool
    {
        return (bool) setting('finance.enableDriverSubscription', false);
    }

    public function driverHasActiveSubscription(?User $driver): bool
    {
        return !empty($driver) && $driver->hasActiveDriverSubscription();
    }

    public function canReceiveAssignments(?User $driver): bool
    {
        return !$this->enabled() || $this->driverHasActiveSubscription($driver);
    }

    public function canGoOnline(?User $driver): bool
    {
        return $this->canReceiveAssignments($driver);
    }

    public function ensureCanReceiveAssignments(?User $driver, ?string $message = null): void
    {
        if (!$this->canReceiveAssignments($driver)) {
            throw new \Exception($message ?? __("Driver needs an active subscription to receive order assignments"), 1);
        }
    }

    public function ensureCanGoOnline(?User $driver, ?string $message = null): void
    {
        if (!$this->canGoOnline($driver)) {
            throw new \Exception($message ?? __("You need an active driver subscription before you can go online"), 1);
        }
    }

    public function applyAssignmentEligibility($query)
    {
        return $query->when($this->enabled(), function ($query) {
            return $query->whereHas('active_driver_subscription');
        });
    }


    //st false when null
    public function setupSetting(): void
    {
        if (setting('finance.enableDriverSubscription', null) === null) {
            setting(['finance.enableDriverSubscription' => false])->save();
        }
    }
}
