<?php

namespace App\Http\Livewire;

use Exception;
use GeoSot\EnvEditor\Facades\EnvEditor;

class MapSettingsLivewire extends BaseLivewireComponent
{

    // App settings
    public $useGoogleOnApp = true;
    public $googleMapKey;
    public $googleMapWebKey;
    public $what3wordsApiKey;
    public $opencageApiKey;
    public $mapBoxApiKey;
    public $radarApiKey;
    public $locationiqApiKey;
    public $placeFilterCountryCodes;
    public $geocoders = ["Google", "Mapbox", "Opencage", "Radar", "Locationiq"];
    public $geocoderType;
    //
    public $geoapifyApiKey;
    public $boundaryGeneratorSystem;
    public $geocoderThrottle = 30;
    public $geocoderCacheTtlSeconds = 21600;


    public function mount()
    {

        //
        if (!\App::environment('production')) {
            $this->googleMapKey = "XXXXXXXXXXXX";
            $this->googleMapWebKey = "XXXXXXXXXXXX";
            $this->what3wordsApiKey = "XXXXXXXXXXXX";
            $this->opencageApiKey = "XXXXXXXXXXXX";
            $this->radarApiKey = "XXXXXXXXXXXX";
            $this->locationiqApiKey = "XXXXXXXXXXXX";
            $this->geoapifyApiKey = "XXXXXXXXXXXX";
            $this->mapBoxApiKey = "XXXXXXXXXXXX";
        } else {
            $this->googleMapKey = $this->getEnvKey("googleMapKey");
            $this->googleMapWebKey = $this->getEnvKey("googleMapWebKey");
            $this->what3wordsApiKey = $this->getEnvKey('what3wordsApiKey');
            $this->opencageApiKey = $this->getEnvKey('opencageApiKey');
            $this->radarApiKey = $this->getEnvKey('radarApiKey');
            $this->locationiqApiKey = $this->getEnvKey('locationiqApiKey');
            $this->mapBoxApiKey = $this->getEnvKey('MAPBOX_API_KEY');
            $this->geoapifyApiKey = $this->getEnvKey('GEOAPIFY_API_KEY');
        }

        //
        $this->geocoderType = $this->getEnvKey('geocoderType');
        $this->useGoogleOnApp = $this->getEnvKey('useGoogleOnApp');
        $this->placeFilterCountryCodes = $this->getEnvKey('placeFilterCountryCodes');
        $this->boundaryGeneratorSystem = $this->getEnvKey('boundaryGeneratorSystem') ?? 'openmap';
        $this->geocoderThrottle = $this->getEnvKey('GEOCODER_THROTTLE') ?? 30;
        $this->geocoderCacheTtlSeconds = $this->getEnvKey('GEOCODER_CACHE_TTL_SECONDS') ?? 21600;
    }

    public function render()
    {
        return view('livewire.settings.map-settings');
    }


    public function saveAppSettings()
    {

        $this->validate([
            "googleMapKey" => "sometimes|nullable|string",
            "googleMapWebKey" => "sometimes|nullable|string",
            "what3wordsApiKey" => "sometimes|nullable|string",
            "opencageApiKey" => "sometimes|nullable|string",
            "radarApiKey" => "sometimes|nullable|string",
            "locationiqApiKey" => "sometimes|nullable|string",
            "locationiqApiKey" => "sometimes|nullable|string",
            "geocoderType" => "required",
            "boundaryGeneratorSystem" => "required|in:openmap,gadm,geoapify",
            "geocoderCacheTtlSeconds" => "sometimes|nullable|integer|min:0",
        ]);

        try {

            $this->isDemo();
            $appSettings = [
                'googleMapKey' => ($this->googleMapKey == "XXXXXXXXXXXX") ? setting('googleMapKey', 'XXXXXXXXXXXX') : $this->googleMapKey ?? "",
                'googleMapWebKey' => ($this->googleMapWebKey == "XXXXXXXXXXXX") ? setting('googleMapWebKey', 'XXXXXXXXXXXX') : $this->googleMapWebKey ?? "",
                'what3wordsApiKey' => $this->what3wordsApiKey ?? "",
                "map" => [
                    'opencageApiKey' => $this->opencageApiKey ?? "",
                    'opencageApiKey' => $this->opencageApiKey ?? "",
                    'radarApiKey' => $this->radarApiKey ?? "",
                    'locationiqApiKey' => $this->locationiqApiKey ?? "",
                    'geocoderType' => $this->geocoderType ?? "",
                    'useGoogleOnApp' => $this->useGoogleOnApp ?? "",
                    'placeFilterCountryCodes' => $this->placeFilterCountryCodes ?? "",
                    'boundaryGeneratorSystem' => $this->boundaryGeneratorSystem ?? "",
                ],
            ];

            // update the site name
            setting($appSettings)->save();

            //
            $this->setEnvKey(
                "googleMapKey",
                ($this->googleMapKey == "XXXXXXXXXXXX") ? setting('googleMapKey', 'XXXXXXXXXXXX') : $this->googleMapKey,
            );
            $this->setEnvKey(
                "googleMapWebKey",
                ($this->googleMapWebKey == "XXXXXXXXXXXX") ? setting('googleMapWebKey', 'XXXXXXXXXXXX') : $this->googleMapWebKey,
            );
            $this->setEnvKey('what3wordsApiKey', $this->what3wordsApiKey, );
            $this->setEnvKey('opencageApiKey', $this->opencageApiKey, );
            $this->setEnvKey('radarApiKey', $this->radarApiKey, );
            $this->setEnvKey('locationiqApiKey', $this->locationiqApiKey, );
            $this->setEnvKey('geocoderType', $this->geocoderType, );
            $this->setEnvKey('useGoogleOnApp', $this->useGoogleOnApp, );
            $this->setEnvKey('placeFilterCountryCodes', $this->placeFilterCountryCodes, );
            $this->setEnvKey('boundaryGeneratorSystem', $this->boundaryGeneratorSystem, );
            //
            $this->setEnvKey('GEOAPIFY_API_KEY', $this->geoapifyApiKey);
            $this->setEnvKey('MAPBOX_API_KEY', $this->mapBoxApiKey);
            $this->setEnvKey('GEOCODER_THROTTLE', $this->geocoderThrottle);
            $this->setEnvKey('GEOCODER_CACHE_TTL_SECONDS', $this->geocoderCacheTtlSeconds);





            $this->showSuccessAlert(__("Map Settings saved successfully!"));
            $this->emit("reloadPage");
        } catch (Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Map Settings save failed!"));
        }
    }



    public function setEnvKey($key, $value)
    {
        if (EnvEditor::keyExists($key)) {
            EnvEditor::editKey($key, $value);
        } else {
            EnvEditor::addKey($key, $value);
        }
    }

    public function getEnvKey($key)
    {
        return env($key);
    }
}