<?php

namespace Database\Seeders;

use App\Models\CancellationPolicy;
use Illuminate\Database\Seeder;

class CancellationPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CancellationPolicy::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $policies = [
            [
                'name' => 'Flexible',
                'description' => 'Full refund if cancelled up to 24 hours before check-in. Cancellations within 24 hours are non-refundable.',
                'refund_percentage_rules' => [
                    ['days_before' => 1, 'refund_percentage' => 100],
                    ['days_before' => 0, 'refund_percentage' => 0],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Moderate',
                'description' => 'Full refund if cancelled 5+ days before check-in. 50% refund if cancelled 2-4 days before. No refund within 1 day of check-in.',
                'refund_percentage_rules' => [
                    ['days_before' => 5, 'refund_percentage' => 100],
                    ['days_before' => 2, 'refund_percentage' => 50],
                    ['days_before' => 0, 'refund_percentage' => 0],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Strict',
                'description' => 'Full refund if cancelled 14+ days before check-in. 50% refund if cancelled 7-13 days before. No refund within 7 days of check-in.',
                'refund_percentage_rules' => [
                    ['days_before' => 14, 'refund_percentage' => 100],
                    ['days_before' => 7, 'refund_percentage' => 50],
                    ['days_before' => 0, 'refund_percentage' => 0],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Super Strict (30 days)',
                'description' => 'Full refund if cancelled 30+ days before check-in. 50% refund if cancelled 14-29 days before. No refund within 14 days of check-in.',
                'refund_percentage_rules' => [
                    ['days_before' => 30, 'refund_percentage' => 100],
                    ['days_before' => 14, 'refund_percentage' => 50],
                    ['days_before' => 0, 'refund_percentage' => 0],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Super Strict (60 days)',
                'description' => 'Full refund if cancelled 60+ days before check-in. 50% refund if cancelled 30-59 days before. No refund within 30 days of check-in.',
                'refund_percentage_rules' => [
                    ['days_before' => 60, 'refund_percentage' => 100],
                    ['days_before' => 30, 'refund_percentage' => 50],
                    ['days_before' => 0, 'refund_percentage' => 0],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Non-Refundable',
                'description' => 'No refunds allowed for any cancellations. Payment is non-refundable regardless of cancellation timing.',
                'refund_percentage_rules' => [
                    ['days_before' => 0, 'refund_percentage' => 0],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Grace Period (48 hours)',
                'description' => 'Full refund if cancelled within 48 hours of booking AND booking is at least 14 days before check-in. Otherwise, standard 7-day policy applies.',
                'refund_percentage_rules' => [
                    ['days_before' => 14, 'refund_percentage' => 100],
                    ['days_before' => 7, 'refund_percentage' => 50],
                    ['days_before' => 0, 'refund_percentage' => 0],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Long Term Stay',
                'description' => 'Designed for stays of 28+ nights. Full refund if cancelled 28+ days before check-in. Partial refunds decrease closer to check-in.',
                'refund_percentage_rules' => [
                    ['days_before' => 28, 'refund_percentage' => 100],
                    ['days_before' => 14, 'refund_percentage' => 75],
                    ['days_before' => 7, 'refund_percentage' => 50],
                    ['days_before' => 3, 'refund_percentage' => 25],
                    ['days_before' => 0, 'refund_percentage' => 0],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Business Traveler',
                'description' => 'Flexible policy for business travelers. Full refund if cancelled up to 3 days before check-in. 50% refund within 3 days.',
                'refund_percentage_rules' => [
                    ['days_before' => 3, 'refund_percentage' => 100],
                    ['days_before' => 0, 'refund_percentage' => 50],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Premium Flexible',
                'description' => 'Most flexible option. Full refund if cancelled up to check-in time. 50% refund after check-in within 24 hours.',
                'refund_percentage_rules' => [
                    ['days_before' => 0, 'refund_percentage' => 100],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($policies as $policyData) {
            $policy = CancellationPolicy::create($policyData);

            $this->command->info("  ✓ Created cancellation policy: {$policy->name}");
        }

        $this->command->info('Cancellation policies seeded successfully!');
    }
}
