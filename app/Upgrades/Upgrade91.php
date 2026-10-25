<?php

namespace App\Upgrades;



class Upgrade91 extends BaseUpgrade
{

    public $versionName = "1.8.21";
    //Runs or migrations to be done on this version
    public function run()
    {

        //add USE_CACHE_VALIDATE_FCM=true to .env file
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        if (!str_contains($envContent, 'USE_CACHE_VALIDATE_FCM')) {
            file_put_contents($envPath, $envContent . "\nUSE_CACHE_VALIDATE_FCM=true\n");
        }

        //



    }
}
