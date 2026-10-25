<?php

namespace App\Traits;

use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use MrShan0\PHPFirestore\FirestoreClient;
use MrShan0\PHPFirestore\Authentication\FirestoreAuthentication;

trait FirebaseAuthTrait
{

    /**
     * Key used to persist the firebase service account JSON in the
     * database-backed `settings` table, as a fallback for when the
     * file on disk is unavailable (e.g. container volume not persisted).
     */
    private $firebaseServiceAccountSettingKey = 'firebaseServiceAccountJson';

    /**
     * Cache key holding the resolved service account JSON, so repeated
     * calls don't re-hit the disk/DB on every request.
     */
    private $firebaseServiceAccountCacheKey = 'firebase_service_account_json';

    /**
     * Store the firebase service account JSON content both on disk
     * (storage/app/vault/...) and in the database, so it survives even
     * if the file on disk is lost (e.g. Docker volume not persisted).
     */
    public function storeFirebaseServiceAccount(string $serviceAccountJson, string $fileName = 'firebase_service.json')
    {
        $serviceKeyPath = 'vault/' . basename($fileName ?: 'firebase_service.json');

        Storage::put($serviceKeyPath, $serviceAccountJson);
        setting([
            'serviceKeyPath' => $serviceKeyPath,
            $this->firebaseServiceAccountSettingKey => $serviceAccountJson,
        ])->save();

        Cache::forget($this->firebaseServiceAccountCacheKey);

        return $serviceKeyPath;
    }

    /**
     * Retrieve the firebase service account JSON content, preferring the
     * file on disk and falling back to the database-stored copy. Cached
     * so we don't hit disk/DB on every call.
     */
    public function getFirebaseServiceAccount(): ?string
    {
        return Cache::rememberForever($this->firebaseServiceAccountCacheKey, function () {
            $filePath = setting('serviceKeyPath', 'vault/firebase_service.json');
            $serviceAccountJson = Storage::get($filePath);

            if (empty($serviceAccountJson)) {
                $serviceAccountJson = setting($this->firebaseServiceAccountSettingKey);
            }

            return empty($serviceAccountJson) ? null : $serviceAccountJson;
        });
    }

    private function getFirebaseFactory()
    {
        try {
            $serviceAccount = $this->getFirebaseServiceAccount();

            if (empty($serviceAccount)) {
                throw new \Exception(__("Please setup firebase on backend"), 1);
            }
            return (new Factory)->withServiceAccount($serviceAccount);
        } catch (\Exception $ex) {
            throw new \Exception(__("Please setup firebase on backend"), 1);
        }
    }


    private function getFirebaseMessaging()
    {
        return $this->getFirebaseFactory()->createMessaging();
    }

    private function getFirebaseAuth()
    {
        return $this->getFirebaseFactory()->createAuth();
    }

    private function getFirebaseStore()
    {
        return $this->getFirebaseFactory()->createFirestore();
    }

    private function getFirebaseStoreClient(): FirestoreClient
    {
        $firebaseProjectId = setting('projectId', "");
        $firebaseApiKey = setting('apiKey', "");
        $firebaseDbName = setting('firestoreDatabase', '(default)');
        $client = new FirestoreClient($firebaseProjectId, $firebaseApiKey, [
            'database' => $firebaseDbName,
        ]);
        //set auth for client
        $this->firestoreClientAuth($client);
        return $client;
    }


    public function verifyFirebaseIDToken($idTokenString)
    {
        //
        $auth = $this->getFirebaseAuth();
        $verifiedIdToken = $auth->verifyIdToken($idTokenString);
        $uid = $verifiedIdToken->claims()->get('sub');
        return $auth->getUser($uid);
    }

    //firestore 
    public function firestoreClientAuth($firestoreClient)
    {

        $authToken = session('fbToken');
        $authTokenExpiry = session('fbTokenExpiry');

        if (empty($authToken) || empty($authTokenExpiry) || $authTokenExpiry < time()) {
            $uId = "user_id_" . \Auth::id() . "";
            $customToken = $this->getFirebaseAuth()->createCustomToken($uId);
            $signInResult = $this->getFirebaseAuth()->signInWithCustomToken($customToken);
            $authToken = $signInResult->idToken();
            session(['fbToken' => $authToken]);

            //refresh token every 60mins/1hr
            $authTokenExpiry = time() + 3600;
            session(['fbTokenExpiry' => $authTokenExpiry]);
        }

        $auth = new FirestoreAuthentication($firestoreClient);
        $auth->setCustomToken($authToken);
    }
}
