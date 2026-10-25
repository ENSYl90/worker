<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\NewAccountMail;
use App\Models\User;
use App\Services\MailHandlerService;
use Illuminate\Http\Request;
use App\Traits\FirebaseAuthTrait;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Value\Provider;

class SocialLoginController extends Controller
{

    //traits
    use FirebaseAuthTrait;

    public function login(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'firebase_id_token' => 'required',
            ],
            $messages = [
                'email.exists' => __('Email not associated with any account'),
            ]
        );

        if ($validator->fails()) {
            // Log failed login - validation failed
            if ($request->has('email')) {
                logFailedLoginActivity($request->email, $request, 'social', 'validation_failed');
            }

            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 401);
        }

        //
        $user = User::where('email', 'like', '%' . $request->email . '')->first();

        //verify firebase token
        try {


            if (!empty($request->firebase_id_token)) {

                if ($request->provider == "google") {
                    $firebaseUser = $this->getFirebaseAuth()->signInWithIdpIdToken("google.com", $request->firebase_id_token);
                    $firebaseUserEmail = $firebaseUser->data()["email"];
                } else if ($request->provider == "facebook") {
                    $firebaseUser = $this->getFirebaseAuth()->signInWithIdpAccessToken("facebook.com", $request->firebase_id_token);
                    $firebaseUserEmail = $firebaseUser->data()["email"];
                    //TODO
                } else if ($request->provider == "apple") {
                    try {
                        $firebaseUser = $this->getFirebaseAuth()->signInWithIdpIdToken(
                            "apple.com",
                            $request->firebase_id_token,
                            null,
                            null,
                            $request->nonce,
                        );
                        $firebaseUserEmail = $firebaseUser->data()["email"];
                    } catch (\Exception $ex) {
                        // logger("Apple login error", [$ex]);
                        $signInResult = $this->getFirebaseAuth()->signInAsUser($request->uid);
                        $firebaseUser = $this->verifyFirebaseIDToken($signInResult->data()['idToken']);
                        logger("Apple login error", [$signInResult->data(), $firebaseUser]);
                        $firebaseUserEmail = $firebaseUser->email;
                    }
                }


                //if there is no user account, then create new account base on the data
                if (empty($user) && (bool) setting('auto_create_social_account', 0)) {
                    //
                    try {
                        \DB::beginTransaction();

                        $user = new User();
                        //formed name
                        $formedName = explode("@", $firebaseUserEmail)[0];

                        if ($request->provider == "apple") {
                            $fbUserData = $firebaseUser;
                            $user->name = $fbUserData->displayName ?? $formedName;
                            $user->phone = $fbUserData->phoneNumber ?? null;
                        } else {
                            $fbUserData = $firebaseUser->data() ?? $firebaseUser;
                            $user->name = $fbUserData["fullName"] ?? $fbUserData["name"] ?? $fbUserData["displayName"] ?? $formedName;
                            $user->phone = $fbUserData['phone'] ?? null;
                        }

                        $password = \Str::random(8);
                        $user->email = $firebaseUserEmail;
                        $user->country_code = "";
                        $user->password = \Hash::make($password);
                        $user->is_active = true;
                        $user->save();
                        $user->syncRoles("client");

                        //send mail
                        MailHandlerService::sendMail(new NewAccountMail($user, $password), $user->email);

                        \DB::commit();
                    } catch (\Expection $ex) {
                        \DB::rollback();
                        logger("Social login error", [$ex]);
                    }
                }


                //verify that the token belongs to the right user
                if ($user != null && $firebaseUserEmail == ($user->email ?? '')) {
                    // Log successful login activity
                    logLoginActivity($user, $request, 'social');

                    $authController = new AuthController();
                    return $authController->authObject($user);
                } else {
                    // Log failed login - email mismatch
                    logFailedLoginActivity($request->email, $request, 'social', 'email_mismatch');

                    return response()->json([
                        "message" => __("Invalid credentials. Please check your phone and try again"),
                    ], 400);
                }
            } else {
                // Log failed login - no firebase token
                logFailedLoginActivity($request->email, $request, 'social', 'no_firebase_token');

                //verify that the token belongs to the right user
                return response()->json([
                    "message" => __("Invalid Account"),
                ], 200);
            }
        } catch (\Expection $ex) {
            // Log failed login - exception
            logFailedLoginActivity($request->email ?? 'unknown', $request, 'social', 'exception_error');

            return response()->json([
                "message" => $ex->getMessage(),
            ], 400);
        }
    }
}