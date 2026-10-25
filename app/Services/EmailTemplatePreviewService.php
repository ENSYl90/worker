<?php

namespace App\Services;

use App\Mail\DocumentRequestMail;
use App\Mail\NewAccountMail;
use App\Mail\NewVendorMail;
use App\Mail\OrderUpdateMail;
use App\Mail\OTPMail;
use App\Mail\VendorUpdateMail;
use App\Models\DocumentRequest;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;

class EmailTemplatePreviewService
{
    /**
     * Build a sample-data instance of the mailable for the given template key.
     * Falls back to static sample data when no matching DB record exists,
     * and to a plain static notice if rendering fails for any other reason.
     *
     * @param string $key
     * @param array|null $previewOverride ['subject' => ?string, 'body' => ?string] to render unsaved draft content
     * @param Request|null $request only used for the order_update slug/id lookup
     */
    public static function make(string $key, ?array $previewOverride = null, ?Request $request = null)
    {
        try {
            $mail = match ($key) {
                'new_account' => new NewAccountMail(
                    User::first() ?? self::fakeUser(),
                    'sample-password',
                    $previewOverride
                ),
                'new_vendor' => new NewVendorMail(Vendor::first() ?? self::fakeVendor(true), $previewOverride),
                'otp' => new OTPMail('123456', User::first() ?? self::fakeUser(), $previewOverride),
                'vendor_update_active' => new VendorUpdateMail(
                    Vendor::where('is_active', true)->first() ?? Vendor::first() ?? self::fakeVendor(true),
                    $previewOverride
                ),
                'vendor_update_deactivated' => new VendorUpdateMail(
                    Vendor::where('is_active', false)->first() ?? Vendor::first() ?? self::fakeVendor(false),
                    $previewOverride
                ),
                'document_request_approved' => new DocumentRequestMail(
                    DocumentRequest::where('status', 'approved')->first()
                        ?? DocumentRequest::first()
                        ?? self::fakeDocumentRequest('approved'),
                    $previewOverride
                ),
                'document_request_rejected' => new DocumentRequestMail(
                    DocumentRequest::where('status', 'rejected')->first()
                        ?? DocumentRequest::first()
                        ?? self::fakeDocumentRequest('rejected'),
                    $previewOverride
                ),
                'document_request_resubmit' => new DocumentRequestMail(
                    DocumentRequest::whereNotIn('status', ['approved', 'rejected'])->first()
                        ?? DocumentRequest::first()
                        ?? self::fakeDocumentRequest('pending'),
                    $previewOverride
                ),
                default => self::orderUpdate($request),
            };

            // force render now so failures are caught here, not later by the caller
            $mail->render();

            return $mail;
        } catch (\Throwable $e) {
            return self::placeholder($previewOverride);
        }
    }

    protected static function fakeUser(): User
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
        $user->exists = false;

        return $user;
    }

    protected static function fakeVendor(bool $isActive): Vendor
    {
        $vendor = new Vendor([
            'name' => 'Sample Vendor',
            'email' => 'vendor@example.com',
            'address' => '123 Sample Street',
            'is_active' => $isActive,
        ]);
        $vendor->use_subscription = false;
        $vendor->exists = false;

        return $vendor;
    }

    protected static function fakeDocumentRequest(string $status): DocumentRequest
    {
        $documentRequest = new DocumentRequest();
        $documentRequest->status = $status;
        $documentRequest->model_type = User::class;
        $documentRequest->exists = false;
        $documentRequest->setRelation('model', self::fakeUser());

        return $documentRequest;
    }

    protected static function orderUpdate(?Request $request)
    {
        $request = $request ?? request();
        $slug = $request->slug ?? "";
        $id = $request->id;

        if ($slug == 'taxi') {
            $order = Order::whereHas('taxi_order')
                ->when($id, fn ($query) => $query->where('id', $id))
                ->first();
        } elseif ($slug == 'package') {
            $order = Order::whereHas('package_type')
                ->when($id, fn ($query) => $query->where('id', $id))
                ->first();
        } elseif ($slug == 'service') {
            $order = Order::whereHas('order_service')
                ->when($id, fn ($query) => $query->where('id', $id))
                ->first();
        } else {
            $order = Order::whereHas('products')
                ->when($id, fn ($query) => $query->where('id', $id))
                ->first();
        }

        if (!$order) {
            // no representative order to fake safely (needs vendor/products/payment_method
            // relations) — let the caller's catch produce the static placeholder instead.
            throw new \RuntimeException('No sample order available to preview.');
        }

        return new OrderUpdateMail($order);
    }

    /**
     * A minimal stand-in exposing the same surface callers rely on
     * (->subject property, ->render()) when no real/fake data can be built.
     */
    protected static function placeholder(?array $previewOverride = null)
    {
        $subject = $previewOverride['subject'] ?? __('Preview unavailable');
        $body = $previewOverride['body'] ?? '';

        return new class($subject, $body) {
            public $subject;
            public $body;

            public function __construct($subject, $body)
            {
                $this->subject = $subject;
                $this->body = $body;
            }

            public function render()
            {
                $notice = e(__('No sample data is available yet to render a full preview. Showing your draft content as-is (placeholders like {{user_name}} will be replaced with real data when this email is actually sent).'));
                $body = !empty($this->body) ? $this->body : '<p>' . e(__('No content yet.')) . '</p>';

                return <<<HTML
<!doctype html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; padding: 1.5rem; color: #111;">
    <div style="background:#fef3c7;border:1px solid #f59e0b;padding:12px 16px;border-radius:6px;margin-bottom:16px;font-size:13px;">
        {$notice}
    </div>
    <p style="font-weight:bold;font-size:18px;">{$this->subject}</p>
    <div>{$body}</div>
</body>
</html>
HTML;
            }
        };
    }
}
