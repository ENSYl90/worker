<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        // Skip during console commands or API requests
        if (app()->runningInConsole() || $request->is('api/*')) {
            return $next($request);
        }

        // Check DB connection
        $isDBConnected = false;
        try {
            DB::connection()->getPdo();
            $isDBConnected = true;
        } catch (\Exception $e) {
            $isDBConnected = false;
        }

        // Redirect if not installed or missing table
        if (!$isDBConnected || ($isDBConnected && !Schema::hasTable('settings'))) {
            if (!str_contains($request->path(), 'installer')) {
                // Check if named route exists
                if (app('router')->has('installer.welcome')) {
                    return redirect()->route('installer.welcome');
                }

                // Fallback in case route name not yet loaded
                return redirect(url("installer"));
            }
        }

        return $next($request);
    }
}
