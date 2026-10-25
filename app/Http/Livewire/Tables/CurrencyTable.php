<?php

namespace App\Http\Livewire\Tables;

use App\Models\Currency;
use Rappasoft\LaravelLivewireTables\Views\Column;

class CurrencyTable extends BaseDataTableComponent
{

    public $model = Currency::class;
    public string $defaultSortColumn = 'is_active';
    public string $defaultSortDirection = 'desc';
    public bool $paginationEnabled = false;

    public function query()
    {
        return Currency::query();
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), "id"),
            Column::make(__('Code'), 'code')->searchable()->sortable(),
            Column::make(__('Name'), 'name')->searchable(),
            Column::make(__('Code'), 'code')->searchable(),
            Column::make(__('Country Code'), 'country_code')->searchable(),
            Column::make(__('Symbol'), 'symbol')->searchable(),
            $this->actionsColumn('components.buttons.currency_actions'),
        ];
    }

    public function activateModel()
    {

        try {

            $this->isDemo();
            Currency::where('is_active', true)
                ->update(['is_active' => false]);
            $this->selectedModel->is_active = true;
            $this->selectedModel->save();
            setting([
                'currencyCode' =>  $this->selectedModel->code,
                'currency' =>  $this->selectedModel->symbol,
                'currencyCountryCode' =>  $this->selectedModel->country_code ?? 'GH',
            ])->save();
            $this->showSuccessAlert(__("Activated"));
        } catch (\Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Failed"));
        }
    }
}
