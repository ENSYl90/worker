<?php

use Illuminate\Http\Request;

if (!function_exists('logLoginActivity')) {
    /**
     * Log successful login activity with device info and IP address
     *
     * @param \App\Models\User $user
     * @param \Illuminate\Http\Request $request
     * @param string $loginType (web, api, social, qrcode)
     * @return void
     */
    function logLoginActivity($user, Request $request, string $loginType = 'api')
    {
        // Get device information
        $userAgent = $request->userAgent();
        $deviceInfo = [
            'user_agent' => $userAgent,
            'platform' => getPlatform($userAgent),
            'browser' => getBrowser($userAgent),
            'device_type' => getDeviceType($userAgent),
        ];

        // Get IP address
        $ipAddress = $request->ip();

        // Prepare activity properties
        $properties = [
            'status' => 'success',
            'email' => $user->email,
            'ip_address' => $ipAddress,
            'device_info' => $deviceInfo,
            'login_type' => $loginType,
            'timestamp' => now()->toDateTimeString(),
        ];

        // Log the activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties($properties)
            ->log('login_success');
    }
}

if (!function_exists('logFailedLoginActivity')) {
    /**
     * Log failed login attempt with email, IP address and device info
     *
     * @param string $email
     * @param \Illuminate\Http\Request $request
     * @param string $loginType (web, api, social, qrcode)
     * @param string $reason (invalid_credentials, account_inactive, unauthorized_role, etc.)
     * @return void
     */
    function logFailedLoginActivity(string $email, Request $request, string $loginType = 'api', string $reason = 'invalid_credentials')
    {
        // Get device information
        $userAgent = $request->userAgent();
        $deviceInfo = [
            'user_agent' => $userAgent,
            'platform' => getPlatform($userAgent),
            'browser' => getBrowser($userAgent),
            'device_type' => getDeviceType($userAgent),
        ];

        // Get IP address
        $ipAddress = $request->ip();

        // Prepare activity properties
        $properties = [
            'status' => 'failed',
            'email' => $email,
            'ip_address' => $ipAddress,
            'device_info' => $deviceInfo,
            'login_type' => $loginType,
            'failure_reason' => $reason,
            'password_attempted' => true,
            'timestamp' => now()->toDateTimeString(),
        ];

        // Log the activity (without causedBy since user might not exist)
        activity()
            ->withProperties($properties)
            ->log('login_failed');
    }
}

if (!function_exists('logCustomActivity')) {
    /**
     * Log any custom activity with flexible properties
     *
     * @param string $action The action/event name (e.g., 'order_created', 'payment_processed', 'profile_updated')
     * @param mixed $subject The model being acted upon (optional)
     * @param mixed $causer The user/entity causing the action (optional, defaults to authenticated user)
     * @param array $properties Additional properties to log (optional)
     * @param \Illuminate\Http\Request|null $request Request object to extract IP and device info (optional)
     * @return void
     *
     * @example
     * // Log order creation
     * logCustomActivity('order_created', $order, auth()->user(), [
     *     'order_id' => $order->id,
     *     'total' => $order->total,
     *     'status' => $order->status
     * ], request());
     *
     * // Log payment processing
     * logCustomActivity('payment_processed', $payment, null, [
     *     'amount' => $payment->amount,
     *     'method' => $payment->method,
     *     'status' => 'success'
     * ]);
     *
     * // Log profile update
     * logCustomActivity('profile_updated', $user, $user, [
     *     'fields_changed' => ['name', 'email'],
     *     'old_values' => $oldData,
     *     'new_values' => $newData
     * ], request());
     */
    function logCustomActivity(
        string $action,
        $subject = null,
        $causer = null,
        array $properties = [],
        ?Request $request = null
    ) {
        // Initialize activity logger
        $activityLogger = activity();

        // Set the causer (who performed the action)
        if ($causer) {
            $activityLogger->causedBy($causer);
        } elseif (auth()->check()) {
            $activityLogger->causedBy(auth()->user());
        }

        // Set the subject (what was acted upon)
        if ($subject) {
            $activityLogger->performedOn($subject);
        }

        // Add request context if available
        if ($request) {
            $userAgent = $request->userAgent();
            $properties['ip_address'] = $request->ip();
            $properties['device_info'] = [
                'user_agent' => $userAgent,
                'platform' => getPlatform($userAgent),
                'browser' => getBrowser($userAgent),
                'device_type' => getDeviceType($userAgent),
            ];
        }

        // Add timestamp if not already present
        if (!isset($properties['timestamp'])) {
            $properties['timestamp'] = now()->toDateTimeString();
        }

        // Log the activity with properties
        $activityLogger->withProperties($properties)->log($action);
    }
}

if (!function_exists('logActivity')) {
    /**
     * Simplified alias for logCustomActivity - log activity with automatic context
     *
     * @param string $action The action/event name
     * @param array $properties Additional properties (optional)
     * @param mixed $subject The model being acted upon (optional)
     * @return void
     *
     * @example
     * // Simple activity log
     * logActivity('user_exported_data', ['format' => 'csv', 'records' => 100]);
     *
     * // With subject
     * logActivity('vendor_approved', ['approved_by' => auth()->id()], $vendor);
     */
    function logActivity(string $action, array $properties = [], $subject = null)
    {
        logCustomActivity(
            $action,
            $subject,
            auth()->check() ? auth()->user() : null,
            $properties,
            request()
        );
    }
}

if (!function_exists('getPlatform')) {
    /**
     * Extract platform from user agent
     *
     * @param string $userAgent
     * @return string
     */
    function getPlatform($userAgent)
    {
        if (preg_match('/windows|win32|win64/i', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            return 'iOS';
        }

        return 'Unknown';
    }
}

if (!function_exists('getBrowser')) {
    /**
     * Extract browser from user agent
     *
     * @param string $userAgent
     * @return string
     */
    function getBrowser($userAgent)
    {
        if (preg_match('/edg/i', $userAgent)) {
            return 'Edge';
        } elseif (preg_match('/chrome/i', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            return 'Opera';
        }

        return 'Unknown';
    }
}

if (!function_exists('getDeviceType')) {
    /**
     * Extract device type from user agent
     *
     * @param string $userAgent
     * @return string
     */
    function getDeviceType($userAgent)
    {
        if (preg_match('/mobile/i', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'Tablet';
        }

        return 'Desktop';
    }
}
