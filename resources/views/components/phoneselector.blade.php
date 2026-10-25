<div>
    <div wire:ignore>
        @php
            if (empty($inputId)) {
                $inputId = $model ?? 'phone';
                $inputId .= rand(1000, 99999);
            }
            $modelId = $model ?? 'phone';
            $defaultCountry = setting('countryCode', 'GH');
            //explode default country code and select the last part
            $defaultCountry = explode(',', $defaultCountry);
            $defaultCountry = end($defaultCountry) ?? 'US';
            //
            if (strlen($defaultCountry) > 2) {
                $defaultCountry = 'US';
            }
            $phoneInitData = [$inputId, $modelId, $value ?? '', $defaultCountry];
        @endphp
        <div class="phoneInitDiv" data-value="{{ json_encode($phoneInitData) }}">
            <x-input id="{{ $inputId }}" name="{{ $modelId }}" title="{{ $title ?? __('Phone') }}" />
            <input wire:model="{{ $modelId }}" type="hidden" id="{{ $modelId }}" name="{{ $modelId }}"
                value="{{ $value ?? '' }}" />
        </div>
    </div>
    <x-input-error message="{{ $errors->first($modelId) }}" />
    @include('layouts.partials.phoneselector')
</div>
