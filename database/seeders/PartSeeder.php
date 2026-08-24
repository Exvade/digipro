<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Part;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inputFileName = base_path('part_197_final_bruto_terisi.xlsx');
        
        if (!file_exists($inputFileName)) {
            $this->command->error("File variant not found: $inputFileName");
            return;
        }

        $spreadsheet = IOFactory::load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Remove header row
        $headers = array_shift($rows);

        $count = 0;
        foreach ($rows as $row) {
            if (empty($row[0])) continue;

            Part::updateOrCreate(
                ['no_parts' => trim($row[0])],
                [
                    'no_barang' => $row[1] ?? null,
                    'nama_part' => $row[2] ?? null,
                    'type'      => $row[3] ?? null,
                    'quantity'  => $row[4] ?? 0,
                    'bruto'     => $row[5] ?? 0,
                ]
            );
            $count++;
        }

        $this->command->info("Imported $count parts.");
    }
}
