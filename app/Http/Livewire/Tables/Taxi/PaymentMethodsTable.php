<?php

namespace App\Http\Livewire\Tables\Taxi;

use App\Http\Livewire\Tables\BaseDataTableComponent;
use App\Models\PaymentMethod;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PaymentMethodsTable extends BaseDataTableComponent
{

    public $model = PaymentMethod::class;
    public function query()
    {
        return PaymentMethod::query();
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), "id")->searchable()->sortable(),
            $this->smImageColumn(),
            Column::make(__('Name'), 'name')->searchable(),
            Column::make(__('Use In Taxi'), 'use_taxi')->format(function ($value, $column, $row) {
                return view('components.table.bool', $data = [
                    "model" => $row,
                    'isTrue' => $value != null,
                ]);
            }),
            Column::make(__('Actions'), 'use_taxi')->format(function ($value, $column, $row) {
                return view('components.buttons.status_actions', $data = [
                    "model" => $row,
                    "value" => $value,
                    "column" => $column,
                    'isTrue' => $value != null,
                ]);
            }),
        ];
    }

    public function activateModel()
    {

        try {
            $this->isDemo();
            $this->selectedModel->use_taxi = true;
            $this->selectedModel->save();
            $this->showSuccessAlert(__("Activated"));
        } catch (Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? "Failed");
        }
    }


    public function deactivateModel()
    {

        try {
            $this->isDemo();
            $this->selectedModel->use_taxi = false;
            $this->selectedModel->save();
            $this->showSuccessAlert(__("Deactivated"));
        } catch (Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? "Failed");
        }
    }
}
