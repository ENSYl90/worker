<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserToken;

class AuthController extends Controller
{
    //
    public function logout(Request $request)
    {
        // Clear the 2FA session
        $request->session()->forget('two_factor_confirmed');
        $deviceIdentifier = $request->device_uuid ?? $request->cookie('device_uuid');
        UserToken::where('device_uuid', $deviceIdentifier)->where('user_id', Auth::id())->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
