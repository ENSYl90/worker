<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogViewContoller extends Controller
{
    //
    public function index(Request $request)
    {
        if (!app()->environment('production')) {
            $code = $request->query('code');
            if ($code && $code === env('LOG_VIEWER_CODE')) {
                return app(\Rap2hpoutre\LaravelLogViewer\LogViewerController::class)->index();
            } else {
                return abort(404); // Invalid or missing code
            }
        }

        // Non-production environments
        return app(\Rap2hpoutre\LaravelLogViewer\LogViewerController::class)->index();
    }
}
