<?php

namespace App\Http\Livewire\Tables;

use App\Models\Option;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Illuminate\Support\Facades\Auth;

class OptionTable extends OrderingBaseDataTableComponent
{

    public $model = Option::class;

    public function query()
    {
        if (Auth::user()->hasRole('admin')) {
            return Option::with('option_group', 'products');
        } else {
            return Option::with('option_group', 'products')->where('vendor_id', Auth::user()->vendor_id);
        }
    }

    public function columns(): array
    {

        $columns = [
            Column::make(__('ID'), "id"),
            $this->xsImageColumn(),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
            $this->priceColumn()->searchable()->sortable(),
            Column::make(__('Option Group'), 'option_group.name')->searchable()->sortable(),
            Column::make(__('Products'), 'option_group.name')
                ->format(function ($value, $column, $row) {
                    $products = $row->products->pluck('name');
                    $visible = $products->take(4);
                    $remaining = $products->count() - $visible->count();
                    $text = implode(', ', $visible->toArray());
                    if ($remaining > 0) {
                        $text .= " <span class=\"inline-block ml-1 px-1.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full\">+{$remaining} ".__("more")."</span>";
                    }
                    return view('components.table.plain', [
                        "text" => $text
                    ]);
                })->addClass('break-all w-40 text-xs')->searchable(
                    //where has any product with provided keyword
                    function ($query, string $searchTerm) {
                        return $query->orWhereHas('products', function ($query) use ($searchTerm) {
                            $query->where('name', 'like', '%' . $searchTerm . '%');
                        });
                    }
                ),
            $this->activeColumn(),
            Column::make(__('Created At'), 'formatted_date'),
            $this->actionsColumn(),
        ];

        //
        // if( $this->canManage ){
        //     array_push($columns, Column::make('Actions')->view('components.buttons.actions'));
        // }
        return $columns;
    }
}
