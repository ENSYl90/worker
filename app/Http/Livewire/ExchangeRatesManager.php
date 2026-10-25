<?php

namespace App\Http\Livewire;

use App\Models\Currency;
use App\Models\CurrencyExchangeRate;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class ExchangeRatesManager extends Component
{
    use LivewireAlert;
    public $currencies;
    public $exchangeRates = [];
    public $baseCurrency = null;
    public $effectiveDate;
    public $showForm = false;
    public $editingRateId = null;
    public $exchangeRateEnabled = true;

    protected $rules = [
        'effectiveDate' => 'required|date',
        'exchangeRates.*.rate' => 'nullable|numeric|min:0',
    ];

    protected $messages = [
        'effectiveDate.required' => 'Effective date is required',
        'effectiveDate.date' => 'Please enter a valid date',
        'exchangeRates.*.rate.numeric' => 'Exchange rate must be a number',
        'exchangeRates.*.rate.min' => 'Exchange rate must be greater than 0',
    ];

    public function getMessages()
    {
        return [
            'effectiveDate.required' => __('Effective date is required'),
            'effectiveDate.date' => __('Please enter a valid date'),
            'exchangeRates.*.rate.numeric' => __('Exchange rate must be a number'),
            'exchangeRates.*.rate.min' => __('Exchange rate must be greater than 0'),
        ];
    }

    public function mount()
    {
        $this->effectiveDate = now()->format('Y-m-d');
        $this->exchangeRateEnabled = (bool) setting('enableExchangeRate', true);
        $this->loadCurrencies();
        $this->loadExchangeRates();
    }

    public function loadCurrencies()
    {
        $this->currencies = Currency::orderBy('is_active',"desc")->get();

        // Use the helper function to get base currency
        $this->baseCurrency = baseCurrency();
    }

    public function loadExchangeRates()
    {
        // Get latest rates for each currency
        $latestRates = CurrencyExchangeRate::with('currency')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('currency_exchange_rates')
                    ->whereNull('deleted_at')
                    ->groupBy('currency_id');
            })
            ->get()
            ->keyBy('currency_id');

        // Prepare rates array for display
        $this->exchangeRates = $this->currencies
            ->map(function ($currency) use ($latestRates) {
                $rate = $latestRates->get($currency->id);

                return [
                    'currency_id' => $currency->id,
                    'currency_code' => $currency->code,
                    'currency_name' => $currency->name,
                    'currency_symbol' => $currency->symbol,
                    'rate' => $rate ? $rate->rate : ($currency->id == $this->baseCurrency?->id ? '1.00000000' : ''),
                    'effective_date' => $rate ? $rate->effective_date->format('Y-m-d') : $this->effectiveDate,
                    'id' => $rate?->id,
                    'is_base' => $currency->id == $this->baseCurrency?->id,
                ];
            })
            ->toArray();
    }

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->editingRateId = null;
        }
    }

    public function editRate($index)
    {
        $this->editingRateId = $index;
        $this->showForm = true;
    }

    public function updateRate($index, $rate)
    {
        $this->validateOnly("exchangeRates.{$index}.rate");

        $currencyRate = &$this->exchangeRates[$index];
        $currencyRate['rate'] = $rate;

        // Don't allow editing base currency rate
        if ($currencyRate['is_base']) {
            $currencyRate['rate'] = '1.00000000';
            $this->alert('warning', __('Base currency rate cannot be changed from 1.0'));
            return;
        }

        $this->editingRateId = null;
    }

    public function saveRates()
    {
        $this->validate();

        try {
            $this->isDemo();
            foreach ($this->exchangeRates as $rateData) {
                if (empty($rateData['rate']) && !$rateData['is_base']) {
                    continue; // Skip empty rates for non-base currencies
                }

                // Ensure base currency always has rate of 1
                $rate = $rateData['is_base'] ? 1 : $rateData['rate'];

                CurrencyExchangeRate::updateOrCreate(
                    [
                        'currency_id' => $rateData['currency_id'],
                        'effective_date' => $this->effectiveDate,
                    ],
                    [
                        'rate' => $rate,
                    ]
                );
            }

            $this->alert('success', __('Exchange rates updated successfully!'));

            $this->loadExchangeRates();
            $this->showForm = false;
        } catch (\Exception $e) {
            $this->alert('error', __('Error updating exchange rates: :error', ['error' => $e->getMessage()]));
        }
    }

    public function deleteRate($currencyId)
    {
        $this->confirm(__('Are you sure you want to delete this exchange rate?'), [
            'onConfirmed' => 'confirmedDeleteRate',
            'data' => ['currencyId' => $currencyId]
        ]);
    }

    public function confirmedDeleteRate($data)
    {
        try {
            $rate = CurrencyExchangeRate::where('currency_id', $data['currencyId'])
                ->where('effective_date', $this->effectiveDate)
                ->first();

            if ($rate) {
                $rate->delete();
                $this->alert('success', __('Exchange rate deleted successfully!'));
                $this->loadExchangeRates();
            }
        } catch (\Exception $e) {
            $this->alert('error', __('Error deleting exchange rate: :error', ['error' => $e->getMessage()]));
        }
    }

    public function loadRatesForDate()
    {
        $this->validateOnly('effectiveDate');

        // Convert date to proper format
        $effectiveDate = Carbon::parse($this->effectiveDate)->format('Y-m-d');

        // Get rates for the selected date
        $ratesForDate = CurrencyExchangeRate::with('currency')
            ->where('effective_date', $effectiveDate)
            ->get()
            ->keyBy('currency_id');

        // Update the rates array
        foreach ($this->exchangeRates as $index => &$rateData) {
            $rate = $ratesForDate->get($rateData['currency_id']);
            $rateData['rate'] = $rate ? $rate->rate : ($rateData['is_base'] ? '1.00000000' : '');
            $rateData['id'] = $rate?->id ?? null;
        }
    }

    public function toggleExchangeRate()
    {
        try {
            $this->isDemo();

            $this->exchangeRateEnabled = !$this->exchangeRateEnabled;

            // Save to settings
            DB::table('settings')->updateOrInsert(
                ['key' => 'enableExchangeRate'],
                ['value' => $this->exchangeRateEnabled ? '1' : '0']
            );

            $status = $this->exchangeRateEnabled ? __('enabled') : __('disabled');
            $this->alert('success', __('Exchange rate feature has been :status', ['status' => $status]));
        } catch (\Exception $e) {
            $this->alert('error', __('Error toggling exchange rate: :error', ['error' => $e->getMessage()]));
        }
    }

    public function render()
    {
        return view('livewire.exchange-rates-manager');
    }


    public function isDemo()
    {
        if (!App::environment('production')) {
            throw new Exception(__("App is in demo version. Some changes can't be made"));
        };
    }
}
