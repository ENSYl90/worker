@props(['step'])

<div x-show="step === {{ $step }}">
    {{ $slot }}
</div>
