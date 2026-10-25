<?php

namespace App\Http\Livewire;

use App\Models\DriverLocation;
use App\Models\User;
use App\Traits\DriverLocationRedisTrait;
use Exception;
use Illuminate\Support\Facades\Redis;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class DriversLocationsLivewire extends BaseLivewireComponent
{

    use DriverLocationRedisTrait;

    //
    public $model = DriverLocation::class;
    public $keyword;
    public function getListeners()
    {
        return $this->listeners + [
            'cleanDeletedDriversConfirm' => 'cleanDeletedDriversConfirm'
        ];
    }

    public function render()
    {
        return view('livewire.drivers_locations');
    }

    public function getDriversProperty()
    {
        return User::role('driver')->select('id', 'name', 'is_online')
            ->when($this->keyword, function ($query) {
                return $query->where("name", "LIKE", "%{$this->keyword}%");
            })
            ->paginate($this->perPage)->through(function ($driver) {
                $locationData = $this->getDriverLocationFromRedis($driver->id);
                if ($locationData) {
                    $driver->lat = $locationData['lat'] ?? '0';
                    $driver->lng = $locationData['lng'] ?? '0';
                    $driver->updated_at = $locationData['updated_at'] ?? now()->toDateTimeString();
                }
                return $driver;
            });
    }
    public function cleanDeletedDrivers()
    {
        if (!isQueueRedis()) {
            $totalDriverLocationsWithTrashedDrivers = DriverLocation::whereDoesntHave('driver')->count();
            if ($totalDriverLocationsWithTrashedDrivers == 0) {
                $this->showWarningAlert(
                    __('No deleted drivers found')
                );
                return;
            }
            //confirm
            $this->confirm(
                title: __('Are you sure?'),
                options: [
                    "type" => 'confirm',
                    "icon" => 'warning',
                    "text" => __('You are about to clean out driver locations of :deleted_drivers drivers. This action cannot be undone.', ['deleted_drivers' => $totalDriverLocationsWithTrashedDrivers]),
                    'confirmButtonText' => __('Yes, delete'),
                    'onConfirmed' => 'cleanDeletedDriversConfirm'
                ],
            );
        } else {
            //confirm
            $this->confirm(
                title: __('Are you sure?'),
                options: [
                    "type" => 'confirm',
                    "icon" => 'warning',
                    "text" => __('You are about to clean out all driver locations. This action cannot be undone.'),
                    'confirmButtonText' => __('Yes, delete'),
                    'onConfirmed' => 'cleanDeletedDriversConfirm'
                ],
            );
        }



    }

    public function cleanDeletedDriversConfirm()
    {
        try {
            //delete all deleted drivers
            if (!isQueueRedis()) {
                DriverLocation::whereDoesntHave('driver')->delete();
            } else {
                $this->clearDriversRedisRecord();
            }
            $this->showSuccessAlert(__('Deleted successfully'));
            $this->refreshDataTable();
        } catch (Exception $ex) {
            $this->showErrorAlert(__('Failed to delete. Please try again'));
        }
    }
}