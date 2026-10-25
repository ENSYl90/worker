<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAppVersion
{
    protected $excludedRoutes = [
        'upgrade',
        'upgrade.*',
        'login',
        'logout',
        'register',
        'register.*',
        "welcome",
        // Add any other routes that should be excluded from the version check
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip version check for Livewire requests
        if ($this->isLivewireRequest($request)) {
            return $next($request);
        }

        // Only check for authenticated users
        if (Auth::check()) {
            $currentVersionCode = (int) setting('appVerisonCode', 1);
            $latestVersionCode = $this->getLatestUpgradeVersion();

            // If current version is less than the latest upgrade version
            if ($currentVersionCode < $latestVersionCode) {
                // Check if not already on an excluded route
                foreach ($this->excludedRoutes as $route) {
                    if ($request->routeIs($route)) {
                        return $next($request);
                    }
                }

                // Add flash message before redirecting
                return redirect()
                    ->route('upgrade')
                    ->with(
                        'upgrade_message',
                        __(
                            "Your application needs to be upgraded from version code :oldVersion to :newVersion",
                            [
                                "oldVersion" => $currentVersionCode,
                                "newVersion" => $latestVersionCode,
                            ]
                        ),
                    );
            }
        }

        return $next($request);
    }

    /**
     * Check if the request is a Livewire request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function isLivewireRequest(Request $request)
    {
        // Check for Livewire request headers or URL patterns
        return $request->hasHeader('X-Livewire') ||
            $request->hasHeader('X-Livewire-Preserve-Scroll') ||
            $request->has('fingerprint') && $request->has('serverMemo') ||
            strpos($request->path(), 'livewire') !== false;
    }

    /**
     * Get the latest upgrade version by scanning the Upgrades directory
     *
     * @return int
     */
    private function getLatestUpgradeVersion()
    {
        $latestVersion = 0;
        $upgradeDir = app_path('Upgrades');

        if (file_exists($upgradeDir)) {
            $files = scandir($upgradeDir);

            foreach ($files as $file) {
                if (preg_match('/Upgrade(\d+)\.php$/', $file, $matches)) {
                    $version = (int) $matches[1];
                    $latestVersion = max($latestVersion, $version);
                }
            }
        }

        return $latestVersion;
    }
}
