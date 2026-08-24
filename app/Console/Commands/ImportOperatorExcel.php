<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportOperatorExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:operator-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import operator data from Karyawan_Inject_Operator.xlsx';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path('Karyawan_Inject_Operator.xlsx');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: " . $filePath);
            return 1;
        }

        $this->info("Loading Excel file...");
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Skip header
        $headers = array_shift($rows);
        $this->info("Headers: " . implode(", ", $headers));
        
        $countInserted = 0;
        $countUpdated = 0;
        
        foreach ($rows as $index => $row) {
            // As per image columns:
            // 0: NIK
            // 1: Username
            // 2: Departemen
            // 3: Nama Lengkap
            // 4: Email
            // 5: Role
            // 6: Bagian
            // 7: Grup
            // 8: Password
            
            $nik = isset($row[0]) ? trim($row[0]) : null;
            if (empty($nik)) continue;
            
            $username = isset($row[1]) ? trim($row[1]) : null;
            // User request: Departemen diganti jadi Produksi
            $departemen = 'Produksi';
            $nama = isset($row[3]) ? trim($row[3]) : 'Unknown';
            $email = isset($row[4]) ? trim($row[4]) : strtolower($username) . '@company.local';
            
            // Set role to 'operator' explicitly based on user request
            $role = 'operator'; 
            
            $bagian = isset($row[6]) ? trim($row[6]) : 'inject';
            // lowercase bagian normally
            if (strtolower($bagian) == 'inject' || strtolower($bagian) == 'assy') {
                $bagian = strtolower($bagian);
            } else {
                $bagian = 'inject';
            }
            
            // Distribusikan grup secara merata (round-robin) ke grup yang ada
            // ID Grup A, B, C biasanya 1, 2, 3. Kita fetch dari DB.
            static $availableGroups = null;
            static $groupIndex = 0;
            if ($availableGroups === null) {
                // Ambil semua grup yang ada di database (bukan membuat baru)
                $availableGroups = \App\Models\Grup::orderBy('nama_grup')->pluck('id')->toArray();
            }

            $grup_id = null;
            if (count($availableGroups) > 0) {
                $grup_id = $availableGroups[$groupIndex % count($availableGroups)];
                $groupIndex++;
            }
            
            $password = isset($row[8]) ? trim($row[8]) : 'Password123!';

            $user = User::where('nik', $nik)->first();
            if ($user) {
                // Update specific fields without overwriting everything, but make sure role is updated to operator
                $user->update([
                    'role' => $role,
                    'bagian' => $bagian,
                    'departemen' => $departemen,
                    'grup_id' => $grup_id,
                ]);
                $countUpdated++;
            } else {
                // Insert new user
                User::create([
                    'nik' => $nik,
                    'username' => $username ?: $nik,
                    'departemen' => $departemen,
                    'nama' => $nama,
                    'email' => $email,
                    'role' => $role,
                    'bagian' => $bagian,
                    'grup_id' => $grup_id,
                    'password' => Hash::make($password),
                ]);
                $countInserted++;
            }
        }

        $this->info("Done! Inserted $countInserted new users. Updated user roles to operator for $countUpdated existing users.");
        return 0;
    }
}
