<?php

namespace App\Http\Livewire;

use App\Models\NavMenu;

class MarketPlaceLivewire extends BaseLivewireComponent
{
    public function render()
    {
        $groupedMenus = NavMenu::all()->groupBy('group');
        // customization: set "Others" for null or empty group
        $groupedMenus = $groupedMenus->mapWithKeys(function ($value, $key) {
            if ($key === null || $key === '') {
                return [__('Others') => $value];
            }

            return [$key => $value];
        });

        $groupedMenus = $groupedMenus
            ->sortBy(fn ($value, $key) => $key === null || $key === '' ? 1 : 0);

        return view('livewire.extensions.marketplace', [
            'groupedMenus' => $groupedMenus ?? [],
        ]);
    }
}
