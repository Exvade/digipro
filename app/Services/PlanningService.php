<?php

namespace App\Services;

use App\Models\PlanningProduksi;
use App\Models\PlanningProduksiJam;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PlanningService
{
    /**
     * Simpan data per jam
     */
    public function saveHourData($planningId, $validated)
    {
        // Hapus data jam lama jika ada
        PlanningProduksiJam::where('planning_id', $planningId)->delete();

        // SHIFT 1 (PAGI) - 07:00 sampai 14:00
        for ($i = 7; $i <= 14; $i++) {
            $key = 'pagi_jam_' . sprintf('%02d', $i);
            if (isset($validated[$key]) && $validated[$key] > 0) {
                PlanningProduksiJam::create([
                    'planning_id' => $planningId,
                    'shift' => 'pagi',
                    'jam' => sprintf('%02d:00:00', $i),
                    'target' => $validated[$key]
                ]);
            }
        }

        // SHIFT 2 (SIANG) - 15:00 sampai 22:00
        for ($i = 15; $i <= 22; $i++) {
            $key = 'siang_jam_' . sprintf('%02d', $i);
            if (isset($validated[$key]) && $validated[$key] > 0) {
                PlanningProduksiJam::create([
                    'planning_id' => $planningId,
                    'shift' => 'siang',
                    'jam' => sprintf('%02d:00:00', $i),
                    'target' => $validated[$key]
                ]);
            }
        }

        // SHIFT 3 (MALAM) - 23:00, 00:00 sampai 06:00
        foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i) {
            $key = 'malam_jam_' . sprintf('%02d', $i);
            if (isset($validated[$key]) && $validated[$key] > 0) {
                PlanningProduksiJam::create([
                    'planning_id' => $planningId,
                    'shift' => 'malam',
                    'jam' => sprintf('%02d:00:00', $i),
                    'target' => $validated[$key]
                ]);
            }
        }
    }

    /**
     * Ambil data per jam
     */
    public function getHourData($planningId)
    {
        $hourData = PlanningProduksiJam::where('planning_id', $planningId)->get();
        $dataPerJam = [];

        // SHIFT 1 (PAGI)
        for ($i = 7; $i <= 14; $i++) {
            $dataPerJam["pagi_jam_" . sprintf('%02d', $i)] = 0;
        }

        // SHIFT 2 (SIANG)
        for ($i = 15; $i <= 22; $i++) {
            $dataPerJam["siang_jam_" . sprintf('%02d', $i)] = 0;
        }

        // SHIFT 3 (MALAM)
        foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i) {
            $dataPerJam["malam_jam_" . sprintf('%02d', $i)] = 0;
        }

        foreach ($hourData as $data) {
            $hour = date('H', strtotime($data->jam));
            $shift = $data->shift;
            $dataPerJam[$shift . "_jam_" . sprintf('%02d', $hour)] = $data->target;
        }

        return $dataPerJam;
    }

    /**
     * Generate digital signature QR (Creator)
     */
    public function generateDigitalSignature(PlanningProduksi $planning)
    {
        try {
            $ttdUrl = asset('images/TTD.png');
            $writer = new PngWriter();
            $builder = new Builder($writer);

            $result = $builder->build(data: $ttdUrl, size: 200, margin: 5);

            $filename = 'planning_signatures/' . $planning->id . '_ttd_' . time() . '.png';
            $path = 'public/' . $filename;

            if (!Storage::exists('public/planning_signatures')) {
                Storage::makeDirectory('public/planning_signatures');
            }

            Storage::put($path, $result->getString());

            $planning->update([
                'signature_qr_code' => $filename,
                'signed_by' => 'Diar W',
                'signed_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate digital signature: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Approval QR (Approver)
     */
    public function generateApprovalQrCode(PlanningProduksi $planning, $approver)
    {
        try {
            $ttdUrl = asset('images/TTD.png');
            $writer = new PngWriter();
            $builder = new Builder($writer);

            $result = $builder->build(data: $ttdUrl, size: 200, margin: 5);

            $filename = 'planning_approvals/' . $planning->id . '_approved_' . time() . '.png';
            $path = 'public/' . $filename;

            if (!Storage::exists('public/planning_approvals')) {
                Storage::makeDirectory('public/planning_approvals');
            }

            Storage::put($path, $result->getString());

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to generate approval QR code: ' . $e->getMessage());
            return null;
        }
    }
}
