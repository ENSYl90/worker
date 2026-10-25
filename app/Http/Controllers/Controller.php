<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $perPage = 20;
    public $productsPerPage = 20;


    // default constructor
    public function __construct()
    {
        $this->productsPerPage = (int) setting('productsPerPage', 30);
    }


    public function readalbeError($validator)
    {
        return Arr::first(Arr::flatten($validator->messages()->get('*')));
    }

    /**
     * Log an exception with a short, unique error code and return a safe
     * JSON error response. The raw exception message/trace is never sent
     * to the client - only the error code, which can be grepped in the logs.
     */
    public function errorResponse(Throwable $error, string $context = 'General error', string $safeMessage = null, int $status = 500)
    {
        $errorCode = strtoupper(Str::random(8));

        logger()->error("[{$errorCode}] {$context}: " . $error->getMessage(), [
            'error_code' => $errorCode,
            'exception' => get_class($error),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
        ]);

        return response()->json([
            "message" => ($safeMessage ?? __("Something went wrong. Please try again")) . " (" . __("Error code") . ": {$errorCode})",
        ], $status);
    }


    //
    public function isDemo()
    {
        if (!\App::environment('production')) {
            throw new \Exception(__("App is in demo version. Some changes can't be made"));
        };
    }

    public function isInDemo()
    {
        return !\App::environment('production');
    }
}