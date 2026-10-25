<?php

namespace App\Upgrades;



class Upgrade90 extends BaseUpgrade
{

    public $versionName = "1.8.20";
    //Runs or migrations to be done on this version
    public function run()
    {

        //add API_CACHE_ENABLED=true to .env file
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        if (!str_contains($envContent, 'API_CACHE_ENABLED')) {
            file_put_contents($envPath, $envContent . "\nAPI_CACHE_ENABLED=true\n");
        }



    }
}
