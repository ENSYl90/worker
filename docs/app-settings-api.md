---
group: Settings
title: App Settings API
order: 10
---

# App Settings API

The app settings endpoint returns runtime configuration used by mobile and web clients: theme colors, feature flags, auth options, map settings, finance settings, vendor modules, websocket config, Firebase config, website content, and currency/exchange data.

These endpoints are public and do not require authentication.

## Endpoints

```http
GET /api/app/settings
```

Alias:

```http
GET /api/settings
```

Both routes call the same controller action and return the same response.

## Response Shape

Top-level response keys:

- `exchange_rates`: structured exchange-rate data from `CurrencyExchangeRateService`.
- `colors`: configured app color theme from `appColorTheme`.
- `strings`: the main client settings payload.
- `websocket`: Reverb websocket environment config.
- `driver`: currently returned as an empty array.

Example response:

```json
{
  "exchange_rates": {
    "base": "GHS",
    "rates": {
      "USD": 0.07,
      "NGN": 110.25
    }
  },
  "colors": {
    "primary": "#22c55e",
    "accent": "#0f172a"
  },
  "strings": {
    "page": {
      "privacy": "Privacy policy content",
      "terms": "Terms content"
    },
    "google_maps_key": "google-map-api-key",
    "fcm_key": "firebase-server-key",
    "app_name": "Glover",
    "company_name": "Glover",
    "enble_otp": "1",
    "enableOTPLogin": "0",
    "enableEmailLogin": true,
    "enableProfileUpdate": true,
    "otpGateway": "firebase",
    "enableGoogleDistance": "0",
    "enableSingleVendor": "0",
    "enableMultipleVendorOrder": false,
    "enableProofOfDelivery": "1",
    "orderVerificationType": "otp",
    "enableDriverWallet": "0",
    "enableDriverSubscription": false,
    "enableGroceryMode": "0",
    "partnersCanRegister": 1,
    "enableReferSystem": "0",
    "referAmount": "0",
    "enableChat": "1",
    "enableOrderTracking": 1,
    "enableUploadPrescription": 1,
    "enableFatchByLocation": false,
    "enableDriverTypeSwitch": 0,
    "alertDuration": 15,
    "driverSearchRadius": 10,
    "maxDriverOrderAtOnce": 1,
    "distanceCoverLocationUpdate": 10,
    "timePassLocationUpdate": 10,
    "bannerHeight": 150,
    "showVendorTypeImageOnly": 0,
    "autoassignmentsystem": 0,
    "fetchNearbyDriverSystem": 0,
    "useWebsocketAssignment": false,
    "enableParcelVendorByLocation": "0",
    "referRewardAmount": "0",
    "enableParcelMultipleStops": "0",
    "maxParcelStops": "1",
    "what3wordsApiKey": "what3words-api-key",
    "currency": "GH₵",
    "currency_code": "GHS",
    "country_code": "GH",
    "androidDownloadLink": "https://play.google.com/store/apps/details?id=com.example.app",
    "iosDownloadLink": "https://apps.apple.com/app/example",
    "isSingleVendorMode": "0",
    "enabledVendorType": {
      "id": 1,
      "name": "Food",
      "slug": "food",
      "is_active": 1
    },
    "emergencyContact": "911",
    "auth": {
      "googleLogin": true,
      "appleLogin": false,
      "facebbokLogin": false,
      "qrcodeLogin": false
    },
    "ui": {
      "home": {
        "showBannerOnHomeScreen": false,
        "showWalletOnHomeScreen": true
      }
    },
    "taxi": {
      "enabled": true
    },
    "map": {
      "useGoogleOnApp": 1
    },
    "finance": {
      "enableDriverSubscription": false
    },
    "dynamic_link": {
      "prefix": "https://example.page.link",
      "scheme": "glover",
      "android": "com.example.app",
      "ios": "com.example.app"
    },
    "website": {
      "websiteHeaderTitle": "Order anything nearby",
      "websiteHeaderSubtitle": "Fast delivery from local vendors",
      "websiteHeaderImage": "https://example.com/storage/header.png",
      "websiteFooterImage": "https://example.com/storage/footer.png",
      "websiteIntroImage": "https://example.com/storage/intro.png",
      "websiteFooterBrief": "Company footer text",
      "social": {
        "facebook": "https://facebook.com/example",
        "instagram": "https://instagram.com/example"
      }
    },
    "upgrade": {
      "force": false,
      "version": "1.0.0"
    },
    "show_cart": true,
    "file_limit": {
      "prescription": "2048"
    },
    "firebase": {
      "prefix": "prod",
      "db": "(default)"
    }
  },
  "websocket": {
    "REVERB_HOST": "localhost",
    "REVERB_PORT": "8080",
    "REVERB_SCHEME": "http",
    "REVERB_APP_ID": "app-id",
    "REVERB_APP_KEY": "app-key"
  },
  "driver": []
}
```

## Important Client Notes

- Some boolean-like values are returned as strings or integers because they come directly from app settings, for example `enableChat: "1"` and `enableOrderTracking: 1`.
- Some keys are intentionally documented with their current API spelling:
  - `enble_otp`
  - `enableFatchByLocation`
  - `facebbokLogin`
  - `autoassignmentsystem`
  - `total_earnig` appears in vendor details, not this endpoint.
- `enabledVendorType` is the first active vendor type.
- `show_cart` is `true` when at least one active sales-capable vendor type exists.
- `useWebsocketAssignment` is `true` when `fetchNearbyDriverSystem` is set to `2`.
- `websiteHeaderImage`, `websiteFooterImage`, and `websiteIntroImage` are returned as absolute URLs using Laravel's `url()` helper.

## Common Usage

Fetch settings during app boot:

```http
GET /api/settings
Accept: application/json
```

Then cache the response client-side and use it to configure:

- login method visibility
- OTP behavior
- wallet and cart visibility
- map provider behavior
- websocket connection
- currency display
- website/app marketing content
- vendor module availability
