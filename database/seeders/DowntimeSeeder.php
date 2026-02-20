<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DowntimeCategory;
use App\Models\DowntimeCode;

class DowntimeSeeder extends Seeder
{
    public function run(): void
    {
        // Create Downtime Categories
        $categories = [
            [
                'name' => 'Visual Defects',
                'code' => 'VIS',
                'description' => 'Defects yang dapat dilihat secara visual',
                'type' => 'quality',
                'color' => '#ffc107',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Dimensional Issues',
                'code' => 'DIM',
                'description' => 'Masalah dimensi produk',
                'type' => 'quality',
                'color' => '#fd7e14',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Functional Problems',
                'code' => 'FUN',
                'description' => 'Masalah fungsi produk',
                'type' => 'quality',
                'color' => '#dc3545',
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Material Issues',
                'code' => 'MAT',
                'description' => 'Masalah material',
                'type' => 'quality',
                'color' => '#6f42c1',
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Process Issues',
                'code' => 'PRO',
                'description' => 'Masalah proses produksi',
                'type' => 'process',
                'color' => '#20c997',
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'Equipment Problems',
                'code' => 'EQP',
                'description' => 'Masalah peralatan/mesin',
                'type' => 'equipment',
                'color' => '#17a2b8',
                'is_active' => true,
                'sort_order' => 6
            ]
        ];

        foreach ($categories as $categoryData) {
            $category = DowntimeCategory::create($categoryData);
            
            // Create specific codes for each category
            $this->createCodesForCategory($category);
        }
    }

    private function createCodesForCategory(DowntimeCategory $category)
    {
        $codes = [];

        switch ($category->code) {
            case 'VIS':
                $codes = [
                    ['name' => 'Scratches', 'code' => 'VIS001', 'description' => 'Goresan pada permukaan'],
                    ['name' => 'Discoloration', 'code' => 'VIS002', 'description' => 'Perubahan warna'],
                    ['name' => 'Surface Roughness', 'code' => 'VIS003', 'description' => 'Kekasaran permukaan'],
                    ['name' => 'Stains', 'code' => 'VIS004', 'description' => 'Noda pada produk'],
                    ['name' => 'Bubbles', 'code' => 'VIS005', 'description' => 'Gelembung udara'],
                ];
                break;

            case 'DIM':
                $codes = [
                    ['name' => 'Oversize', 'code' => 'DIM001', 'description' => 'Ukuran terlalu besar'],
                    ['name' => 'Undersize', 'code' => 'DIM002', 'description' => 'Ukuran terlalu kecil'],
                    ['name' => 'Out of Round', 'code' => 'DIM003', 'description' => 'Tidak bulat'],
                    ['name' => 'Warpage', 'code' => 'DIM004', 'description' => 'Melengkung/berubah bentuk'],
                    ['name' => 'Thickness Variation', 'code' => 'DIM005', 'description' => 'Variasi ketebalan'],
                ];
                break;

            case 'FUN':
                $codes = [
                    ['name' => 'Assembly Issues', 'code' => 'FUN001', 'description' => 'Masalah perakitan'],
                    ['name' => 'Fit Problems', 'code' => 'FUN002', 'description' => 'Tidak pas saat dipasang'],
                    ['name' => 'Performance Issues', 'code' => 'FUN003', 'description' => 'Masalah performa'],
                    ['name' => 'Durability Problems', 'code' => 'FUN004', 'description' => 'Masalah ketahanan'],
                    ['name' => 'Safety Issues', 'code' => 'FUN005', 'description' => 'Masalah keamanan'],
                ];
                break;

            case 'MAT':
                $codes = [
                    ['name' => 'Contamination', 'code' => 'MAT001', 'description' => 'Kontaminasi material'],
                    ['name' => 'Wrong Material', 'code' => 'MAT002', 'description' => 'Material salah'],
                    ['name' => 'Material Defects', 'code' => 'MAT003', 'description' => 'Cacat material'],
                    ['name' => 'Insufficient Material', 'code' => 'MAT004', 'description' => 'Material tidak cukup'],
                    ['name' => 'Expired Material', 'code' => 'MAT005', 'description' => 'Material expired'],
                ];
                break;

            case 'PRO':
                $codes = [
                    ['name' => 'Temperature Issues', 'code' => 'PRO001', 'description' => 'Masalah suhu'],
                    ['name' => 'Pressure Problems', 'code' => 'PRO002', 'description' => 'Masalah tekanan'],
                    ['name' => 'Cycle Time Issues', 'code' => 'PRO003', 'description' => 'Masalah waktu siklus'],
                    ['name' => 'Speed Problems', 'code' => 'PRO004', 'description' => 'Masalah kecepatan'],
                    ['name' => 'Setup Issues', 'code' => 'PRO005', 'description' => 'Masalah setup'],
                ];
                break;

            case 'EQP':
                $codes = [
                    ['name' => 'Machine Breakdown', 'code' => 'EQP001', 'description' => 'Mesin rusak'],
                    ['name' => 'Tool Wear', 'code' => 'EQP002', 'description' => 'Keausan tool'],
                    ['name' => 'Calibration Issues', 'code' => 'EQP003', 'description' => 'Masalah kalibrasi'],
                    ['name' => 'Maintenance Required', 'code' => 'EQP004', 'description' => 'Perlu maintenance'],
                    ['name' => 'Power Issues', 'code' => 'EQP005', 'description' => 'Masalah daya'],
                ];
                break;
        }

        foreach ($codes as $index => $codeData) {
            DowntimeCode::create([
                'downtime_category_id' => $category->id,
                'name' => $codeData['name'],
                'code' => $codeData['code'],
                'description' => $codeData['description'],
                'type' => $category->type,
                'responsibility' => $this->getResponsibility($category->type),
                'requires_approval' => $category->type === 'equipment',
                'is_active' => true,
                'sort_order' => $index + 1
            ]);
        }
    }

    private function getResponsibility($type)
    {
        switch ($type) {
            case 'quality':
                return 'QC';
            case 'process':
                return 'Production';
            case 'equipment':
                return 'Maintenance';
            default:
                return 'Production';
        }
    }
}
