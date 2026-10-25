<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('amI')) {
    /**
     * Check if the authenticated user has a specific role
     *
     * @param string|array $role Role name(s) to check
     * @param string $guard Guard to use (optional)
     * @return bool
     */
    function amI($role, $guard = null)
    {
        $user = Auth::guard($guard)->user();

        if (!$user) {
            return false;
        }

        // If user doesn't have roles relationship, return false
        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        // Handle single role or array of roles
        if (is_array($role)) {
            return $user->hasAnyRole($role);
        }

        return $user->hasRole($role);
    }
}

if (!function_exists('canI')) {
    /**
     * Check if the authenticated user has a specific permission
     *
     * @param string|array $permission Permission name(s) to check
     * @param string $guard Guard to use (optional)
     * @return bool
     */
    function canI($permission, $guard = null)
    {
        $user = Auth::guard($guard)->user();

        if (!$user) {
            return false;
        }

        // If user doesn't have permissions relationship, return false
        if (!method_exists($user, 'can')) {
            return false;
        }

        // Handle single permission or array of permissions
        if (is_array($permission)) {
            return $user->hasAnyPermission($permission);
        }

        return $user->can($permission);
    }
}

if (!function_exists('amIAll')) {
    /**
     * Check if the authenticated user has all specified roles
     *
     * @param array $roles Array of role names
     * @param string $guard Guard to use (optional)
     * @return bool
     */
    function amIAll(array $roles, $guard = null)
    {
        $user = Auth::guard($guard)->user();

        if (!$user || !method_exists($user, 'hasAllRoles')) {
            return false;
        }

        return $user->hasAllRoles($roles);
    }
}

if (!function_exists('canIAll')) {
    /**
     * Check if the authenticated user has all specified permissions
     *
     * @param array $permissions Array of permission names
     * @param string $guard Guard to use (optional)
     * @return bool
     */
    function canIAll(array $permissions, $guard = null)
    {
        $user = Auth::guard($guard)->user();

        if (!$user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$user->can($permission)) {
                return false;
            }
        }

        return true;
    }
}



//canIAny
if (!function_exists('canIAny')) {
    function canIAny($permissions, $guard = null)
    {
        $user = Auth::guard($guard)->user();

        if (!$user) {
            return false;
        }

        if ($user->canAny($permissions)) {
            return true;
        }
        return false;
    }
}


//custom functions 
if (!function_exists('canIAll')) {
    /**
     * Check if the authenticated user has all specified permissions
     *
     * @param array $permissions Array of permission names
     * @param string $guard Guard to use (optional)
     * @return bool
     */
    function canIAll(array $permissions, $guard = null)
    {
        $user = Auth::guard($guard)->user();

        if (!$user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$user->can($permission)) {
                return false;
            }
        }

        return true;
    }
}
