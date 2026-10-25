<?php

namespace App\Http\Livewire\Tables;

use App\Models\Property;
use App\Models\PropertyType;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Illuminate\Support\Facades\DB;
use Exception;

class PropertyTable extends BaseDataTableComponent
{
    public $model = Property::class;
    public bool $bulkActionsEnabled = true;
    public bool $hideBulkActionsOnEmpty = true;
    public array $bulkActions = [];
    public bool $columnSelect = true;
    public array $columnSelectEnabled = [];


    public function mount()
    {
        $isProd = env('APP_ENV') == 'production';
        $this->bulkActionsEnabled = $isProd;
        $this->bulkActions = [
            'massActiveSelected' => __('Activate'),
            'massDeactivateSelected' => __('Deactivate'),
            'massDeleteSelected' => __('Delete'),
        ];
    }

    public function filters(): array
    {
        // Property type filter
        $propertyTypesFilter = [
            "" => __('All'),
        ];

        PropertyType::active()->get()->map(function ($propertyType) use (&$propertyTypesFilter) {
            $propertyTypesFilter[$propertyType->id] = $propertyType->name;
        });


        return [
            'property_type_id' => Filter::make(__('Property Type'))
                ->select($propertyTypesFilter),
        ];
    }

    public function query()
    {
        return Property::with(['vendor', 'propertyType'])
            ->withCount('bookingOrders')
            ->when($this->filters['property_type_id'], function ($query) {
                $query->where('property_type_id', $this->filters['property_type_id']);
            })
            ->when(amI('manager'), function ($query) {
                $query->where('vendor_id', auth()->id());
            })
            ->when(!amI('admin') && !amI('manager'), function ($query) {
                $query->whereHas('vendor', function ($query) {
                    $query->where('creator_id', auth()->id());
                });
            });
    }

    public function columns(): array
    {
        $columns = [
            $this->indexColumn(),
            Column::make(__('Property'), 'name')
                ->format(function ($value, $column, $row) {
                    return view('components.table.property', ['model' => $row]);
                })
                ->asHtml()
                ->searchable()
                ->sortable(),
            Column::make(__('Price'), 'base_price')
                ->format(function ($value, $column, $row) {
                    $currency = setting('currency', '$');
                    $base = number_format($value, 2);
                    $price = "<div class='flex flex-col'>";
                    if ($row->discount_price > 0) {
                        $price .= "<span class='text-xxs text-gray-400 line-through'>$currency$base</span>";
                        $price .= "<span class='text-sm font-bold text-green-600'>$currency".number_format($row->discount_price, 2)."</span>";
                    } else {
                        $price .= "<span class='text-sm font-bold text-gray-900'>$currency$base</span>";
                    }
                    $price .= "</div>";
                    return $price;
                })
                ->asHtml()
                ->sortable(),

        ];

        if (!amI("manager")) {
            $columns[] = Column::make(__('Owner'), 'vendor.name')->sortable(function ($query, $direction) {
                return $query->join('vendors', 'vendors.id', '=', 'properties.vendor_id')
                    ->orderBy('vendors.name', $direction);
            });
        }

        $columns[] = Column::make(__('Specs'))
            ->format(function ($value, $column, $row) {
                return view('components.table.property_specs', ['model' => $row]);
            })
            ->asHtml();

        $columns[] = Column::make(__('Flags'))
            ->format(function ($value, $column, $row) {
                return view('components.table.property_flags', ['model' => $row]);
            })
            ->asHtml();

        $columns[] = $this->customActionsColumn();

        return $columns;
    }

    // Bulk Actions
    public function massActiveSelected()
    {
        if ($this->selectedRowsQuery->count() > 0) {
            try {
                $this->isDemo();
                $totalItems = $this->selectedRowsQuery->count();
                DB::beginTransaction();
                $this->selectedRowsQuery->update(['is_active' => true]);
                DB::commit();
                $this->showSuccessAlert($totalItems . ' ' . __('Properties') . ' ' . __('Activated'));
                $this->resetBulk();
            } catch (Exception $error) {
                DB::rollback();
                $this->showErrorAlert($error->getMessage());
            }
        } else {
            $this->showErrorAlert(__('No data selected'));
        }
    }

    public function massDeactivateSelected()
    {
        if ($this->selectedRowsQuery->count() > 0) {
            try {
                $this->isDemo();
                $totalItems = $this->selectedRowsQuery->count();
                DB::beginTransaction();
                $this->selectedRowsQuery->update(['is_active' => false]);
                DB::commit();
                $this->showSuccessAlert($totalItems . ' ' . __('Properties') . ' ' . __('Deactivated'));
                $this->resetBulk();
            } catch (Exception $error) {
                DB::rollback();
                $this->showErrorAlert($error->getMessage());
            }
        } else {
            $this->showErrorAlert(__('No data selected'));
        }
    }

    public function massDeleteSelected()
    {
        if ($this->selectedRowsQuery->count() > 0) {
            try {
                $this->isDemo();
                $totalItems = $this->selectedRowsQuery->count();
                DB::beginTransaction();
                $models = $this->selectedRowsQuery->get();
                foreach ($models as $model) {
                    try {
                        // Check for active bookings
                        $activeBookings = \App\Models\BookingOrder::where('property_id', $model->id)
                            ->whereHas('order', function ($q) {
                                $q->whereNotIn('status', ['cancelled', 'failed', 'delivered']);
                            })
                            ->count();

                        if ($activeBookings === 0) {
                            $model->delete();
                        }
                    } catch (Exception $error) {
                        logger('Error deleting Property', ['error' => $error]);
                    }
                }
                DB::commit();
                $this->showSuccessAlert($totalItems . ' ' . __('Properties') . ' ' . __('Deleted'));
                $this->resetBulk();
            } catch (Exception $error) {
                DB::rollback();
                $this->showErrorAlert($error->getMessage());
            }
        } else {
            $this->showErrorAlert(__('No data selected'));
        }
    }
}
