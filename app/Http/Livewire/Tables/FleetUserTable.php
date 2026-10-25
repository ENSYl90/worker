<?php

namespace App\Http\Livewire\Tables;

use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Spatie\Permission\Models\Role;
use Exception;

class FleetUserTable extends BaseDataTableComponent
{

    public $model = User::class;
    public $role;


    public function filters(): array
    {
        $roles = Role::whereIn("name", ["driver", 'fleet-manager'])->get()->pluck('name')->toArray();
        $roleFilters = [
            "" => __('All')
        ];
        foreach ($roles as $role) {
            $roleFilters[$role] = $role;
        }
        return [
            'role' => Filter::make(__("Role"))
                ->select($roleFilters),
        ];
    }


    public function query()
    {
        //if cusrrent user has not fleet return empty
        $user = User::find(Auth::id());
        $currenctUserFleet = $user->fleets()->first();
        if ($currenctUserFleet == null) {
            return User::with('roles', 'creator')->where('id', 0);
        }
        return User::with('roles', 'creator')->whereHas('roles', function ($query) {
            $query->when($this->getFilter('role'), fn($query, $role) => $query->where('name', $role));
        })->whereHas('fleets', function ($query) use ($currenctUserFleet) {
            return $query->where('id', $currenctUserFleet->id);
        })
            ->whereNotIn('id', [Auth::id()]);
    }


    public function columns(): array
    {
        $columns = [
            Column::make(__('ID'), "id")->searchable()->sortable(),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
        ];

        if (!$this->inDemo()) {
            $columns[] = Column::make(__('Phone'), 'phone')->searchable()->sortable();
        }

        $mColumns = [
            $this->customColumn(__('Wallet'), 'components.table.wallet'),
            Column::make(__('Commission') . "(%)", 'commission'),
            Column::make(__('Role'), 'role_name'),
            Column::make(__('Created At'), 'formatted_date'),
            $this->customActionsColumn(
                $showView = true,
                $showEdit = true,
                $showDelete = true,
                $showToggleActive = true,
            ),
        ];

        $columns = array_merge($columns, $mColumns);

        return $columns;
    }

    //
    public function deleteModel()
    {

        try {

            $this->isDemo();
            \DB::beginTransaction();
            //
            $walletIds = Wallet::where('user_id', $this->selectedModel->id)->get()->pluck('id');
            DeliveryAddress::whereIn('user_id', [$this->selectedModel->id])->delete();
            Order::whereIn('user_id', [$this->selectedModel->id])->delete();
            WalletTransaction::whereIn('wallet_id', $walletIds)->delete();
            Wallet::whereIn('id', $walletIds)->delete();
            $this->selectedModel = $this->selectedModel->fresh();
            $this->selectedModel->forceDelete();
            \DB::commit();
            $this->showSuccessAlert("Deleted");
        } catch (Exception $error) {
            \DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? "Failed");
        }
    }
}
