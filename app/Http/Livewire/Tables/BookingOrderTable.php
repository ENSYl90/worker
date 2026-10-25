<?php

namespace App\Http\Livewire\Tables;

use App\Models\Order;
use App\Models\User;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Illuminate\Support\Facades\Auth;

class BookingOrderTable extends OrderTable
{
    public function query()
    {
        $user = User::find(Auth::id());

        if ($user->hasRole('admin')) {
            $query = Order::with(['booking_order.property', 'vendor', 'user']);
        } else if ($user->hasRole('manager')) {
            $query = Order::with(['booking_order.property', 'vendor', 'user'])
                ->where('vendor_id', $user->vendor_id);
        } else {
            $query = Order::with(['booking_order.property', 'vendor', 'user']);
        }

        return $query->whereHas('booking_order')
            ->when($this->getFilter('payment_method_id'), fn($query, $paymentMethodId) => $query->where('payment_method_id', $paymentMethodId))
            ->when($this->getFilter('status'), fn($query, $status) => $query->currentStatus($status))
            ->when($this->getFilter('payment_status'), fn($query, $pStatus) => $query->where('payment_status', $pStatus))
            ->when($this->getFilter('start_date'), fn($query, $sDate) => $query->whereDate('created_at', '>=', $sDate))
            ->when($this->getFilter('end_date'), fn($query, $eDate) => $query->whereDate('created_at', '<=', $eDate));
    }

    public function columns(): array
    {
        $columns = [
            $this->indexColumn(),
            Column::make(__('Code/Status'), 'code')
                ->format(function ($value, $column, $row) {
                    $text = __(\Str::ucfirst($row->code));
                    // Status
                    $status = $row->status;
                    $statusColor = setting("appColorTheme.$status" . 'Color', '#0099FF');
                    $text .= '<br/>';
                    $text .= "<span class='text-xs font-medidum' style='color:$statusColor;'>";
                    $text .= __(\Str::ucfirst($row->status));
                    $text .= '</span>';

                    return view('components.table.plain', $data = [
                        'text' => $text,
                    ]);
                })
                ->searchable()
                ->sortable(),

            Column::make(__('User'), 'user.name')
                ->format(function ($value, $column, $row) {
                    return view('components.table.user', $data = [
                        'value' => $value,
                        'model' => $row->user,
                    ]);
                })
                ->searchable(function ($query, $search) {
                    return $query->orWhereHas('user', function ($query) use ($search) {
                        return $query->where('name', 'LIKE', '%' . $search . '%');
                    });
                }),

            Column::make(__('Property'), 'booking_order.property.name')
                ->format(function ($value, $column, $row) {
                    return $value ?? '-';
                })
                ->searchable(function ($query, $search) {
                    return $query->orWhereHas('booking_order.property', function ($query) use ($search) {
                        return $query->where('name', 'LIKE', '%' . $search . '%');
                    });
                }),

            Column::make(__('Check-in'), 'booking_order.check_in_date')
                ->format(function ($value, $column, $row) {
                    if ($row->booking_order) {
                        return \Carbon\Carbon::parse($value)->format('M d, Y');
                    }
                    return '-';
                })
                ->sortable(function ($query, $direction) {
                    return $query->join('booking_orders', 'booking_orders.order_id', '=', 'orders.id')
                        ->orderBy('booking_orders.check_in_date', $direction);
                }),

            $this->totalColumn(),
            $this->dateColumn(),

            Column::make(__('Actions'))->format(function ($value, $column, $row) {
                return view('components.buttons.order_actions', $data = [
                    "model" => $row
                ]);
            })
        ];

        return $columns;
    }

    private function totalColumn()
    {
        return Column::make(__('Total'), 'total')
            ->format(function ($value, $column, $row) {
                $text = "<p>" . currencyFormat($row->total, $row->currency_symbol) . "</p>";
                $text .= "<span class='text-xs' style='color:$row->payment_status_color;'>" . __(\Str::ucfirst($row->payment_status)) . "</span>";
                return view('components.table.plain', $data = [
                    "text" => $text
                ]);
            })->searchable()->sortable();
    }

    private function dateColumn()
    {
        return Column::make(__('Created At'), 'created_at')
            ->format(function ($value, $column, $row) {
                return view('components.table.formatted_date_time', $data = [
                    "model" => $row
                ]);
            })->sortable();
    }
}
