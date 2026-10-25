<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OnboardingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('onboardings')->delete();

        \DB::table('onboardings')->insert(array(
            0 =>
            array(
                'id' => 1,
                'in_order' => 3,
                'title' => '{"en":"Order Anything, Anytime"}',
                'description' => '{"en":"Browse nearby restaurants, select your favorite meals, and get them delivered fast."}',
                'type' => 'customer',
                'is_active' => 1,
                'created_at' => '2022-08-15 20:30:25',
                'updated_at' => '2024-05-01 13:17:14',
            ),
            1 =>
            array(
                'id' => 2,
                'in_order' => 1,
                'title' => '{"en":"Rides Made Simple"}',
                'description' => '{"en":"Book a ride in seconds, track your driver in real time, and arrive safely."}',
                'type' => 'customer',
                'is_active' => 1,
                'created_at' => '2022-08-15 20:30:52',
                'updated_at' => '2024-05-01 13:17:14',
            ),
            2 =>
            array(
                'id' => 3,
                'in_order' => 2,
                'title' => '{"en":"Pay Your Way"}',
                'description' => '{"en":"Choose how you pay — cash, card, or wallet — it’s all secure and convenient."}',
                'type' => 'customer',
                'is_active' => 1,
                'created_at' => '2022-08-15 20:31:11',
                'updated_at' => '2024-05-01 13:17:14',
            ),
            3 =>
            array(
                'id' => 4,
                'in_order' => 1,
                'title' => '{"en":"Drive and Earn"}',
                'description' => '{"en":"Accept ride and delivery requests, and earn money on your schedule."}',
                'type' => 'driver',
                'is_active' => 1,
                'created_at' => '2022-08-15 20:35:14',
                'updated_at' => '2024-05-01 13:17:14',
            ),
            4 =>
            array(
                'id' => 5,
                'in_order' => 1,
                'title' => '{"en":"Stay in Control"}',
                'description' => '{"en":"Manage trips, track earnings, and get route guidance — all in one place."}',
                'type' => 'driver',
                'is_active' => 1,
                'created_at' => '2022-08-15 20:35:35',
                'updated_at' => '2024-05-01 13:17:14',
            ),
            5 =>
            array(
                'id' => 6,
                'in_order' => 1,
                'title' => '{"en":"Work Smart and Safe"}',
                'description' => '{"en":"Enjoy reliable support, live updates, and safe driving tools built for you."}',
                'type' => 'driver',
                'is_active' => 1,
                'created_at' => '2022-08-15 20:35:58',
                'updated_at' => '2024-05-01 13:17:14',
            ),
            6 =>
            array(
                'id' => 7,
                'in_order' => 1,
                'title' => '{"en":"Manage Orders Easily"}',
                'description' => '{"en":"Receive and process orders in real time — from kitchen to delivery."}',
                'type' => 'vendor',
                'is_active' => 1,
                'created_at' => '2022-08-15 20:36:53',
                'updated_at' => '2024-05-01 13:17:32',
            ),
            7 =>
            array(
                'id' => 8,
                'in_order' => 1,
                'title' => '{"en":"Update and Grow"}',
                'description' => '{"en":"Edit menus, change prices, and boost your sales with promotions."}',
                'type' => 'vendor',
                'is_active' => 1,
                'created_at' => '2022-08-15 20:37:12',
                'updated_at' => '2024-05-01 13:17:14',
            ),
            8 =>
            array(
                'id' => 9,
                'in_order' => 1,
                'title' => '{"en":"Track Performance"}',
                'description' => '{"en":"Monitor sales reports and delivery status to keep your business running smoothly."}',
                'type' => 'vendor',
                'is_active' => 1,
                'created_at' => '2022-08-15 20:37:27',
                'updated_at' => '2024-05-01 13:18:17',
            ),
        ));
    }
}
