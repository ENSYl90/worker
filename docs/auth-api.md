---
group: Auth
title: Auth API
order: 10
---

# Auth API

Authentication endpoints are available under the API prefix.

Unless noted otherwise, request and response bodies are JSON. Authenticated endpoints require a Laravel Sanctum bearer token returned by the login, registration, OTP login, Firebase phone login, social login, or QR login endpoints.

```http
Authorization: Bearer {token}
```

## Common Auth Response

Successful login-style endpoints return the same auth object:

```json
{
  "token": "1|plain-text-sanctum-token",
  "fb_token": "firebase-custom-token",
  "type": "Bearer",
  "message": "User login successful",
  "user": {
    "id": 1,
    "name": "Jane Customer",
    "email": "jane@example.com",
    "phone": "+233501234567",
    "is_active": true
  },
  "vehicle": null,
  "vendor": null
}
```

Validation errors usually return:

```json
{
  "message": "First readable validation error"
}
```

Status code: `400`

Unauthorized or inactive-account errors usually return status code `401`.

## Login

```http
POST /api/login
Content-Type: application/json
```

Request body:

```json
{
  "email": "jane@example.com",
  "password": "password",
  "role": "client"
}
```

Fields:

- `email` is required and must exist in `users`.
- `password` is required.
- `role` is optional. When provided, the user must have that role. For `manager`, the user must also be assigned to a vendor.

Successful response:

```json
{
  "token": "1|plain-text-sanctum-token",
  "fb_token": "firebase-custom-token",
  "type": "Bearer",
  "message": "User login successful",
  "user": {
    "id": 1,
    "name": "Jane Customer",
    "email": "jane@example.com",
    "phone": "+233501234567",
    "is_active": true
  },
  "vehicle": null,
  "vendor": null
}
```

Error responses:

```json
{
  "message": "Invalid credentials. Please check your password and try again"
}
```

Status code: `401`

```json
{
  "message": "Unauthorized Access. Please try with an authorized credentials"
}
```

Status code: `401`

```json
{
  "message": "Account is not active. Please contact us"
}
```

Status code: `401`

## QR Code Login

```http
POST /api/login/qrcode
Content-Type: application/json
```

Request body:

```json
{
  "code": "encrypted-login-code",
  "role": "manager"
}
```

Fields:

- `code` is required. It must decrypt to an object containing a user `id`.
- `role` is optional and is checked the same way as normal login.

Successful response:

```json
{
  "token": "1|plain-text-sanctum-token",
  "fb_token": "firebase-custom-token",
  "type": "Bearer",
  "message": "User login successful",
  "user": {
    "id": 1,
    "name": "Vendor Manager",
    "email": "manager@example.com",
    "phone": "+233501234567",
    "is_active": true
  },
  "vehicle": null,
  "vendor": {
    "id": 1,
    "name": "Fresh Market"
  }
}
```

Invalid code response:

```json
{
  "message": "Invalid Login Data"
}
```

Status code: `400`

## Social Login

```http
POST /api/social/login
Content-Type: application/json
```

Request body:

```json
{
  "email": "jane@example.com",
  "provider": "google",
  "firebase_id_token": "firebase-provider-token"
}
```

Fields:

- `email` is required.
- `firebase_id_token` is required.
- `provider` supports `google`, `facebook`, and `apple`.
- `nonce` may be sent for Apple ID token verification.
- `uid` may be sent for the Apple fallback path.

Behavior:

- Verifies the Firebase provider token.
- Finds a user whose email matches the Firebase email.
- If `auto_create_social_account` is enabled and no user exists, creates an active `client` account and emails the generated password.
- Returns a login response when the Firebase email matches the account email.

Successful response:

```json
{
  "token": "1|plain-text-sanctum-token",
  "fb_token": "firebase-custom-token",
  "type": "Bearer",
  "message": "User login successful",
  "user": {
    "id": 1,
    "name": "Jane Customer",
    "email": "jane@example.com",
    "phone": "+233501234567",
    "is_active": true
  },
  "vehicle": null,
  "vendor": null
}
```

Error response:

```json
{
  "message": "Invalid credentials. Please check your phone and try again"
}
```

Status code: `400`

## Register Client

```http
POST /api/register
Content-Type: application/json
```

Request body:

```json
{
  "name": "Jane Customer",
  "email": "jane@example.com",
  "phone": "+233501234567",
  "country_code": "GH",
  "password": "password",
  "role": "client",
  "code": "REFERRALCODE"
}
```

Fields:

- `name` is required.
- `email` is required, must be unique, and must be a valid email address.
- `phone` must be valid for the configured country code and must be unique.
- `password` is required.
- `country_code` is optional.
- `code` is optional referral code data.
- `role` is optional. Defaults to `client`. Allowed values are `client`, `driver`, and `manager`.

Successful response:

```json
{
  "token": "1|plain-text-sanctum-token",
  "fb_token": "firebase-custom-token",
  "type": "Bearer",
  "message": "User login successful",
  "user": {
    "id": 1,
    "name": "Jane Customer",
    "email": "jane@example.com",
    "phone": "+233501234567",
    "is_active": true
  },
  "vehicle": null,
  "vendor": null
}
```

Error responses:

```json
{
  "message": "Unauthorized role"
}
```

Status code: `500`

```json
{
  "message": "Account with phone already exists"
}
```

Status code: `500`

## Register Vendor Partner

```http
POST /api/vendor/register
Content-Type: multipart/form-data
```

Request fields:

```json
{
  "name": "Vendor Manager",
  "email": "manager@example.com",
  "phone": "+233501234567",
  "password": "password",
  "vendor_name": "Fresh Market",
  "vendor_email": "store@example.com",
  "vendor_phone": "+233551234567",
  "vendor_type_id": 1,
  "address": "Accra",
  "latitude": 5.6037,
  "longitude": -0.187,
  "logo": "image file",
  "feature_image": "image file",
  "documents[]": "document files"
}
```

Required fields:

- `name`, `email`, `phone`, `password`
- `vendor_name`, `vendor_email`, `vendor_phone`, `vendor_type_id`, `address`

Optional fields:

- `latitude`, `longitude`
- `logo`, image file
- `feature_image`, image file
- `documents[]`, image/document files

Behavior:

- Creates an inactive user with the `manager` role.
- Creates an inactive vendor.
- Assigns the manager to the created vendor.
- Does not return an auth token because the account requires review.

Successful response:

```json
{
  "message": "Account Created Successfully. Your account will be reviewed and you will be notified via email/sms when account gets approved. Thank you"
}
```

## Register Driver Partner

```http
POST /api/driver/register
Content-Type: multipart/form-data
```

Request fields:

```json
{
  "name": "Driver Name",
  "email": "driver@example.com",
  "phone": "+233501234567",
  "password": "password",
  "driver_type": "taxi",
  "vehicle_type_id": 1,
  "car_model_id": 1,
  "reg_no": "GR-1234-26",
  "color": "Black",
  "documents[]": "document files",
  "referral_code": "REFERRALCODE"
}
```

Required fields:

- `name`, `email`, `phone`, `password`

Optional fields:

- `driver_type`; when set to `taxi`, a vehicle record is created.
- `vehicle_type_id`, must exist when provided.
- `car_model_id`, used for taxi vehicle registration.
- `reg_no`, `color`
- `documents[]`
- `referral_code`

Behavior:

- Creates an inactive user with the `driver` role.
- Creates or updates the driver's driver-type record.
- For taxi drivers, creates an inactive vehicle record.
- Does not return an auth token because the account requires review.

Successful response:

```json
{
  "message": "Account Created Successfully. Your account will be reviewed and you will be notified via email/sms when account gets approved. Thank you"
}
```

## Send OTP

```http
POST /api/otp/send
Content-Type: application/json
```

Middleware:

- `throttle:otp`

Request body:

```json
{
  "phone": "+233501234567",
  "is_login": true
}
```

Fields:

- `phone` is the destination phone number.
- `is_login` is optional. When present, the phone number must belong to an existing user.

Behavior:

- Generates a six-digit OTP.
- Stores or replaces the OTP for the phone number.
- Dispatches `OTPSendJob`.

Successful response:

```json
{
  "message": "OTP sent successfully",
  "instructions": "Sent"
}
```

Login phone-not-found response:

```json
{
  "message": "Phone number not associated with any account"
}
```

Status code: `401`

## Verify OTP

```http
POST /api/otp/verify
Content-Type: application/json
```

Middleware:

- `throttle:otp`

Request body:

```json
{
  "phone": "+233501234567",
  "code": "123456",
  "is_login": true
}
```

Fields:

- `phone` is required by behavior.
- `code` is required by behavior.
- `is_login` is optional.

Behavior:

- Deletes the OTP after a successful match.
- If `is_login` is present, logs in the user with that phone number and returns a login response.
- If `is_login` is absent, returns an encrypted verification token that can be used for password reset.

Successful login response:

```json
{
  "token": "1|plain-text-sanctum-token",
  "fb_token": "firebase-custom-token",
  "type": "Bearer",
  "message": "User login successful",
  "user": {
    "id": 1,
    "name": "Jane Customer",
    "email": "jane@example.com",
    "phone": "+233501234567",
    "is_active": true
  },
  "vehicle": null,
  "vendor": null
}
```

Successful non-login response:

```json
{
  "message": "OTP verification successful",
  "token": "encrypted-verification-token"
}
```

Invalid response:

```json
{
  "message": "Invalid OTP"
}
```

Status code: `400`

## Verify Firebase Phone Token

```http
POST /api/otp/firebase/verify
Content-Type: application/json
```

Middleware:

- `throttle:otp`

Request body:

```json
{
  "phone": "+233501234567",
  "firebase_id_token": "firebase-phone-id-token"
}
```

Fields:

- `phone` must be valid for the configured country code and must belong to a user.
- `firebase_id_token` is required for a successful login.

Behavior:

- Verifies the Firebase ID token.
- Confirms the token phone number matches the supplied phone number.
- Returns a login response.

Successful response:

```json
{
  "token": "1|plain-text-sanctum-token",
  "fb_token": "firebase-custom-token",
  "type": "Bearer",
  "message": "User login successful",
  "user": {
    "id": 1,
    "name": "Jane Customer",
    "email": "jane@example.com",
    "phone": "+233501234567",
    "is_active": true
  },
  "vehicle": null,
  "vendor": null
}
```

Error response:

```json
{
  "message": "Invalid credentials. Please check your phone and try again"
}
```

Status code: `400`

## Verify Phone Account

```http
GET /api/verify/phone?phone=%2B233501234567
```

Query parameters:

- `phone` must be valid for the configured country code.

Successful response:

```json
{
  "phone": "+233 50 123 4567"
}
```

Phone-not-found response:

```json
{
  "message": "There is no account accoutiated with provided phone number +233501234567"
}
```

Status code: `400`

## Reset Password

```http
POST /api/password/reset/init
Content-Type: application/json
```

Request body with Firebase verification:

```json
{
  "phone": "+233501234567",
  "password": "new-password",
  "firebase_id_token": "firebase-phone-id-token"
}
```

Request body with OTP verification token:

```json
{
  "phone": "+233501234567",
  "password": "new-password",
  "verification_token": "encrypted-verification-token"
}
```

Fields:

- `phone` must be valid for the configured country code and must belong to a user.
- `password` is required by behavior.
- Send either `firebase_id_token` or `verification_token`.

Successful response:

```json
{
  "message": "Account Password Updated Successfully"
}
```

Error response:

```json
{
  "message": "Password Reset Failed"
}
```

Status code: `400`

## Get My Profile

```http
GET /api/my/profile
Authorization: Bearer {token}
```

Middleware:

- `auth:sanctum`
- `user.active.check`

Successful response:

```json
{
  "id": 1,
  "name": "Jane Customer",
  "email": "jane@example.com",
  "phone": "+233501234567"
}
```

## Update Profile

```http
PUT /api/profile/update
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

Request fields:

```json
{
  "name": "Jane Customer",
  "email": "jane@example.com",
  "phone": "+233501234567",
  "country_code": "GH",
  "is_online": true,
  "photo": "image file"
}
```

Fields:

- `name` is optional and must be a string.
- `email` is optional, must be valid, and must be unique except for the current user.
- `phone` must be valid for the configured country code and must be unique except for the current user.
- `country_code` is optional.
- `is_online` is optional.
- `photo` is optional, must be an image, and must be no larger than 2 MB.

Successful response:

```json
{
  "message": "User profile updated successful",
  "user": {
    "id": 1,
    "name": "Jane Customer",
    "email": "jane@example.com"
  }
}
```

## Change Password

```http
PUT /api/profile/password/update
Authorization: Bearer {token}
Content-Type: application/json
```

Request body:

```json
{
  "password": "current-password",
  "new_password": "new-password",
  "new_password_confirmation": "new-password"
}
```

Fields:

- `password` is required and must match the current password.
- `new_password` is required and must be confirmed by `new_password_confirmation`.

Successful response:

```json
{
  "message": "User password updated successful",
  "user": {
    "id": 1,
    "email": "jane@example.com"
  }
}
```

Invalid current password response:

```json
{
  "message": "Invalid Current Password"
}
```

Status code: `400`

## Logout

```http
GET /api/logout
Authorization: Bearer {token}
```

Behavior:

- Deletes the current Sanctum access token.
- Removes synced device tokens.
- Sets `is_online` to `0` for drivers.

Successful response:

```json
{
  "message": "Logout successful"
}
```

## Delete Account

```http
DELETE /api/account/delete
Authorization: Bearer {token}
Content-Type: application/json
```

Request body:

```json
{
  "password": "current-password"
}
```

Fields:

- `password` is required and must match the current password.

Behavior:

- Prefixes the current email and phone with the configured account removal code.
- Soft deletes the user record.

Successful response:

```json
{
  "message": "Account deleted successfully",
  "user": {
    "id": 1,
    "email": "removed_jane@example.com",
    "phone": "removed_+233501234567"
  }
}
```

Invalid current password response:

```json
{
  "message": "Invalid Current Password"
}
```

Status code: `400`
