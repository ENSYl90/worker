<?php

namespace App\Http\Livewire\Tables;

use App\Models\PushNotification;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PushNotificationTable extends BaseDataTableComponent
{

    public $model = PushNotification::class;

    public function query()
    {
        return PushNotification::with('user');
    }

    public function columns(): array
    {
        return [
            $this->indexColumn(),
            Column::make(__('Target'), 'role')->searchable()->sortable(),
            Column::make(__('Title'), 'title')->searchable()->sortable(),
            Column::make(__('Body'), 'body')
                ->format(function($value){
                    return str()->limit($value, 42);
                })
                ->searchable()->sortable(),
            $this->smImageColumn(),
            Column::make(__('Sender'), 'user.name')->searchable(),
            Column::make(__('At'), 'created_at')->format(function($value) {
                $date = $value->format('M d, Y');
                $time = $value->format('h:i A');
                $text = $date;
                $text .= "<br/>";
                $text .= $time;
                return $text;
            })->sortable(
                fn($query, $direction) => $query->orderBy('created_at', $direction)
            )->asHtml(),
            Column::make(__('Actions'))->format(fn($value, $column, $row) =>
                view('components.buttons.push_notification_actions', ['model' => $row])
            ),
        ];
    }
}
