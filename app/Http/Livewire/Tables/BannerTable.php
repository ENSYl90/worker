<?php

namespace App\Http\Livewire\Tables;

use App\Models\Banner;
use Rappasoft\LaravelLivewireTables\Views\Column;

class BannerTable extends OrderingBaseDataTableComponent
{

    public $model = Banner::class;

    public function query()
    {
        return Banner::with('category', 'vendor');
    }

    public function columns(): array
    {
        $columns = [
            Column::make(__('ID'), "id")->searchable()->sortable(),
            Column::make(__('Image'))->format(function ($value, $column, $row) {
                return view('components.table.image_md', $data = [
                    "model" => $row
                ]);
            }),
            Column::make(__('Title'), 'title')->searchable()->sortable(),
            Column::make(__('Active'), 'is_active')->format(function ($value, $column, $row) {
                return view('components.table.active', $data = [
                    "model" => $row
                ]);
            })->sortable(),
            Column::make(__('Created At'), 'formatted_date'),


        ];

        //
        if (app()->environment('production')) {
            $columns[] = Column::make(__('Actions'))->format(function ($value, $column, $row) {
                return view('components.buttons.banner_actions', $data = [
                    "model" => $row
                ]);
            });
        }

        return $columns;
    }
}