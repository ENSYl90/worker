<?php

namespace App\Http\Livewire\Tables;

use App\Models\PaymentMethod;
use Rappasoft\LaravelLivewireTables\Views\Column;

class WalletPaymentMethodsTable extends BaseDataTableComponent
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
            Column::make(__('Use In Wallet'), 'use_wallet')->format(function ($value, $column, $row) {
                return view('components.table.bool', $data = [
                    "model" => $row,
                    'isTrue' => $value != null,
                ]);
            }),
            Column::make(__('Actions'), 'use_wallet')->format(function ($value, $column, $row) {
                return view('components.buttons.status_actions', $data = [
                    "model" => $row,
                    "column" => $column,
                    "value" => $value,
                    'isTrue' => $value != null,
                ]);
            }),
        ];
    }

    public function activateModel()
    {

        try {
            $this->isDemo();
            $this->selectedModel->use_wallet = true;
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
            $this->selectedModel->use_wallet = false;
            $this->selectedModel->save();
            $this->showSuccessAlert(__("Deactivated"));
        } catch (Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? "Failed");
        }
    }
}
