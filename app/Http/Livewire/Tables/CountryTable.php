<?php

namespace App\Http\Livewire\Tables;

use App\Models\City;
use App\Models\CityVendor;
use App\Models\Country;
use App\Models\CountryVendor;
use App\Models\State;
use App\Models\StateVendor;
use App\Services\BoundaryGeneratorService;
use App\Services\GeoBoundaryService;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Exception;
use Illuminate\Support\Facades\DB;

class CountryTable extends BaseDataTableComponent
{

    public $model = Country::class;
    public $per_page = 20;
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
        return Country::query();
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
        $country = Country::find($id);
        $keyword = $country->name;
        $boundaryGeneratorSystem = env("boundaryGeneratorSystem", "openmap");

        if ($boundaryGeneratorSystem == "openmap") {
            $this->emit("initateGeoModelBoundaryGen", Country::class, $id, $keyword);
            return;
        } else if ($boundaryGeneratorSystem == "gadm") {
            try {
                $boundaryGeneratorService = new BoundaryGeneratorService();
                $boundariesData = $boundaryGeneratorService->getBoundariesFromGADM($country);
                $country->boundaries = $boundariesData;
                $country->save();
                $this->showSuccessAlert(__('Boundaries synced successfully'));
            } catch (\Exception $e) {
                $msg = __('Failed to sync boundaries');
                $msg .= ". ";
                $msg .= $e->getMessage();
                $this->showErrorAlert($msg);
            }
        } else {
            try {
                $country = Country::find($id);
                $country->boundaries = GeoBoundaryService::getPlaceBoundaries($keyword, "country");
                $country->save();
                $this->showSuccessAlert(__('Boundaries synced successfully'));
            } catch (\Exception $e) {
                $msg = __('Failed to sync boundaries');
                $msg .= ". ";
                $msg .= $e->getMessage();
                $this->showErrorAlert($msg);
            }
        }
    }


    public function initiateDelete($id)
    {
        try {
            $this->selectedModel = $this->model::withTrashed()->find($id);
        } catch (Exception $ex) {
            $this->selectedModel = $this->model::find($id);
        }

        $this->confirm(__('Delete'), [
            'icon' => 'question',
            'toast' => false,
            'text' =>  __('Deleting this country will auto delete states & cities under it. Do you want to continue?'),
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => __("Cancel"),
            'confirmButtonText' => __("Yes"),
            'onConfirmed' => 'deleteModel',
            'onCancelled' => 'cancelled'
        ]);
    }

    //
    public function deleteModel()
    {

        //delete state - auto delete cities under it
        try {
            $this->isDemo();
            DB::beginTransaction();
            $countryId = $this->selectedModel->id;
            //delete cities and cities of operations
            $citiesIds = City::whereHas('state', function ($query) use ($countryId) {
                return $query->where("country_id", $countryId);
            })->pluck("id");
            City::whereIn('id', $citiesIds)->delete();
            CityVendor::whereIn('city_id', $citiesIds)->delete();
            //delete states and vendor state of operations
            $stateIds = State::where("country_id", $countryId)->pluck('id');
            State::whereIn("id", $stateIds)->delete();
            StateVendor::whereIn('state_id', $stateIds)->delete();
            //
            $this->selectedModel->delete();
            DB::commit();
            $this->showSuccessAlert(__("Deleted"));
        } catch (Exception $error) {
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
            'text' =>  __('De-activating this country, will auto de-activate operation for this country for any parcel/package vendor with this country as an area of operation. Do you still want to continue?'),
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
            //de-activate vendors set country of operations
            CountryVendor::where("country_id", $this->selectedModel->id)->update([
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
                $this->showErrorAlert(__('Cannot sync more than 10 items at once. Please select 10 or fewer countries.'));
                return;
            }

            if (empty($this->selected)) {
                $this->showErrorAlert(__('No countries selected'));
                return;
            }

            $boundaryGeneratorService = new BoundaryGeneratorService();
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($this->selected as $countryId) {
                try {
                    $country = Country::find($countryId);
                    if (!$country) {
                        $errorCount++;
                        continue;
                    }

                    $boundariesData = $boundaryGeneratorService->getBoundariesFromGADM($country);
                    $country->boundaries = $boundariesData;
                    $country->save();
                    $successCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    $errors[] = "Country ID {$countryId}: " . $e->getMessage();
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
                $this->showErrorAlert(__('No countries selected'));
                return;
            }

            $this->confirm(__('Delete Selected Countries'), [
                'icon' => 'question',
                'toast' => false,
                'text' => __('Deleting these countries will auto delete all states & cities under them. This action cannot be undone. Do you want to continue?'),
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

            foreach ($this->selected as $countryId) {
                try {
                    $country = Country::find($countryId);
                    if (!$country) {
                        $errorCount++;
                        continue;
                    }

                    // Delete cities and city vendors for this country
                    $citiesIds = City::whereHas('state', function ($query) use ($countryId) {
                        return $query->where("country_id", $countryId);
                    })->pluck("id");

                    City::whereIn('id', $citiesIds)->delete();
                    CityVendor::whereIn('city_id', $citiesIds)->delete();

                    // Delete states and state vendors for this country
                    $stateIds = State::where("country_id", $countryId)->pluck('id');
                    State::whereIn("id", $stateIds)->delete();
                    StateVendor::whereIn('state_id', $stateIds)->delete();

                    // Delete country vendors
                    CountryVendor::where('country_id', $countryId)->delete();

                    // Delete the country
                    $country->delete();
                    $deletedCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    logger("Error deleting country ID {$countryId}: " . $e->getMessage());
                }
            }

            DB::commit();

            // Clear selection after operation
            $this->selected = [];

            $message = __('Deletion completed') . ": {$deletedCount} " . __('countries deleted');
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
