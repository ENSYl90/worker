<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Services\BoundaryGeneratorService;
use Illuminate\Support\Facades\Log;

class MassGenerateBoundariesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mass:boundaries:generate {--fetch-states : Fetch and create states from GADM before processing} {--fetch-cities : Fetch and create cities from GADM before processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate boundaries for a country, its states, and cities using GADM data';

    /**
     * Execute the console command.
     */
    public function handle(BoundaryGeneratorService $boundaryGenerator)
    {
        $deleteFailedBoundaryCountries = false;
        $countries = Country::all();
        $totalCountries = $countries->count();
        $loopCount = 0;
        $deletedCountries = 0;
        $this->info("Found {$totalCountries} countries.");
        $this->table(
            [
                "Countries",
                "With Boundaries",
                "Without boundaries"
            ],
            [
                [
                    $totalCountries,
                    $countries->where('boundaries', '!=', null)->count(),
                    $countries->where('boundaries', null)->count()
                ]
            ]
        );
        //ask if to continue
        if (!$this->confirm('Do you want to continue?')) {
            return 0;
        }

        //ask to handle only ones without boundaries
        $handleOnlyWithoutBoundaries = $this->confirm('Do you want to handle only ones without boundaries?');
        if ($handleOnlyWithoutBoundaries) {
            $countries = $countries->where('boundaries', null);
            $totalCountries = $countries->count();
            $loopCount = 0;
        }


        //ask if to delete any country that fails to get boundaries
        $deleteFailedBoundaryCountries = $this->confirm('Do you want to delete any country that fails to get boundaries?');

        foreach ($countries as $country) {
            $this->info("Processing country: {$country->name}");
            $this->generateBoundaries($country, $boundaryGenerator, $deleteFailedBoundaryCountries, $deletedCountries);
            $this->info("Finished processing country: {$country->name}");
            $loopCount++;
            $this->info("Processed {$loopCount} out of {$totalCountries} countries.");
        }

        return 0;
    }


    public function generateBoundaries(
        Country $country,
        BoundaryGeneratorService $boundaryGenerator,
        bool $deleteFailedBoundaryCountries,
        int &$deletedCountries
    ) {
        $countryName = $country->name;
        $fetchStates = $this->option('fetch-states');
        $fetchCities = $this->option('fetch-cities');

        $this->info("Starting boundary generation for: {$countryName}");

        try {

            if ($country->wasRecentlyCreated) {
                $this->info("Created new country: {$countryName}");
            } else {
                $this->info("Found existing country: {$countryName}");
            }

            // 1. Generate Country Boundaries
            if (empty($country->boundaries)) {
                $this->info("Fetching country boundaries...");
                $countryBoundaries = $boundaryGenerator->getBoundariesFromGADM($country);

                if ($countryBoundaries) {
                    $country->name = ucwords($countryName);
                    $country->boundaries = $countryBoundaries;
                    $country->save();
                    $this->info("✅ Country boundaries updated.");
                } else {
                    $this->error("❌ Failed to fetch boundaries for country: {$countryName}");
                    if ($deleteFailedBoundaryCountries) {
                        $country->delete();
                        $deletedCountries++;
                        $this->info("❌ Deleted country: {$countryName}");
                    }
                }
            } else {
                $this->info("ℹ️ Country boundaries already exist. Skipping fetch.");
            }

            // 1.5 Fetch and Create States if requested
            if ($fetchStates) {
                $this->info("Fetching and creating states list from GADM...");
                $count = $boundaryGenerator->fetchAndCreateStates($country);
                $this->info("✅ Extracted and synced {$count} states.");
            }

            // 1.6 Fetch and Create Cities if requested
            if ($fetchCities) {
                $this->info("Fetching and creating cities list from GADM (this may take a moment)...");
                $count = $boundaryGenerator->fetchAndCreateCities($country);
                $this->info("✅ Extracted and synced {$count} cities.");
            }

            // 2. Process States (Chunking to avoid memory issues)
            $stateCount = $country->states()->count();

            if ($stateCount > 0) {
                $this->info("Processing {$stateCount} states (checking boundaries)...");
                $bar = $this->output->createProgressBar($stateCount);
                $bar->start();

                $country->states()->orderBy('id')->chunk(50, function ($states) use ($boundaryGenerator, $bar) {
                    foreach ($states as $state) {
                        try {
                            // Skip if already has boundaries
                            if (empty($state->boundaries)) {
                                $stateBoundaries = $boundaryGenerator->getBoundariesFromGADM($state);
                                if ($stateBoundaries) {
                                    $state->boundaries = $stateBoundaries;
                                    $state->save();
                                }
                                // Small delay to play nice with API if we are hitting it sequentially
                                usleep(200000); // 0.2s delay
                            }
                        } catch (\Exception $e) {
                            Log::error("Failed to process state {$state->name}: " . $e->getMessage());
                        }

                        // 3. Process Cities for this State (Chunking)
                        $cityCount = $state->cities()->count();
                        if ($cityCount > 0) {
                            $state->cities()->orderBy('id')->chunk(50, function ($cities) use ($boundaryGenerator) {
                                foreach ($cities as $city) {
                                    try {
                                        if (empty($city->boundaries)) {
                                            $cityBoundaries = $boundaryGenerator->getBoundariesFromGADM($city);
                                            if ($cityBoundaries) {
                                                $city->boundaries = $cityBoundaries;
                                                $city->save();
                                            }
                                            usleep(100000); // 0.1s delay
                                        }
                                    } catch (\Exception $e) {
                                        Log::error("Failed to process city {$city->name}: " . $e->getMessage());
                                    }
                                }
                            });
                        }

                        $bar->advance();
                    }
                });

                $bar->finish();
                $this->newLine();
                $this->info("✅ States and cities processed.");
            } else {
                $this->warn("No states found for {$countryName}. Skipping state/city boundary generation.");
            }

            return true;
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            Log::error($e);
            return false;
        }
    }
}
