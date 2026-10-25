<?php

namespace App\Http\Livewire\Tables;

use App\Models\City;
use App\Models\CityVendor;
use App\Services\BoundaryGeneratorService;
use App\Services\GeoBoundaryService;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Exception;
use Illuminate\Support\Facades\DB;

class CityTable extends BaseDataTableComponent
{

    public $model = City::class;
    public $per_page = 100;
    public string $defaultSortColumn = 'is_active';
    public string $defaultSortDirection = 'desc';
    public array $bulkActions = [];

    public function getListeners()
    {
        return $this->listeners + [
            "confirmDeleteSelected" => "confirmDeleteSelected",
        ];
    }

    public function mount()
    {
        $this->bulkActions = [
            'deleteSelected' => __('Delete'),
            'massBoundarySyncSelected' => __('Sync Boundaries'),
        ];
    }

    public function query()
    {
        return City::with('state.country');
    }

    public function setTableRowClass($row): ?string
    {
        return $row->is_active ? null : 'inactive-item';
    }

    public function columns(): array
    {
        return [
            $this->indexColumn(),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
            Column::make(__('State'), "state.name")->searchable()->sortable(),
            Column::make(__('Country'), "state.country.name")->searchable()->sortable(),
            //has boundaries column
            Column::make(__('Has Boundaries'), 'boundaries')->format(function ($value, $column, $row) {
                return view('components.table.bool', $data = [
                    "model" => $row,
                    'isTrue' => $value != null,
                ]);
            })->addClass('w-48')->sortable(),
            $this->actionsColumn('components.buttons.geodata_actions')->addClass('w-4/12'),

        ];
    }

    public function syncModelBoundaries($id)
    {

        $city = City::find($id);
        $keyword = $city->name . ', ' . $city->state->name . ', ' . $city->state->country->name;
        $boundaryGeneratorSystem = env("boundaryGeneratorSystem", "openmap");
        if ($boundaryGeneratorSystem == "openmap") {
            $this->emit("initateGeoModelBoundaryGen", City::class, $id, $keyword);
            return;
        } else if ($boundaryGeneratorSystem == "gadm") {
            try {
                $boundaryGeneratorService = new BoundaryGeneratorService();
                $boundariesData = $boundaryGeneratorService->getBoundariesFromGADM($city);
                $city->boundaries = $boundariesData;
                $city->save();
                $this->showSuccessAlert(__('Boundaries synced successfully'));
            } catch (\Exception $e) {
                $msg = __('Failed to sync boundaries');
                $msg .= ". ";
                $msg .= $e->getMessage();
                $this->showErrorAlert($msg);
            }
        } else {
            try {
                $city = City::find($id);
                $city->boundaries = GeoBoundaryService::getPlaceBoundaries($keyword, "city");
                $city->save();
                $this->showSuccessAlert(__('Boundaries synced successfully'));
            } catch (\Exception $e) {
                $msg = __('Failed to sync boundaries');
                $msg .= ". ";
                $msg .= $e->getMessage();
                $this->showErrorAlert($msg);
            }
        }
    }


    //
    public function deleteModel()
    {

        //delete state - auto delete cities under it
        try {
            $this->isDemo();
            DB::beginTransaction();
            CityVendor::where("city_id", $this->selectedModel->id)->delete();
            $this->selectedModel->delete();
            DB::commit();
            $this->showSuccessAlert(__("Deleted"));
        } catch (\Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? "Failed");
        }
    }

    public function initiateDeactivate($id)
    {
        $this->selectedModel = $this->model::find($id);

        $this->confirm(__('Deactivate'), [
            'icon' => 'question',
            'toast' => false,
            'text' =>  __('De-activating this city, will auto de-activate operation for this city for any parcel/package vendor with this city as an area of operation. Do you still want to continue?'),
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => __("Cancel"),
            'confirmButtonText' => __("Yes"),
            'onConfirmed' => 'deactivateModel',
            'onCancelled' => 'cancelled'
        ]);
    }


    public function deactivateModel()
    {

        try {
            if ($this->checkDemo) {
                $this->isDemo();
            }
            DB::beginTransaction();
            //de-activate vendors set city of operations
            CityVendor::where("city_id", $this->selectedModel->id)->update([
                "is_active" => 0
            ]);
            //
            $this->selectedModel->is_active = false;
            $this->selectedModel->save();
            DB::commit();
            $this->showSuccessAlert(__("Deactivated"));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert("Failed");
        }
    }

    public function massBoundarySyncSelected()
    {
        try {
            $this->isDemo();

            // Check selection limit
            if (count($this->selected) > 10) {
                $this->showErrorAlert(__('Cannot sync more than 10 items at once. Please select 10 or fewer cities.'));
                return;
            }

            if (empty($this->selected)) {
                $this->showErrorAlert(__('No cities selected'));
                return;
            }

            $boundaryGeneratorService = new BoundaryGeneratorService();
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($this->selected as $cityId) {
                try {
                    $city = City::with('state.country')->find($cityId);
                    if (!$city) {
                        $errorCount++;
                        continue;
                    }

                    $boundariesData = $boundaryGeneratorService->getBoundariesFromGADM($city);
                    $city->boundaries = $boundariesData;
                    $city->save();
                    $successCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    $errors[] = "City ID {$cityId}: " . $e->getMessage();
                }
            }

            // Clear selection after operation
            $this->selected = [];

            $message = __('Boundary sync completed') . ": {$successCount} " . __('successful');
            if ($errorCount > 0) {
                $message .= ", {$errorCount} " . __('failed');
            }

            if ($errorCount > 0 && !empty($errors)) {
                $this->showErrorAlert($message . '. ' . __('Errors') . ': ' . implode('; ', array_slice($errors, 0, 3)));
            } else {
                $this->showSuccessAlert($message);
            }
        } catch (Exception $e) {
            $this->showErrorAlert(__('Mass boundary sync failed') . ': ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        try {
            $this->isDemo();

            if (empty($this->selected)) {
                $this->showErrorAlert(__('No cities selected'));
                return;
            }

            $this->confirm(__('Delete Selected Cities'), [
                'icon' => 'question',
                'toast' => false,
                'text' => __('This will permanently delete the selected cities and their vendor associations. This action cannot be undone. Do you want to continue?'),
                'position' => 'center',
                'showConfirmButton' => true,
                'cancelButtonText' => __("Cancel"),
                'confirmButtonText' => __("Yes, Delete All"),
                'onConfirmed' => 'confirmDeleteSelected',
                'onCancelled' => 'cancelled'
            ]);
        } catch (Exception $e) {
            $this->showErrorAlert(__('Delete operation failed') . ': ' . $e->getMessage());
        }
    }

    public function confirmDeleteSelected()
    {
        try {
            $this->isDemo();

            DB::beginTransaction();

            $deletedCount = 0;
            $errorCount = 0;

            foreach ($this->selected as $cityId) {
                try {
                    $city = City::find($cityId);
                    if (!$city) {
                        $errorCount++;
                        continue;
                    }

                    // Delete city vendors
                    CityVendor::where("city_id", $cityId)->delete();

                    // Delete the city
                    $city->delete();
                    $deletedCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    logger("Error deleting city ID {$cityId}: " . $e->getMessage());
                }
            }

            DB::commit();

            // Clear selection after operation
            $this->selected = [];

            $message = __('Deletion completed') . ": {$deletedCount} " . __('cities deleted');
            if ($errorCount > 0) {
                $message .= ", {$errorCount} " . __('failed');
            }

            if ($errorCount > 0) {
                $this->showErrorAlert($message);
            } else {
                $this->showSuccessAlert($message);
            }
        } catch (Exception $e) {
            DB::rollback();
            $this->showErrorAlert(__('Mass delete failed') . ': ' . $e->getMessage());
        }
    }
}
