<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Property;
use App\Models\Service;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DynamicLinkController extends Controller
{
    private function detectMobileApp(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        $isAndroid = stripos($userAgent, 'android') !== false;
        $isIOS = stripos($userAgent, 'iphone') !== false || stripos($userAgent, 'ipad') !== false;

        return [
            'is_mobile' => $isAndroid || $isIOS,
            'is_android' => $isAndroid,
            'is_ios' => $isIOS
        ];
    }

    private function getAppStoreUrls()
    {
        return [
            'android_package' => env('dynamic_link.android', 'online.edentech.fuodz'),
            'ios_bundle' => env('dynamic_link.ios', 'online.edentech.fuodz'),
            'play_store' => 'https://play.google.com/store/apps/details?id=' . env('dynamic_link.android', 'online.edentech.fuodz'),
            'app_store' => 'https://apps.apple.com/app/id' . env('dynamic_link.ios_app_id', env('dynamic_link.ios_id', '')),
            'scheme' => env('dynamic_link.scheme', 'glover'),
        ];
    }

    public function handleProductLink(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $device = $this->detectMobileApp($request);
            $storeUrls = $this->getAppStoreUrls();

            if ($device['is_mobile']) {
                $deepLink = $this->generateDeepLink('product', $id);

                if ($device['is_android']) {
                    $scheme = $storeUrls['scheme'];
                    $package = $storeUrls['android_package'];
                    $fallbackUrl = $storeUrls['play_store'];
                    $appName = env('APP_NAME', 'App');

                    return view('dynamic-link.android-redirect', compact('scheme', 'package', 'fallbackUrl', 'appName', 'id'));
                } elseif ($device['is_ios']) {
                    $universalLink = "{$storeUrls['scheme']}://product/{$id}";
                    $fallbackUrl = $storeUrls['app_store'];
                    $appName = env('APP_NAME', 'App');
                    return view('dynamic-link.ios-redirect', compact('universalLink', 'fallbackUrl', 'appName'));
                }
            }

            return redirect()->route('product.description.render', $id);
        } catch (\Exception $e) {
            Log::error('Dynamic link error for product: ' . $e->getMessage());
            return redirect('/');
        }
    }

    public function handlePropertyLink(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);
            $device = $this->detectMobileApp($request);
            $storeUrls = $this->getAppStoreUrls();

            if ($device['is_mobile']) {
                $deepLink = $this->generateDeepLink('property', $id);

                if ($device['is_android']) {
                    $scheme = $storeUrls['scheme'];
                    $package = $storeUrls['android_package'];
                    $fallbackUrl = $storeUrls['play_store'];
                    $appName = env('APP_NAME', 'App');

                    return view('dynamic-link.android-redirect', compact('scheme', 'package', 'fallbackUrl', 'appName', 'id'));
                } elseif ($device['is_ios']) {
                    $universalLink = "{$storeUrls['scheme']}://property/{$id}";
                    $fallbackUrl = $storeUrls['app_store'];
                    $appName = env('APP_NAME', 'App');
                    return view('dynamic-link.ios-redirect', compact('universalLink', 'fallbackUrl', 'appName'));
                }
            }

            return redirect()->route('property.description.render', $id);
        } catch (\Exception $e) {
            Log::error('Dynamic link error for property: ' . $e->getMessage());
            return redirect('/');
        }
    }

    public function handleServiceLink(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);
            $device = $this->detectMobileApp($request);
            $storeUrls = $this->getAppStoreUrls();

            if ($device['is_mobile']) {
                $deepLink = $this->generateDeepLink('service', $id);

                if ($device['is_android']) {
                    $scheme = $storeUrls['scheme'];
                    $package = $storeUrls['android_package'];
                    $fallbackUrl = $storeUrls['play_store'];
                    $appName = env('APP_NAME', 'App');

                    return view('dynamic-link.android-redirect', compact('scheme', 'package', 'fallbackUrl', 'appName', 'id'));
                } elseif ($device['is_ios']) {
                    $universalLink = "{$storeUrls['scheme']}://service/{$id}";
                    $fallbackUrl = $storeUrls['app_store'];
                    $appName = env('APP_NAME', 'App');
                    return view('dynamic-link.ios-redirect', compact('universalLink', 'fallbackUrl', 'appName'));
                }
            }

            return redirect()->route('service.description.render', $id);
        } catch (\Exception $e) {
            Log::error('Dynamic link error for service: ' . $e->getMessage());
            return redirect('/');
        }
    }

    public function handleVendorLink(Request $request, $id)
    {
        try {
            $vendor = Vendor::findOrFail($id);
            $device = $this->detectMobileApp($request);
            $storeUrls = $this->getAppStoreUrls();

            if ($device['is_mobile']) {
                $deepLink = $this->generateDeepLink('vendor', $id);

                if ($device['is_android']) {
                    $scheme = $storeUrls['scheme'];
                    $package = $storeUrls['android_package'];
                    $fallbackUrl = $storeUrls['play_store'];
                    $appName = env('APP_NAME', 'App');

                    return view('dynamic-link.android-redirect', compact('scheme', 'package', 'fallbackUrl', 'appName', 'id'));
                } elseif ($device['is_ios']) {
                    $universalLink = "{$storeUrls['scheme']}://vendor/{$id}";
                    $fallbackUrl = $storeUrls['app_store'];
                    $appName = env('APP_NAME', 'App');
                    return view('dynamic-link.ios-redirect', compact('universalLink', 'fallbackUrl', 'appName'));
                }
            }

            return redirect()->route('vendor.description.render', $id);
        } catch (\Exception $e) {
            Log::error('Dynamic link error for vendor: ' . $e->getMessage());
            return redirect('/');
        }
    }

    private function generateDeepLink($type, $id)
    {
        $scheme = env('dynamic_link.scheme', 'glover');
        return "{$scheme}://{$type}/{$id}";
    }

    public function generateShareableLink($type, $id)
    {
        $baseUrl = config('app.url');
        return "{$baseUrl}/link/{$type}/{$id}";
    }

    public function assetLinks()
    {
        $androidPackage = env('dynamic_link.android', 'online.edentech.fuodz');
        $sha256Fingerprint = env('dynamic_link.android_sha256', 'REPLACE_WITH_YOUR_APP_SHA256_FINGERPRINT');

        $assetLinks = [
            [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => $androidPackage,
                    'sha256_cert_fingerprints' => [
                        $sha256Fingerprint
                    ]
                ]
            ]
        ];

        return response()->json($assetLinks, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

}