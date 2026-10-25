<?php

namespace App\Http\Livewire;

use App\Models\CancellationPolicy;
use Exception;
use Illuminate\Support\Facades\DB;

class CancellationPolicyLivewire extends BaseLivewireComponent
{
    public $model = CancellationPolicy::class;

    // Form fields
    public $name;
    public $description;
    public $refund_percentage_rules = [];
    public $is_active = true;

    // For adding rules
    public $rule_days_before;
    public $rule_refund_percentage;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'refund_percentage_rules' => 'nullable|array',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->refund_percentage_rules = [];
    }

    public function render()
    {
        return view('livewire.cancellation-policies');
    }

    public function addRule()
    {
        $this->validate([
            'rule_days_before' => 'required|numeric|min:0',
            'rule_refund_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $this->refund_percentage_rules[] = [
            'days_before' => (int)$this->rule_days_before,
            'refund_percentage' => (float)$this->rule_refund_percentage,
        ];

        // Sort by days_before descending
        usort($this->refund_percentage_rules, function ($a, $b) {
            return $b['days_before'] - $a['days_before'];
        });

        $this->rule_days_before = null;
        $this->rule_refund_percentage = null;
    }

    public function removeRule($index)
    {
        unset($this->refund_percentage_rules[$index]);
        $this->refund_percentage_rules = array_values($this->refund_percentage_rules);
    }

    public function save()
    {
        // Validate
        $this->validate();

        try {
            DB::beginTransaction();
            $model = new CancellationPolicy();
            if (!$this->showCreate) {
                $model = $this->selectedModel;
            }
            $model->name = $this->name;
            $model->description = $this->description;
            $rules = [];
            if ($this->refund_percentage_rules) {
                if (!is_array($this->refund_percentage_rules)) {
                    $rules = json_encode($this->refund_percentage_rules);
                } else {
                    $rules = $this->refund_percentage_rules ?? [];
                }
            }

            $model->refund_percentage_rules = $rules;
            $model->is_active = $this->is_active;
            $model->save();

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Cancellation Policy saved successfully!"));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Cancellation Policy save failed!"));
        }
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        $this->description = $this->selectedModel->description;

        $rules = [];
        if ($this->selectedModel->refund_percentage_rules) {
            if (!is_array($this->selectedModel->refund_percentage_rules)) {
                $rules = json_decode($this->selectedModel->refund_percentage_rules, true);
            } else {
                $rules = $this->selectedModel->refund_percentage_rules ?? [];
            }
        }

        $this->refund_percentage_rules = $rules;
        $this->is_active = $this->selectedModel->is_active;
        $this->emit('showEditModal');
    }
}
