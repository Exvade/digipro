<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Moldshop;
use Illuminate\Support\Facades\DB;

class MoldShopImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Adjust path as needed, or use absolute path provided by user context
        $csvFile = 'd:\laragon\www\DIGIPRO - Copy (2)\FORM PERBAIKAN MOLD - DATA PERBAIKAN.csv';

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found at: $csvFile");
            return;
        }

        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header row

        $count = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                // Ensure row has enough columns
                if (count($row) < 4) continue;

                $no_mold = trim($row[0]);
                $nama_mold = trim($row[1]);
                $type = trim($row[2]);
                $customer = trim($row[3]);

                if (empty($no_mold)) continue;

                Moldshop::updateOrCreate(
                    ['no_mold' => $no_mold],
                    [
                        'nama_mold' => $nama_mold,
                        'type' => $type,
                        'customer' => $customer,
                        'status' => 'OK' // Default status
                    ]
                );
                $count++;
            }
            DB::commit();
            $this->command->info("Successfully imported $count molds.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Failed to import: " . $e->getMessage());
        } finally {
            fclose($file);
        }
    }
}
