<?php

namespace App\Traits;

trait FlashMessages
{
    /**
     * Flash a success message
     *
     * @param string $message
     * @param string|null $title
     * @return void
     */
    protected function flashSuccess(string $message, ?string $title = null): void
    {
        session()->flash('success', $message);
        if ($title) {
            session()->flash('success_title', $title);
        }
    }

    /**
     * Flash an error message
     *
     * @param string $message
     * @param string|null $title
     * @return void
     */
    protected function flashError(string $message, ?string $title = null): void
    {
        session()->flash('error', $message);
        if ($title) {
            session()->flash('error_title', $title);
        }
    }

    /**
     * Flash a warning message
     *
     * @param string $message
     * @param string|null $title
     * @return void
     */
    protected function flashWarning(string $message, ?string $title = null): void
    {
        session()->flash('warning', $message);
        if ($title) {
            session()->flash('warning_title', $title);
        }
    }

    /**
     * Flash an info message
     *
     * @param string $message
     * @param string|null $title
     * @return void
     */
    protected function flashInfo(string $message, ?string $title = null): void
    {
        session()->flash('info', $message);
        if ($title) {
            session()->flash('info_title', $title);
        }
    }
}