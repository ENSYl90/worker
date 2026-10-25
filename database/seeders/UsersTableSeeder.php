<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('users')->delete();

        \DB::table('users')->insert(array(
            0 =>
                array(
                    'id' => 1,
                    'uuid' => \Str::uuid(),
                    'name' => 'Admin Account',
                    'email' => 'admin@demo.com',
                    'phone' => $this->randomPhone(),
                    'email_verified_at' => NULL,
                    'password' => bcrypt("password"),
                    'vendor_id' => NULL,
                    'remember_token' => NULL,
                    'created_at' => '2020-12-28 11:14:31',
                    'updated_at' => '2020-12-28 11:14:31',
                    'deleted_at' => NULL,
                    'is_active' => 1,
                ),
            1 =>
                array(
                    'id' => 2,
                    'uuid' => \Str::uuid(),
                    'name' => 'Manager Account',
                    'email' => 'manager@demo.com',
                    'phone' => $this->randomPhone(),
                    'email_verified_at' => NULL,
                    'password' => bcrypt("password"),
                    'vendor_id' => NULL,
                    'remember_token' => NULL,
                    'created_at' => '2021-01-08 12:15:16',
                    'updated_at' => '2021-03-09 10:49:29',
                    'deleted_at' => NULL,
                    'is_active' => 1,
                ),
            2 =>
                array(
                    'id' => 3,
                    'uuid' => \Str::uuid(),
                    'name' => 'Driver account',
                    'email' => 'driver@demo.com',
                    'phone' => $this->randomPhone(),
                    'email_verified_at' => NULL,
                    'password' => bcrypt("password"),
                    'vendor_id' => NULL,
                    'remember_token' => NULL,
                    'created_at' => '2021-01-08 12:15:16',
                    'updated_at' => '2021-01-08 20:33:00',
                    'deleted_at' => NULL,
                    'is_active' => 1,
                ),
            3 =>
                array(
                    'id' => 4,
                    'uuid' => \Str::uuid(),
                    'name' => 'Client Account',
                    'email' => 'client@demo.com',
                    'phone' => $this->randomPhone(),
                    'email_verified_at' => NULL,
                    'password' => bcrypt("password"),
                    'vendor_id' => NULL,
                    'remember_token' => NULL,
                    'created_at' => '2021-02-01 13:14:26',
                    'updated_at' => '2021-02-01 13:14:26',
                    'deleted_at' => NULL,
                    'is_active' => 1,
                ),
            4 =>
                array(
                    'id' => 5,
                    'uuid' => \Str::uuid(),
                    'name' => 'Manager Account Package Vendor',
                    'email' => 'manager1@demo.com',
                    'phone' => $this->randomPhone(),
                    'email_verified_at' => NULL,
                    'password' => bcrypt("password"),
                    'vendor_id' => NULL,
                    'remember_token' => NULL,
                    'created_at' => '2021-01-08 12:15:16',
                    'updated_at' => '2021-03-09 10:49:14',
                    'deleted_at' => NULL,
                    'is_active' => 1,
                ),
            5 =>
                array(
                    'id' => 6,
                    'uuid' => \Str::uuid(),
                    'name' => 'Taxi Driver',
                    'email' => 'taxi_driver@demo.com',
                    'phone' => $this->randomPhone(),
                    'email_verified_at' => NULL,
                    'password' => bcrypt("password"),
                    'vendor_id' => NULL,
                    'remember_token' => NULL,
                    'created_at' => '2021-01-08 12:15:16',
                    'updated_at' => '2021-03-09 10:49:14',
                    'deleted_at' => NULL,
                    'is_active' => 1,
                ),
            6 => array(
                'id' => 7,
                'uuid' => \Str::uuid(),
                'name' => 'Service Manager Account',
                'email' => 'manager2@demo.com',
                'phone' => $this->randomPhone(),
                'email_verified_at' => NULL,
                'password' => bcrypt("password"),
                'vendor_id' => NULL,
                'remember_token' => NULL,
                'created_at' => '2021-01-08 12:15:16',
                'updated_at' => '2021-03-09 10:49:14',
                'deleted_at' => NULL,
                'is_active' => 1,
            ),
            7 => array(
                'id' => 8,
                'uuid' => \Str::uuid(),
                'name' => 'Booking Manager Account',
                'email' => 'booking_manager@demo.com',
                'phone' => $this->randomPhone(),
                'email_verified_at' => NULL,
                'password' => bcrypt("password"),
                'vendor_id' => NULL,
                'remember_token' => NULL,
                'created_at' => '2021-01-08 12:15:16',
                'updated_at' => '2021-03-09 10:49:14',
                'deleted_at' => NULL,
                'is_active' => 1,
            ),
        ));

        //
        $users = User::get();
        foreach ($users as $user) {
            $user->code = \Str::random(8);
            $user->saveQuietly();
        }
    }


    private function randomPhone()
    {
        return $this->randomCountryCode() . rand(100000000, 999999999);
    }
    private function randomCountryCode()
    {
        return "+" . rand(1, 234);
    }
}
