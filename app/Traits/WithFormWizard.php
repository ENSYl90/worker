<?php

namespace App\Traits;

trait WithFormWizard
{
    public int $currentStep = 1;

    public function goToStep(int $step)
    {
        if ($step < 1) return;
        $this->validateCurrentStep();
        $this->currentStep = $step;
    }

    public function nextStep()
    {
        $this->validateCurrentStep();
        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep = max(1, $this->currentStep - 1);
    }

    public function validateCurrentStep()
    {
        $method = 'rulesForStep' . $this->currentStep;

        if (method_exists($this, $method)) {
            $this->validate($this->{$method}());
        }
    }

    public function isLastStep($total): bool
    {
        return $this->currentStep === $total;
    }

    public function resetWizard()
    {
        $this->currentStep = 1;
    }
}
