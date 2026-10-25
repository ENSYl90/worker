<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyReportReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            "Inaccurate description",
            "Scam or fraud",
            "Offensive content",
            "Host unavailable",
            "Safety concerns",
            "Cleanliness issues",
            "Other"
        ];

        foreach ($reasons as $reason) {
            \App\Models\PropertyReportReason::firstOrCreate(
                ['reason' => $reason],
                ['is_active' => true]
            );
        }
    }
}
