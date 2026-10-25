<?php

namespace App\Services;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BoundaryGeneratorService
{
    /**
     * Generate boundaries using GADM system for Country, State, or City models
     */
    public function getBoundariesFromGADM(Model $model): ?array
    {
        try {
            switch (get_class($model)) {
                case Country::class:
                    return $this->getCountryBoundariesFromGADM($model);
                case State::class:
                    return $this->getStateBoundariesFromGADM($model);
                case City::class:
                    return $this->getCityBoundariesFromGADM($model);
                default:
                    throw new \InvalidArgumentException('Unsupported model type: ' . get_class($model));
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch GADM boundaries for {$model->name}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get country boundaries from GADM (level 0)
     */
    private function getCountryBoundariesFromGADM(Country $country): ?array
    {
        $iso3Code = $this->getISO3CodeFromCountryName($country->name);

        if (!$iso3Code) {
            throw new \Exception("Could not find ISO3 code for country: {$country->name}");
        }

        $url = "https://geodata.ucdavis.edu/gadm/gadm4.1/json/gadm41_{$iso3Code}_0.json";

        $data = $this->fetchGADMData($url);

        if (!$data) {
            return null;
        }

        // For country level, get the first feature (main country boundary)
        $feature = $data['features'][0] ?? null;
        if (!$feature) {
            return null;
        }

        $geometry = $feature['geometry'] ?? [];
        return $this->processGADMCoordinates($geometry);
    }

    /**
     * Get state boundaries from GADM (level 1)
     */
    private function getStateBoundariesFromGADM(State $state): ?array
    {
        $country = $state->country;
        if (!$country) {
            throw new \Exception("State {$state->name} has no associated country");
        }

        $iso3Code = $this->getISO3CodeFromCountryName($country->name);

        if (!$iso3Code) {
            throw new \Exception("Could not find ISO3 code for country: {$country->name}");
        }

        $url = "https://geodata.ucdavis.edu/gadm/gadm4.1/json/gadm41_{$iso3Code}_1.json";

        $data = $this->fetchGADMData($url);

        if (!$data) {
            return null;
        }

        // Find the specific state in the GADM data
        return $this->findFeatureByName($data, $state->name, 'NAME_1');
    }

    /**
     * Get city boundaries from GADM (level 2 or 3)
     */
    private function getCityBoundariesFromGADM(City $city): ?array
    {
        $state = $city->state;
        if (!$state || !$state->country) {
            throw new \Exception("City {$city->name} has no associated state/country");
        }

        $iso3Code = $this->getISO3CodeFromCountryName($state->country->name);

        if (!$iso3Code) {
            throw new \Exception("Could not find ISO3 code for country: {$state->country->name}");
        }

        // Try level 2 first (usually districts/counties)
        $url = "https://geodata.ucdavis.edu/gadm/gadm4.1/json/gadm41_{$iso3Code}_2.json";
        $data = $this->fetchGADMData($url);

        if ($data) {
            $result = $this->findFeatureByName($data, $city->name, 'NAME_2');
            if ($result) {
                return $result;
            }
        }

        // Try level 3 if level 2 doesn't have the city
        $url = "https://geodata.ucdavis.edu/gadm/gadm4.1/json/gadm41_{$iso3Code}_3.json";
        $data = $this->fetchGADMData($url);

        if ($data) {
            return $this->findFeatureByName($data, $city->name, 'NAME_3');
        }

        return null;
    }

    /**
     * Fetch all states (level 1) from GADM for a country and create/update them in DB
     */
    public function fetchAndCreateStates(Country $country): int
    {
        $iso3Code = $this->getISO3CodeFromCountryName($country->name);

        if (!$iso3Code) {
            throw new \Exception("Could not find ISO3 code for country: {$country->name}");
        }

        $url = "https://geodata.ucdavis.edu/gadm/gadm4.1/json/gadm41_{$iso3Code}_1.json";

        $data = $this->fetchGADMData($url);

        if (!$data || !isset($data['features'])) {
            return 0;
        }

        $count = 0;
        foreach ($data['features'] as $feature) {
            $name = $feature['properties']['NAME_1'] ?? null;
            if ($name) {
                // Determine boundaries for this specific state
                // Note: The feature itself contains the geometry, so we can process it directly
                // instead of re-fetching.
                $geometry = $feature['geometry'] ?? [];
                $boundaries = $this->processGADMCoordinates($geometry);

                // Find or create the state
                $state = State::firstOrCreate(
                    [
                        'name' => $name,
                        'country_id' => $country->id
                    ],
                    [
                        'is_active' => 1
                    ]
                );

                // Update boundaries
                if (!empty($boundaries)) {
                    // Try to save as JSON, or array if cast handled by model
                    $state->boundaries = json_encode($boundaries);
                    $state->save();
                }

                $count++;
            }
        }

        return $count;
    }

    /**
     * Fetch all cities (level 2) from GADM for a country and create/update them in DB
     */
    public function fetchAndCreateCities(Country $country): int
    {
        $iso3Code = $this->getISO3CodeFromCountryName($country->name);

        if (!$iso3Code) {
            throw new \Exception("Could not find ISO3 code for country: {$country->name}");
        }

        // Try level 2 (Districts/Counties)
        $url = "https://geodata.ucdavis.edu/gadm/gadm4.1/json/gadm41_{$iso3Code}_2.json";
        $data = $this->fetchGADMData($url);

        if (!$data || !isset($data['features'])) {
            return 0;
        }

        // Pre-fetch states to minimize DB queries
        // Normalize names to lowercase for comparison if needed, but direct matching is faster
        $states = State::where('country_id', $country->id)->get()->keyBy(function ($item) {
            return strtolower($item->name);
        });

        $count = 0;
        foreach ($data['features'] as $feature) {
            $stateName = $feature['properties']['NAME_1'] ?? null;
            $cityName = $feature['properties']['NAME_2'] ?? null;

            if ($stateName && $cityName) {
                // Find the state
                $state = $states->get(strtolower($stateName));

                if ($state) {
                    $geometry = $feature['geometry'] ?? [];
                    $boundaries = $this->processGADMCoordinates($geometry);

                    // Find or create the city
                    $city = City::firstOrCreate(
                        [
                            'name' => $cityName,
                            'state_id' => $state->id
                        ],
                        [
                            'is_active' => 1
                        ]
                    );

                    // Update boundaries
                    if (!empty($boundaries)) {
                        $city->boundaries = json_encode($boundaries);
                        $city->save();
                    }

                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Fetch and parse GADM JSON data
     */
    private function fetchGADMData(string $url): ?array
    {
        Log::info("Fetching GADM data from: {$url}");

        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch GADM data: " . $response->body());
        }

        $data = $response->json();

        if (!isset($data['features']) || empty($data['features'])) {
            return null;
        }


        return $data;
    }

    /**
     * Find a specific feature by name in GADM data
     */
    private function findFeatureByName(array $data, string $name, string $nameField): ?array
    {
        foreach ($data['features'] as $feature) {
            $properties = $feature['properties'] ?? [];

            if (
                isset($properties[$nameField]) &&
                strcasecmp($properties[$nameField], $name) === 0
            ) {

                $geometry = $feature['geometry'] ?? [];
                $coordinates = $geometry['coordinates'] ?? [];

                if (!empty($coordinates)) {
                    return $this->processGADMCoordinates($geometry);
                }
            }
        }

        return null;
    }

    /**
     * Process GADM coordinates based on geometry type
     */
    private function processGADMCoordinates(array $geometry): array
    {
        $coordinates = $geometry['coordinates'] ?? [];
        $type = $geometry['type'] ?? '';

        switch ($type) {
            case 'Polygon':
                // Return the exterior ring (first array)
                return $coordinates[0] ?? [];

            case 'MultiPolygon':
                // Return the largest polygon's exterior ring
                $largestPolygon = [];
                $maxPoints = 0;

                foreach ($coordinates as $polygon) {
                    $exteriorRing = $polygon[0] ?? [];
                    if (count($exteriorRing) > $maxPoints) {
                        $maxPoints = count($exteriorRing);
                        $largestPolygon = $exteriorRing;
                    }
                }

                return $largestPolygon;

            default:
                return [];
        }
    }

    /**
     * Get ISO3 country code from country name using OpenStreetMap Nominatim API
     */
    private function getISO3CodeFromCountryName(string $countryName): ?string
    {
        try {
            $url = 'https://nominatim.openstreetmap.org/search';

            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'BoundaryGeneratorService/1.0'
                ])
                ->get($url, [
                    'q' => $countryName,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'limit' => 1,
                    'countrycodes' => '',
                    'featureType' => 'country'
                ]);

            if (!$response->successful()) {
                Log::warning("Nominatim API failed for country: {$countryName}");
                return $this->getFallbackISO3Code($countryName);
            }

            $data = $response->json();

            if (empty($data)) {
                Log::warning("No results found for country: {$countryName}");
                return $this->getFallbackISO3Code($countryName);
            }

            $result = $data[0];
            $address = $result['address'] ?? [];

            // Try to get ISO3166-1:alpha3 from the address details
            if (isset($address['country_code'])) {
                $iso2Code = strtoupper($address['country_code']);
                return $this->convertISO2ToISO3($iso2Code);
            }

            Log::warning("No country code found for: {$countryName}");
            return $this->getFallbackISO3Code($countryName);
        } catch (\Exception $e) {
            Log::error("Error fetching country code for {$countryName}: " . $e->getMessage());
            return $this->getFallbackISO3Code($countryName);
        }
    }

    /**
     * Convert ISO2 country code to ISO3
     */
    private function convertISO2ToISO3(string $iso2Code): ?string
    {
        $iso2ToISO3 = [
            'AD' => 'AND',
            'AE' => 'ARE',
            'AF' => 'AFG',
            'AG' => 'ATG',
            'AI' => 'AIA',
            'AL' => 'ALB',
            'AM' => 'ARM',
            'AO' => 'AGO',
            'AQ' => 'ATA',
            'AR' => 'ARG',
            'AS' => 'ASM',
            'AT' => 'AUT',
            'AU' => 'AUS',
            'AW' => 'ABW',
            'AX' => 'ALA',
            'AZ' => 'AZE',
            'BA' => 'BIH',
            'BB' => 'BRB',
            'BD' => 'BGD',
            'BE' => 'BEL',
            'BF' => 'BFA',
            'BG' => 'BGR',
            'BH' => 'BHR',
            'BI' => 'BDI',
            'BJ' => 'BEN',
            'BL' => 'BLM',
            'BM' => 'BMU',
            'BN' => 'BRN',
            'BO' => 'BOL',
            'BQ' => 'BES',
            'BR' => 'BRA',
            'BS' => 'BHS',
            'BT' => 'BTN',
            'BV' => 'BVT',
            'BW' => 'BWA',
            'BY' => 'BLR',
            'BZ' => 'BLZ',
            'CA' => 'CAN',
            'CC' => 'CCK',
            'CD' => 'COD',
            'CF' => 'CAF',
            'CG' => 'COG',
            'CH' => 'CHE',
            'CI' => 'CIV',
            'CK' => 'COK',
            'CL' => 'CHL',
            'CM' => 'CMR',
            'CN' => 'CHN',
            'CO' => 'COL',
            'CR' => 'CRI',
            'CU' => 'CUB',
            'CV' => 'CPV',
            'CW' => 'CUW',
            'CX' => 'CXR',
            'CY' => 'CYP',
            'CZ' => 'CZE',
            'DE' => 'DEU',
            'DJ' => 'DJI',
            'DK' => 'DNK',
            'DM' => 'DMA',
            'DO' => 'DOM',
            'DZ' => 'DZA',
            'EC' => 'ECU',
            'EE' => 'EST',
            'EG' => 'EGY',
            'EH' => 'ESH',
            'ER' => 'ERI',
            'ES' => 'ESP',
            'ET' => 'ETH',
            'FI' => 'FIN',
            'FJ' => 'FJI',
            'FK' => 'FLK',
            'FM' => 'FSM',
            'FO' => 'FRO',
            'FR' => 'FRA',
            'GA' => 'GAB',
            'GB' => 'GBR',
            'GD' => 'GRD',
            'GE' => 'GEO',
            'GF' => 'GUF',
            'GG' => 'GGY',
            'GH' => 'GHA',
            'GI' => 'GIB',
            'GL' => 'GRL',
            'GM' => 'GMB',
            'GN' => 'GIN',
            'GP' => 'GLP',
            'GQ' => 'GNQ',
            'GR' => 'GRC',
            'GS' => 'SGS',
            'GT' => 'GTM',
            'GU' => 'GUM',
            'GW' => 'GNB',
            'GY' => 'GUY',
            'HK' => 'HKG',
            'HM' => 'HMD',
            'HN' => 'HND',
            'HR' => 'HRV',
            'HT' => 'HTI',
            'HU' => 'HUN',
            'ID' => 'IDN',
            'IE' => 'IRL',
            'IL' => 'ISR',
            'IM' => 'IMN',
            'IN' => 'IND',
            'IO' => 'IOT',
            'IQ' => 'IRQ',
            'IR' => 'IRN',
            'IS' => 'ISL',
            'IT' => 'ITA',
            'JE' => 'JEY',
            'JM' => 'JAM',
            'JO' => 'JOR',
            'JP' => 'JPN',
            'KE' => 'KEN',
            'KG' => 'KGZ',
            'KH' => 'KHM',
            'KI' => 'KIR',
            'KM' => 'COM',
            'KN' => 'KNA',
            'KP' => 'PRK',
            'KR' => 'KOR',
            'KW' => 'KWT',
            'KY' => 'CYM',
            'KZ' => 'KAZ',
            'LA' => 'LAO',
            'LB' => 'LBN',
            'LC' => 'LCA',
            'LI' => 'LIE',
            'LK' => 'LKA',
            'LR' => 'LBR',
            'LS' => 'LSO',
            'LT' => 'LTU',
            'LU' => 'LUX',
            'LV' => 'LVA',
            'LY' => 'LBY',
            'MA' => 'MAR',
            'MC' => 'MCO',
            'MD' => 'MDA',
            'ME' => 'MNE',
            'MF' => 'MAF',
            'MG' => 'MDG',
            'MH' => 'MHL',
            'MK' => 'MKD',
            'ML' => 'MLI',
            'MM' => 'MMR',
            'MN' => 'MNG',
            'MO' => 'MAC',
            'MP' => 'MNP',
            'MQ' => 'MTQ',
            'MR' => 'MRT',
            'MS' => 'MSR',
            'MT' => 'MLT',
            'MU' => 'MUS',
            'MV' => 'MDV',
            'MW' => 'MWI',
            'MX' => 'MEX',
            'MY' => 'MYS',
            'MZ' => 'MOZ',
            'NA' => 'NAM',
            'NC' => 'NCL',
            'NE' => 'NER',
            'NF' => 'NFK',
            'NG' => 'NGA',
            'NI' => 'NIC',
            'NL' => 'NLD',
            'NO' => 'NOR',
            'NP' => 'NPL',
            'NR' => 'NRU',
            'NU' => 'NIU',
            'NZ' => 'NZL',
            'OM' => 'OMN',
            'PA' => 'PAN',
            'PE' => 'PER',
            'PF' => 'PYF',
            'PG' => 'PNG',
            'PH' => 'PHL',
            'PK' => 'PAK',
            'PL' => 'POL',
            'PM' => 'SPM',
            'PN' => 'PCN',
            'PR' => 'PRI',
            'PS' => 'PSE',
            'PT' => 'PRT',
            'PW' => 'PLW',
            'PY' => 'PRY',
            'QA' => 'QAT',
            'RE' => 'REU',
            'RO' => 'ROU',
            'RS' => 'SRB',
            'RU' => 'RUS',
            'RW' => 'RWA',
            'SA' => 'SAU',
            'SB' => 'SLB',
            'SC' => 'SYC',
            'SD' => 'SDN',
            'SE' => 'SWE',
            'SG' => 'SGP',
            'SH' => 'SHN',
            'SI' => 'SVN',
            'SJ' => 'SJM',
            'SK' => 'SVK',
            'SL' => 'SLE',
            'SM' => 'SMR',
            'SN' => 'SEN',
            'SO' => 'SOM',
            'SR' => 'SUR',
            'SS' => 'SSD',
            'ST' => 'STP',
            'SV' => 'SLV',
            'SX' => 'SXM',
            'SY' => 'SYR',
            'SZ' => 'SWZ',
            'TC' => 'TCA',
            'TD' => 'TCD',
            'TF' => 'ATF',
            'TG' => 'TGO',
            'TH' => 'THA',
            'TJ' => 'TJK',
            'TK' => 'TKL',
            'TL' => 'TLS',
            'TM' => 'TKM',
            'TN' => 'TUN',
            'TO' => 'TON',
            'TR' => 'TUR',
            'TT' => 'TTO',
            'TV' => 'TUV',
            'TW' => 'TWN',
            'TZ' => 'TZA',
            'UA' => 'UKR',
            'UG' => 'UGA',
            'UM' => 'UMI',
            'US' => 'USA',
            'UY' => 'URY',
            'UZ' => 'UZB',
            'VA' => 'VAT',
            'VC' => 'VCT',
            'VE' => 'VEN',
            'VG' => 'VGB',
            'VI' => 'VIR',
            'VN' => 'VNM',
            'VU' => 'VUT',
            'WF' => 'WLF',
            'WS' => 'WSM',
            'YE' => 'YEM',
            'YT' => 'MYT',
            'ZA' => 'ZAF',
            'ZM' => 'ZMB',
            'ZW' => 'ZWE'
        ];

        return $iso2ToISO3[$iso2Code] ?? null;
    }

    /**
     * Fallback method for getting ISO3 code when API fails
     */
    private function getFallbackISO3Code(string $countryName): ?string
    {
        $commonCountries = [
            'United States' => 'USA',
            'United Kingdom' => 'GBR',
            'Canada' => 'CAN',
            'Australia' => 'AUS',
            'Germany' => 'DEU',
            'France' => 'FRA',
            'Italy' => 'ITA',
            'Spain' => 'ESP',
            'Brazil' => 'BRA',
            'Mexico' => 'MEX',
            'India' => 'IND',
            'China' => 'CHN',
            'Japan' => 'JPN',
            'South Korea' => 'KOR',
            'Nigeria' => 'NGA',
            'South Africa' => 'ZAF'
        ];

        return $commonCountries[$countryName] ?? null;
    }
}