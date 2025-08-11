<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Downtime Categories
        $categories = [
            [
                'name' => 'Equipment Failure',
                'code' => 'EF',
                'description' => 'Kegagalan peralatan atau mesin',
                'type' => 'unplanned',
                'color' => '#dc3545',
                'sort_order' => 1
            ],
            [
                'name' => 'Setup & Adjustment',
                'code' => 'SA',
                'description' => 'Setup dan penyesuaian mesin',
                'type' => 'planned',
                'color' => '#ffc107',
                'sort_order' => 2
            ],
            [
                'name' => 'Material Shortage',
                'code' => 'MS',
                'description' => 'Kekurangan material',
                'type' => 'unplanned',
                'color' => '#fd7e14',
                'sort_order' => 3
            ],
            [
                'name' => 'Break Time',
                'code' => 'BT',
                'description' => 'Waktu istirahat',
                'type' => 'planned',
                'color' => '#6c757d',
                'sort_order' => 4
            ],
            [
                'name' => 'Changeover',
                'code' => 'CO',
                'description' => 'Perubahan produk',
                'type' => 'planned',
                'color' => '#17a2b8',
                'sort_order' => 5
            ],
            [
                'name' => 'Power Failure',
                'code' => 'PF',
                'description' => 'Kegagalan listrik',
                'type' => 'unplanned',
                'color' => '#6f42c1',
                'sort_order' => 6
            ]
        ];

        foreach ($categories as $category) {
            DB::table('downtime_categories')->insert($category);
        }

        // Seed Downtime Codes
        $codes = [
            // Equipment Failure
            ['downtime_category_id' => 1, 'name' => 'Mechanical Failure', 'code' => 'EF001', 'type' => 'unplanned', 'responsibility' => 'maintenance'],
            ['downtime_category_id' => 1, 'name' => 'Electrical Failure', 'code' => 'EF002', 'type' => 'unplanned', 'responsibility' => 'maintenance'],
            ['downtime_category_id' => 1, 'name' => 'Hydraulic Failure', 'code' => 'EF003', 'type' => 'unplanned', 'responsibility' => 'maintenance'],
            ['downtime_category_id' => 1, 'name' => 'Pneumatic Failure', 'code' => 'EF004', 'type' => 'unplanned', 'responsibility' => 'maintenance'],
            
            // Setup & Adjustment
            ['downtime_category_id' => 2, 'name' => 'Tool Change', 'code' => 'SA001', 'type' => 'planned', 'responsibility' => 'operator'],
            ['downtime_category_id' => 2, 'name' => 'Parameter Adjustment', 'code' => 'SA002', 'type' => 'planned', 'responsibility' => 'operator'],
            ['downtime_category_id' => 2, 'name' => 'Calibration', 'code' => 'SA003', 'type' => 'planned', 'responsibility' => 'maintenance'],
            
            // Material Shortage
            ['downtime_category_id' => 3, 'name' => 'Raw Material Shortage', 'code' => 'MS001', 'type' => 'unplanned', 'responsibility' => 'supervisor'],
            ['downtime_category_id' => 3, 'name' => 'Packaging Material Shortage', 'code' => 'MS002', 'type' => 'unplanned', 'responsibility' => 'supervisor'],
            ['downtime_category_id' => 3, 'name' => 'Component Shortage', 'code' => 'MS003', 'type' => 'unplanned', 'responsibility' => 'supervisor'],
            
            // Break Time
            ['downtime_category_id' => 4, 'name' => 'Lunch Break', 'code' => 'BT001', 'type' => 'planned', 'responsibility' => 'operator'],
            ['downtime_category_id' => 4, 'name' => 'Coffee Break', 'code' => 'BT002', 'type' => 'planned', 'responsibility' => 'operator'],
            
            // Changeover
            ['downtime_category_id' => 5, 'name' => 'Product Change', 'code' => 'CO001', 'type' => 'planned', 'responsibility' => 'operator'],
            ['downtime_category_id' => 5, 'name' => 'Color Change', 'code' => 'CO002', 'type' => 'planned', 'responsibility' => 'operator'],
            ['downtime_category_id' => 5, 'name' => 'Size Change', 'code' => 'CO003', 'type' => 'planned', 'responsibility' => 'operator'],
            
            // Power Failure
            ['downtime_category_id' => 6, 'name' => 'Power Outage', 'code' => 'PF001', 'type' => 'unplanned', 'responsibility' => 'external'],
            ['downtime_category_id' => 6, 'name' => 'Voltage Fluctuation', 'code' => 'PF002', 'type' => 'unplanned', 'responsibility' => 'external']
        ];

        foreach ($codes as $code) {
            DB::table('downtime_codes')->insert($code);
        }

        // Seed OEE Targets (assuming mesin with ID 1 and 2 exist)
        $targets = [
            [
                'mesin_id' => 1,
                'target_oee' => 85.00,
                'target_availability' => 90.00,
                'target_performance' => 95.00,
                'target_quality' => 99.50,
                'target_cycle_time' => 30,
                'max_downtime_per_day' => 60,
                'max_stops_per_day' => 5,
                'period_type' => 'daily',
                'effective_date' => now()->format('Y-m-d')
            ],
            [
                'mesin_id' => 2,
                'target_oee' => 82.00,
                'target_availability' => 88.00,
                'target_performance' => 93.00,
                'target_quality' => 99.00,
                'target_cycle_time' => 35,
                'max_downtime_per_day' => 75,
                'max_stops_per_day' => 6,
                'period_type' => 'daily',
                'effective_date' => now()->format('Y-m-d')
            ]
        ];

        foreach ($targets as $target) {
            DB::table('oee_targets')->insert($target);
        }

        // Seed sample OEE data for the last 30 days
        $startDate = now()->subDays(30);
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            // Mesin 1
            DB::table('oee_data')->insert([
                'mesin_id' => 1,
                'user_id' => 1,
                'tanggal' => $date->format('Y-m-d'),
                'shift' => 'shift_1',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'planned_production_time' => 480,
                'actual_running_time' => 432,
                'total_downtime' => 48,
                'availability' => 90.00,
                'ideal_cycle_time' => 30,
                'actual_cycle_time' => 32,
                'total_units_produced' => 810,
                'good_units_produced' => 805,
                'rejected_units' => 5,
                'performance' => 93.75,
                'quality' => 99.38,
                'oee' => 83.44,
                'stop_count' => rand(3, 8),
                'setup_time' => 15,
                'changeover_time' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Mesin 2
            DB::table('oee_data')->insert([
                'mesin_id' => 2,
                'user_id' => 1,
                'tanggal' => $date->format('Y-m-d'),
                'shift' => 'shift_1',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'planned_production_time' => 480,
                'actual_running_time' => 422,
                'total_downtime' => 58,
                'availability' => 87.92,
                'ideal_cycle_time' => 35,
                'actual_cycle_time' => 38,
                'total_units_produced' => 666,
                'good_units_produced' => 659,
                'rejected_units' => 7,
                'performance' => 92.11,
                'quality' => 98.95,
                'oee' => 80.00,
                'stop_count' => rand(4, 9),
                'setup_time' => 20,
                'changeover_time' => 15,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
} 