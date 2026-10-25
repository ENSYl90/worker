<?php

namespace App\Http\Controllers;

use App\Services\EmailTemplatePreviewService;
use Illuminate\Http\Request;

class PreviewMailController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type ?? 'order_update';

        $mail = EmailTemplatePreviewService::make($type, null, $request);

        return $mail->render();
    }
}
