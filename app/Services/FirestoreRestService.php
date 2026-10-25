<?php

namespace App\Services;

use App\Traits\FirebaseAuthTrait;
use Illuminate\Support\Facades\Http;

class FirestoreRestService
{

    use FirebaseAuthTrait;
    public $authToken;

    public function refreshAuth($force = false)
    {
        $this->authToken = setting("serverFBAuthToken", "");
        $tokenExpiry = ((int) setting("serverFBAuthTokenExpiry", 0)) ?? 0;

        if ($force || $tokenExpiry < now()->milliseconds) {
            $uId = "web_server";
            $firebaseAuth = $this->getFirebaseAuth();
            $customToken = $firebaseAuth->createCustomToken($uId);
            $signInResult = $firebaseAuth->signInWithCustomToken($customToken);
            $this->authToken = $signInResult->idToken();
            $tokenExpiry = (now()->milliseconds + ($signInResult->ttl() ?? 0)) - 300000;

            $payload = [
                "serverFBAuthToken" => $this->authToken,
                "serverFBAuthTokenExpiry" => $tokenExpiry,
            ];
            setting($payload)->save();
        }
    }

    public function exipredDriverNewOrders()
    {
        $this->refreshAuth();

        $baseUrl = "https://firestore.googleapis.com/v1/projects/" . setting("projectId", "") . "/databases/(default)/documents/:runQuery";
        $response = Http::withToken($this->authToken)->post(
            $baseUrl,
            [
                'structuredQuery' => [
                    'orderBy' => [
                        'field' => [
                            'fieldPath' => 'exipres_at',
                        ],
                        'direction' => 'ASCENDING',
                    ],
                    'from' => [
                        0 => [
                            'collectionId' => 'driver_new_order',
                        ],
                    ],
                    'limit' => 20,
                ],
            ]
        );

        $drivers = [];
        if ($response->ok()) {
            $driversRawData = $response->json();
            foreach ($driversRawData as $driver) {
                if (empty($driver["document"])) {
                    continue;
                }

                try {
                    $docName = $driver["document"]["name"];
                    $docName = explode("/", $docName);
                    $docNameCount = count($docName);
                    $docName = $docName[$docNameCount - 1];
                    $currentTime = new \Carbon\Carbon($driver["readTime"]);

                    $drivers[] = $driverObject =  [
                        "id" => $docName,
                        "exipres_at" => $driver["document"]["fields"]["exipres_at"]["integerValue"],
                        "current_time" => $currentTime->timestamp * 1000,
                    ];
                } catch (\Exception $ex) {
                    logger("Driver details Error", [
                        "3",
                        $ex->getMessage(),
                        $driver["document"]
                    ]);
                }
            }
        } else {
            $errorCode = $response->json()[0]["error"]["code"];
            if ($errorCode == 401 || $errorCode == 403) {
                $this->refreshAuth(true);
            }
            logger("Error with drivers search", [$response->body()]);
        }

        return $drivers;
    }

    public function deleteDocument($path, $recursive = true)
    {
        $this->refreshAuth();

        $collectionIDs = $this->getCollections($path);
        if (empty($collectionIDs)) {
            $this->deleteDocumentData($path);
        } else {

            foreach ($collectionIDs as $collectionID) {
                $collectionPath = "{$path}/{$collectionID}";
                $this->deleteDocument($collectionPath);
            }
        }
    }

    public function getCollections($path): array
    {
        $projectId = setting("projectId", "");
        $baseUrl = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/{$path}/:listCollectionIds";
        $response = Http::withToken($this->authToken)->post($baseUrl, [
            "pageSize" => 20,
        ]);

        $collections = [];
        if ($response->ok()) {
            logger("collections", [$response->json()]);
            $collections = $response->json()["collectionIds"];
        } else {
            logger("collections fetch error", [$response->json()]);
            $errorCode = ($response->json()[0] ??  $response->json())["error"]["code"];
            if ($errorCode == 401 || $errorCode == 403) {
                $this->refreshAuth(true);
            }

            try {


                if ($response->json()[0]["error"]["status"] == "FAILED_PRECONDITION") {
                    logger("Please follow this link to create the required condition on your firebase", [$response->json()[0]["error"]["message"]]);
                } else {
                    logger("Error with drivers search", [$response->body()]);
                }
            } catch (\Exception $ex) {
                logger("Error with drivers search", [$response->body()]);
            }
        }

        return $collections;
    }

    public function deleteDocumentData($path)
    {
        $baseUrl = "https://firestore.googleapis.com/v1/projects/" . setting("projectId", "") . "/databases/(default)/documents/{$path}";
        $response = Http::withToken($this->authToken)->delete($baseUrl);

        if (!$response->ok()) {
            $errorCode = $response->json()[0]["error"]["code"];
            if ($errorCode == 401 || $errorCode == 403) {
                $this->refreshAuth(true);
            }
            logger("Error with deleting documents", [$response->body()]);
        }
    }

    public function whereWithinGeohash($lat, $lng, $radius = 10, $rejectedDriversCount = 0, $vehicleTypeId = null)
    {
        $limitItems = (int) setting('maxDriverOrderNotificationAtOnce', 1) + $rejectedDriversCount;
        $geoLocHash = app(DriverLocationGeohashService::class)->encodeForRadius((float) $lat, (float) $lng, (float) $radius);

        $minLocGeoHash = $geoLocHash;

        $this->refreshAuth();

        $vehicleTypeFilter = [];
        if (!empty($vehicleTypeId)) {
            $vehicleTypeFilter = [
                'fieldFilter' => [
                    'field' => [
                        'fieldPath' => 'vehicle_type_id',
                    ],
                    'op' => 'EQUAL',
                    'value' => [
                        'doubleValue' => $vehicleTypeId,
                    ],
                ],
            ];
        } else {
            $vehicleTypeFilter = [
                'compositeFilter' => [
                    'op' => 'OR',
                    'filters' => [
                        [
                            'unaryFilter' => [
                                'field' => [
                                    'fieldPath' => 'vehicle_type_id',
                                ],
                                'op' => 'IS_NULL',
                            ],
                        ],
                        [
                            'fieldFilter' => [
                                'field' => [
                                    'fieldPath' => 'vehicle_type_id',
                                ],
                                'op' => 'EQUAL',
                                'value' => [
                                    'doubleValue' => 0,
                                ],
                            ],
                        ],

                    ],
                ]
            ];
        }

        $baseUrl = "https://firestore.googleapis.com/v1/projects/" . setting("projectId", "") . "/databases/(default)/documents/:runQuery";
        $response = Http::withToken($this->authToken)->post(
            $baseUrl,
            $params = [
                'structuredQuery' => [
                    'startAt' => [
                        'values' => [
                            [
                                'stringValue' => $minLocGeoHash,
                            ],
                        ],
                        'before' => false,
                    ],
                    'where' => [
                        'compositeFilter' => [
                            'op' => 'AND',
                            'filters' => [
                                [
                                    'fieldFilter' => [
                                        'field' => [
                                            'fieldPath' => 'free',
                                        ],
                                        'op' => 'EQUAL',
                                        'value' => [
                                            'doubleValue' => 1,
                                        ],
                                    ],
                                ],
                                [
                                    'fieldFilter' => [
                                        'field' => [
                                            'fieldPath' => 'online',
                                        ],
                                        'op' => 'EQUAL',
                                        'value' => [
                                            'doubleValue' => 1,
                                        ],
                                    ],
                                ],
                                $vehicleTypeFilter
                            ],
                        ],
                    ],
                    'orderBy' => [
                        'field' => [
                            'fieldPath' => 'g.geohash',
                        ],
                        'direction' => 'ASCENDING',
                    ],
                    'from' => [
                        0 => [
                            'collectionId' => 'drivers',
                        ],
                    ],
                    'limit' => (int) $limitItems,
                ],
            ]
        );

        $drivers = [];
        if ($response->ok()) {
            $driversRawData = $response->json();
            foreach ($driversRawData as $driver) {
                if (empty($driver["document"])) {
                    continue;
                }

                try {

                    $drivers[] = [
                        "id" => $driver["document"]["fields"]["id"]["stringValue"] ?? $driver["document"]["fields"]["id"]["integerValue"],
                        "lat" => $driver["document"]["fields"]["lat"]["doubleValue"] ?? $driver['document']['fields']['coordinates']['geoPointValue']['latitude'],
                        "long" => $driver["document"]["fields"]["long"]["doubleValue"] ?? $driver['document']['fields']['coordinates']['geoPointValue']['longitude'],
                        "earth_distance" => $driver["document"]["fields"]["earth_distance"]["doubleValue"] ?? $driver["document"]["fields"]["earth_distance"]["integerValue"] ?? 0.0,
                    ];
                } catch (\Exception $ex) {
                    logger("Driver details Error", [
                        "1",
                        $ex->getMessage(),
                        $driver["document"]
                    ]);
                }
            }
        } else {
            $errorCode = $response->json()[0]["error"]["code"];
            if ($errorCode == 401 || $errorCode == 403) {
                $this->refreshAuth(true);
            }

            try {


                if ($response->json()[0]["error"]["status"] == "FAILED_PRECONDITION") {
                    logger("Please follow this link to create the required condition on your firebase", [$response->json()[0]["error"]["message"]]);

                    $error = "Please follow this link to create the required condition on your firebase \n" . $response->json()[0]["error"]["message"] . "";
                    throw new \Exception($error, 1);
                } else {
                    logger("Error with drivers search", [$response->body()]);
                }
            } catch (\Exception $ex) {
                logger("Error with drivers search", [$ex->getMessage() ?? $response->body()]);
                throw new \Exception($ex->getMessage() ?? $response->body(), 1);
            }
        }

        return $drivers;
    }


    function getBounds($latitude, $longitude, $radius)
    {
        $earthRadius = 6371;

        $lat = deg2rad($latitude);
        $lng = deg2rad($longitude);

        $maxLat = rad2deg(asin(sin($lat) * cos($radius / $earthRadius) +
            cos($lat) * sin($radius / $earthRadius)));

        $minLat = rad2deg(asin(sin($lat) * cos($radius / $earthRadius) -
            cos($lat) * sin($radius / $earthRadius)));

        $maxLng = rad2deg($lng + atan2(
            sin($radius / $earthRadius) * cos($lat),
            cos($radius / $earthRadius) - sin($lat) * sin(deg2rad($maxLat))
        ));

        $minLng = rad2deg($lng + atan2(
            sin($radius / $earthRadius) * cos($lat),
            cos($radius / $earthRadius) - sin($lat) * sin(deg2rad($minLat))
        ));

        $minLat = round($minLat, 9);
        $maxLat = round($maxLat, 9);
        $minLng = round($minLng, 9);
        $maxLng = round($maxLng, 9);

        return array(
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLng' => $minLng,
            'maxLng' => $maxLng
        );
    }

    function getGeohashPrecision($radiusInKm)
    {
        return app(DriverLocationGeohashService::class)->precisionFromRadiusKm((float) $radiusInKm);
    }
}
