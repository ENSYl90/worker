<?php

namespace App\Http\Livewire\Tables\Reports;

use App\Models\DriverSubscription;
use App\Models\DriverSubscriptionHistory;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;

class DriverSubscriptionReportTable extends BaseReportTable
{
    public $model = DriverSubscriptionHistory::class;

    public function mount()
    {
        $this->bulkActions = [];
    }

    public function query()
    {
        return $this->model::with(['driver', 'subscription'])
            ->when($this->getFilter('status'), function ($query, $status) {
                if ($status == 'active') {
                    return $query->active();
                } elseif ($status == 'expired') {
                    return $query->expired();
                }
            })
            ->when($this->getFilter('start_date'), function ($query, $sDate) {
                return $query->whereDate('starts_at', '>=', $sDate);
            })
            ->when($this->getFilter('end_date'), function ($query, $eDate) {
                return $query->whereDate('starts_at', '<=', $eDate);
            })
            ->when($this->getFilter('type'), function ($query, $type) {
                return $query->where('type', $type);
            });
    }

    public function filters(): array
    {
        $filters = array_merge(parent::filters(), [
            'type' => Filter::make(__('Type'))
                ->select([
                    '' => __('Any'),
                    DriverSubscription::TYPE_TIME => __('Time Based'),
                    DriverSubscription::TYPE_ORDERS => __('Order Based'),
                ]),
            'status' => Filter::make(__('Status'))
                ->select([
                    '' => __('Any'),
                    'active' => __('Active'),
                    'expired' => __('Expired'),
                ]),
        ]);

        //flip the order of filters
        $filters = array_reverse($filters);
        return $filters;
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), 'id')->sortable(),
            Column::make(__('Driver'), 'driver.name')
                ->format(function ($value, $column, $row) {
                    return view('components.table.user', $data = [
                        "value" => $value,
                        "model" => $row->driver,
                    ]);
                })
                ->searchable()->sortable(),
            Column::make(__('Plan'), 'subscription.name')->searchable()->sortable(),
            Column::make(__('Type'), 'type')->format(function ($value) {
                return $value === DriverSubscription::TYPE_ORDERS ? __('Order Based') : __('Time Based');
            })->sortable(),
            Column::make(__('Started'), 'starts_at')->format(function ($value) {
                return !empty($value) ? format_date($value, 'd M Y h:i A') : '';
            })->sortable(),
            Column::make(__('Expires'), 'expires_at')->format(function ($value) {
                return !empty($value) ? format_date($value, 'd M Y h:i A') : __('N/A');
            })->sortable(),
            Column::make(__('Remaining'), 'remaining_orders')->format(function ($value, $column, $row) {
                return view('components.table.driver_subscription_remaining', [
                    'model' => $row,
                ]);
            }),
            Column::make(__('Status'), 'status')->format(function ($value) {
                return __(\Str::title($value));
            })->sortable(),
        ];
    }
}
