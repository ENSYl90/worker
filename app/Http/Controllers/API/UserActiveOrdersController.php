<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\UserActiveOrdersUpdatedJob;
use Illuminate\Support\Facades\Auth;

class UserActiveOrdersController extends Controller
{
    public function emit()
    {
        $activeOrdersCount = UserActiveOrdersUpdatedJob::emitForUser(Auth::id());

        return response()->json([
            'message' => __('Active orders event emitted successfully.'),
            'active' => $activeOrdersCount,
        ]);
    }
}
