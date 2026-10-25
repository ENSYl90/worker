<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Services\DriverSubscriptionService;
use App\Traits\FirebaseAuthTrait;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Models\VendorManager;
use App\Traits\ReferralTrait;
use Illuminate\Contracts\Encryption\DecryptException;

class AuthController extends Controller
{
    //traits
    use FirebaseAuthTrait, ReferralTrait;

    //
    public function login(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email|exists:users',
                'password' => 'required',
            ],
            $messages = [
                'email.exists' => __('Email not associated with any account'),
            ]
        );

        if ($validator->fails()) {
            // Log failed login - invalid email
            if ($request->has('email')) {
                logFailedLoginActivity($request->email, $request, 'api', 'validation_failed');
            }

            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        //
        $user = User::where('email', $request->email)->first();

        if (!empty($request->role) && !$user->hasAnyRole($request->role)) {
            // Log failed login - unauthorized role
            logFailedLoginActivity($request->email, $request, 'api', 'unauthorized_role');

            return response()->json([
                "message" => __("Unauthorized Access. Please try with an authorized credentials")
            ], 401);
        } else if (!$user->is_active) {
            // Log failed login - inactive account
            logFailedLoginActivity($request->email, $request, 'api', 'account_inactive');

            return response()->json([
                "message" => __("Account is not active. Please contact us")
            ], 401);
        } else if ($request->role == "manager" && empty($user->vendor_id)) {
            // Log failed login - manager not assigned to vendor
            logFailedLoginActivity($request->email, $request, 'api', 'manager_no_vendor');

            return response()->json([
                "message" => __("Manager is not assigned to a vendor. Please assign manager to vendor and try again")
            ], 401);
        } else if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            // Log successful login activity
            logLoginActivity($user, $request, 'api');

            //generate tokens
            return $this->authObject($user);
        } else {
            // Log failed login - invalid password
            logFailedLoginActivity($request->email, $request, 'api', 'invalid_password');

            return response()->json([
                "message" => __("Invalid credentials. Please check your password and try again")
            ], 401);
        }
    }

    public function qrlogin(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'code' => 'required'
            ],
            $messages = [
                'code.required' => __('Invalid Login Data'),
            ]
        );

        if ($validator->fails()) {

            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        try {
            //decrypt the code
            $loginObject = decrypt($request->code);
            //
            $user = User::findorfail($loginObject["id"]);

            if (!empty($request->role) && !$user->hasAnyRole($request->role)) {
                // Log failed login - unauthorized role
                logFailedLoginActivity($user->email, $request, 'qrcode', 'unauthorized_role');

                return response()->json([
                    "message" => __("Unauthorized Access. Please try with an authorized credentials")
                ], 401);
            } else if (!$user->is_active) {
                // Log failed login - inactive account
                logFailedLoginActivity($user->email, $request, 'qrcode', 'account_inactive');

                return response()->json([
                    "message" => __("Account is not active. Please contact us")
                ], 401);
            } else if ($request->role == "manager" && empty($user->vendor_id)) {
                // Log failed login - manager not assigned to vendor
                logFailedLoginActivity($user->email, $request, 'qrcode', 'manager_no_vendor');

                return response()->json([
                    "message" => __("Manager is not assigned to a vendor. Please assign manager to vendor and try again")
                ], 401);
            } else {
                // Log successful login activity
                logLoginActivity($user, $request, 'qrcode');

                return $this->authObject($user);
            }
        } catch (DecryptException $e) {
            // Log failed login - invalid QR code
            logFailedLoginActivity('unknown', $request, 'qrcode', 'invalid_qr_code');

            return response()->json([
                "message" => __('Invalid Login Data')
            ], 400);
        } catch (Exception $ex) {
            // Log failed login - general error
            logFailedLoginActivity('unknown', $request, 'qrcode', 'general_error');

            return $this->errorResponse($ex, 'QR login failed', __("Invalid credentials. Please check your password and try again"), 401);
        }
    }


    //
    public function verifyPhoneAccount(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'phone' => 'phone:' . setting('countryCode', "GH"),
            ],
            $messages = [
                'phone.exists' => __('Phone not associated with any account'),
            ]
        );

        if ($validator->fails()) {

            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        //
        $phone = new PhoneNumber($request->phone);
        $user = User::where('phone', 'like', '%' . $phone . '')->first();

        if (!empty($user)) {

            //
            $internationalFormat = (new PhoneNumber($phone, setting('countryCode', "GH")))->formatInternational();
            return response()->json([
                "phone" => $internationalFormat
            ], 200);
        } else {
            return response()->json([
                "message" => __("There is no account accoutiated with provided phone number ") . $phone . "",
            ], 400);
        }
    }

    //
    public function passwordReset(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'phone' => 'phone:' . setting('countryCode', "GH"),
            ],
            $messages = [
                'phone.exists' => __('Phone not associated with any account'),
            ]
        );

        if ($validator->fails()) {

            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        //
        $phone = new PhoneNumber($request->phone);
        $user = User::where('phone', 'like', '%' . $phone . '')->first();

        if (empty($user)) {
            return response()->json([
                "message" => __("There is no account accoutiated with provided phone number ") . $phone . "",
            ], 400);
        }

        //verify firebase token
        try {

            //
            $phone = new PhoneNumber($request->phone);

            if (!empty($request->firebase_id_token)) {
                $firebaseUser = $this->verifyFirebaseIDToken($request->firebase_id_token);

                //verify that the token belongs to the right user
                if ($firebaseUser->phoneNumber == $phone) {

                    //
                    $user = User::where("phone", $phone)->firstorfail();
                    $user->password = Hash::make($request->password);
                    $user->Save();

                    return response()->json([
                        "message" => __("Account Password Updated Successfully"),
                    ], 200);
                } else {
                    return response()->json([
                        "message" => __("Password Reset Failed"),
                    ], 400);
                }
            } else if (!empty($request->verification_token)) {
                $tokenData = decrypt($request->verification_token);
                $encryptedPhone = $tokenData["phone"];
                if ($encryptedPhone != $phone) {
                    throw new Exception(__("Invalid verification token"), 1);
                }
                //verify that the token belongs to the right user
                $user = User::where("phone", $phone)->firstorfail();
                $user->password = Hash::make($request->password);
                $user->Save();

                return response()->json([
                    "message" => __("Account Password Updated Successfully"),
                ], 200);
            } else {
                //throw error
            }
        } catch (Exception $ex) {
            // Expected, user-facing validation errors thrown deliberately above (code 1)
            if ($ex->getCode() === 1) {
                return response()->json([
                    "message" => $ex->getMessage()
                ], 400);
            }

            return $this->errorResponse($ex, 'Password reset failed', __("Password Reset Failed"), 400);
        }
    }

    //
    public function register(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'phone' => 'phone:' . setting('countryCode', "GH") . '|unique:users',
                'password' => 'required',
            ],
            $messages = [
                'email.unique' => __('Email already associated with an account'),
                'phone.unique' => __('Phone already associated with an account'),
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }


        try {

            //
            $phone = new PhoneNumber($request->phone);
            // $rawPhone = new PhoneNumber($request->phone, setting('countryCode', "GH"))->formatNational();
            // $phone = str_replace(' ', '', $rawPhone);
            // logger("Phone", [$request->phone, $phone]);

            //
            $user = User::where('phone', $phone)->first();
            if (!empty($user)) {
                throw new Exception(__("Account with phone already exists"), 1);
            }


            DB::beginTransaction();
            //
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $phone;
            $user->country_code = $request->country_code ?? "";
            $user->password = Hash::make($request->password);
            $user->is_active = true;
            $user->save();

            //refer system is enabled
            $this->handleControllerReferralRegistration($request->code, $user);


            //
            if (empty($request->role)) {
                $user->syncRoles("client");
            } else {
                //only driver and client roles are allowed
                $roleNotAllowed = !in_array($request->role, ["client", "driver", "manager"]);
                if ($roleNotAllowed) {
                    throw new Exception(__("Unauthorized role"), 1);
                }
                $user->syncRoles($request->role);
            }

            DB::commit();
            //generate tokens
            return $this->authObject($user);
        } catch (Exception $error) {
            DB::rollback();

            // Expected, user-facing validation errors thrown deliberately above (code 1)
            if ($error->getCode() === 1) {
                return response()->json([
                    "message" => $error->getMessage()
                ], 400);
            }

            return $this->errorResponse($error, 'Registration failed', __("Registration failed. Please try again"), 500);
        }
    }

    //
    public function profileUpdate(Request $request)
    {

        //
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email,' . Auth::id(),
                'phone' => 'phone:' . setting('countryCode', "GH") . '|unique:users,phone,' . Auth::id(),
                'photo' => 'sometimes|nullable|image|max:2048',
            ],
            $messages = [
                'email.unique' => __('Email already associated with an account'),
                'phone.unique' => __('Phone already associated with an account'),
                'photo.max' => __('Photo must be equal or less to 2MB'),
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }


        try {



            DB::beginTransaction();
            //
            $user = User::find(Auth::id());
            $requestedOnlineStatus = $request->is_online ?? $user->is_online;
            if (
                $user->hasRole('driver')
                && (bool) $requestedOnlineStatus
                && !(new DriverSubscriptionService())->canGoOnline($user)
            ) {
                DB::rollback();
                return response()->json([
                    "message" => __("You need an active driver subscription before you can go online"),
                ], 400);
            }
            //only update the other user data if app is not in demo
            if (!$this->isInDemo()) {
                $user->name = $request->name ?? $user->name;
                $user->email = $request->email ?? $user->email;
                $user->phone = $request->phone ?? $user->phone;
                $user->country_code = $request->country_code ?? $user->country_code;
            }
            $user->is_online = $requestedOnlineStatus;
            $user->save();

            if (!$this->isInDemo()) {
                if ($request->photo) {
                    $user->clearMediaCollection('profile');
                    $user->addMedia($request->file('photo'))->toMediaCollection('profile');
                }
            }

            DB::commit();
            //generate tokens
            return response()->json([
                "message" => __("User profile updated successful"),
                "user" => $user,
            ]);
        } catch (Exception $error) {
            DB::rollback();
            return $this->errorResponse($error, 'Profile update failed', __("Profile update failed. Please try again"), 500);
        }
    }

    //
    public function changePassword(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required',
                'new_password' => 'required|confirmed',
            ],
        );

        if ($validator->fails()) {
            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        //check old password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json([
                "message" => __("Invalid Current Password"),
            ], 400);
        }


        try {

            $this->isDemo();
            DB::beginTransaction();
            //
            $user = User::find(Auth::id());
            $user->password = Hash::make($request->new_password);
            $user->save();

            DB::commit();
            //generate tokens
            return response()->json([
                "message" => __("User password updated successful"),
                "user" => $user,
            ]);
        } catch (Exception $error) {
            DB::rollback();
            return $this->errorResponse($error, 'Password change failed', __("Password change failed. Please try again"), 500);
        }
    }


    //
    public function logout(Request $request)
    {
        $user = \Auth::user();
        if (!empty($user)) {
            if ($user->hasAnyRole('driver')) {
                $user->is_online = 0;
                $user->save();
            }
            $request->user()->currentAccessToken()->delete();
            //remove device tokens
            $user->deListTokens();
            return response()->json([
                "message" => __("Logout successful")
            ]);
        } else {
            return response()->json([
                "message" => __("Logout failed")
            ], 401);
        }
    }

    /**
     *
     * Helpers
     *
     */
    public function authObject($user)
    {
        try {
            if (!$user->is_active) {
                throw new Exception(__("User Account is inactive"), 1);
            }
            $user = User::find($user->id);
            $vendor = Vendor::find($user->vendor_id);
            $vehicle = Vehicle::active()->where('driver_id', $user->id)->first();
            $token = $user->createToken($user->name)->plainTextToken;
            $user->has_multiple_vendors = false;
            //adding has_multiple_vendors to user object
            //first check the user is a manager
            if ($user->hasRole('manager')) {
                //check if the manager has multiple vendors
                $hasManyVendor = VendorManager::where('user_id', $user->id)->count() > 1;
                $user->has_multiple_vendors = $hasManyVendor;
                //append the permissions to the user object
                $user->permissions = $user->getAllPermissions();
            }

            //store/sync firebase tokens for the user
            $user->syncFCMTokens();


            return response()->json([
                "token" => $token,
                "fb_token" => $this->fbToken($user),
                "type" => "Bearer",
                "message" => __("User login successful"),
                "user" => $user,
                "vehicle" => $vehicle,
                "vendor" => $vendor,
            ]);
        } catch (Exception $ex) {
            // Expected, user-facing error thrown deliberately above (code 1)
            if ($ex->getCode() === 1) {
                return response()->json([
                    "message" => $ex->getMessage()
                ], 401);
            }

            return $this->errorResponse(
                $ex,
                __('Auth object generation failed'),
                __("Login failed. Please try again"),
                500
            );
        }
    }

    public function fbToken($user)
    {

        $uId = "user_id_" . $user->id . "";
        $firebaseAuth = $this->getFirebaseAuth();
        $customToken = $firebaseAuth->createCustomToken($uId);
        $customTokenString = $customToken->toString();
        return $customTokenString;
    }
}
