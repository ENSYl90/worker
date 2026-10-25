<?php

namespace App\Http\Livewire;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class BoundaryGenerator extends Component
{
    use LivewireAlert;
    protected $listeners = [
        'generateBoundaries',
        'saveGeoModel'
    ];

    public function saveGeoModel($boundaryData, $modelClass, $modelId)
    {
        try {

            $modelType = $boundaryData['modelType'];
            $name = $boundaryData['name'];
            $boundaries = $boundaryData['boundaries'];

            // Log::info("Saving boundaries for {$modelType}: {$name} (ID: {$modelId})");
            // Log::info("Original boundaries received:", ['boundaries' => $boundaries]);

            // Use the passed model class directly
            if (!class_exists($modelClass)) {
                throw new \Exception("Model class does not exist: {$modelClass}");
            }

            // Find the model by ID using the passed model class
            $model = $modelClass::find($modelId);

            if (!$model) {
                throw new \Exception("{$modelType} with ID '{$modelId}' not found in database");
            }

            // Flatten boundaries if it's a single-element MultiPolygon
            $flattenedBoundaries = $this->flattenSingleMultiPolygon($boundaries);

            // Boundaries are already in [lng, lat] format from Nominatim
            $convertedBoundaries = $flattenedBoundaries;

            // Save only the boundaries to existing column
            $model->update([
                'boundaries' => json_encode($convertedBoundaries)
            ]);

            // Log::info("Successfully saved boundaries for {$modelType}: {$name} (ID: {$modelId})");

            // Show success alert
            $this->showSuccessAlert(__("Successfully saved boundaries for :modelType: :name", [
                'modelType' => $modelType,
                'name' => $name
            ]));

            $this->emit('refreshTable');
        } catch (\Exception $e) {
            Log::error("Error saving boundaries: " . $e->getMessage(), [$e]);

            // Show error alert
            $this->showErrorAlert(__("Error saving boundaries: :error", [
                'error' => $e->getMessage()
            ]));
        }
    }

    private function getModelClass($modelType)
    {
        return match (strtolower($modelType)) {
            'country' => Country::class,
            'state' => State::class,
            'city' => City::class,
            default => null
        };
    }


    private function flattenSingleMultiPolygon($boundaries)
    {
        if (!is_array($boundaries)) {
            return $boundaries;
        }

        // Check if this is a MultiPolygon with only one polygon
        // Structure: [[[coordinates]], [], [], ...] where only first element has data
        if (count($boundaries) >= 1 && is_array($boundaries[0])) {
            // Count non-empty elements
            $nonEmptyCount = 0;
            $firstNonEmptyIndex = -1;

            for ($i = 0; $i < count($boundaries); $i++) {
                if (is_array($boundaries[$i]) && count($boundaries[$i]) > 0) {
                    $nonEmptyCount++;
                    if ($firstNonEmptyIndex === -1) {
                        $firstNonEmptyIndex = $i;
                    }
                }
            }

            // If only one non-empty element, flatten it
            if ($nonEmptyCount === 1 && $firstNonEmptyIndex !== -1) {
                return $boundaries[$firstNonEmptyIndex];
            }
        }

        return $boundaries;
    }

    //Alert
    public function showSuccessAlert($message = "", $time = 3000)
    {
        $this->alert('success', "", [
            'position'  =>  'center',
            'text' => $message,
            'toast'  =>  false,
            "timer" => $time,
            'cancelButtonText' => __('Cancel'),
        ]);
    }

    public function showWarningAlert($message = "", $time = 3000)
    {
        $this->alert('warning', "", [
            'position'  =>  'center',
            'text' => $message,
            'toast'  =>  false,
            "timer" => $time,
            'cancelButtonText' => __('Cancel'),
        ]);
    }

    public function showErrorAlert($message = "", $time = 3000)
    {
        $this->alert('error', "", [
            'position'  =>  'center',
            'text' => $message,
            'toast'  =>  false,
            "timer" => $time,
            'cancelButtonText' => __('Cancel'),
        ]);
    }

    public function render()
    {
        return view('livewire.boundary-generator');
    }
}
