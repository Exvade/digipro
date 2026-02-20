<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QrCodeController extends Controller
{
    public function index()
    {
        $parts = Part::all();
        Log::info('QR Code Index - Parts count: ' . $parts->count());
        return view('qrcode.index', compact('parts'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1|max:100'
        ]);

        $part = Part::findOrFail($request->part_id);
        $quantity = $request->quantity;

        Log::info('Generating QR codes for part', [
            'part_id' => $part->id,
            'no_parts' => $part->no_parts,
            'nama_part' => $part->nama_part,
            'quantity' => $quantity
        ]);

        $qrCodes = [];
        $timestamp = time();
        
        for ($i = 1; $i <= $quantity; $i++) {
            // Generate unique identifier for each QR code
            $uniqueId = $part->no_parts . '_' . $timestamp . '_' . str_pad($i, 3, '0', STR_PAD_LEFT);
            
            // Create QR code data - Make it more structured and consistent
            $qrData = [
                'type' => 'DIGIPRO_PART',
                'version' => '1.0',
                'part_id' => $part->id,
                'no_parts' => $part->no_parts,
                'nama_part' => $part->nama_part,
                'type_part' => $part->type ?? '',
                'quantity_part' => $part->quantity ?? '',
                'bruto' => $part->bruto ?? '',
                'unique_id' => $uniqueId,
                'sequence' => $i,
                'total_quantity' => $quantity,
                'generated_at' => Carbon::now()->toISOString(),
                'generated_by' => auth()->user()->nama ?? 'System'
            ];

            // Convert to JSON string for QR code
            $qrText = json_encode($qrData, JSON_UNESCAPED_UNICODE);

            $qrCodes[] = [
                'data' => $qrData,
                'unique_id' => $uniqueId,
                'qr_text' => $qrText,
                'sequence' => $i
            ];
        }

        Log::info('Generated QR codes successfully', [
            'count' => count($qrCodes),
            'first_unique_id' => $qrCodes[0]['unique_id'] ?? 'none'
        ]);

        // Debug: Check if variables are properly set
        if (!$part) {
            Log::error('Part not found');
            return back()->with('error', 'Part tidak ditemukan');
        }

        if (empty($qrCodes)) {
            Log::error('No QR codes generated');
            return back()->with('error', 'Gagal generate QR codes');
        }

        return view('qrcode.result', compact('qrCodes', 'part', 'quantity'));
    }

    public function downloadAll(Request $request)
    {
        try {
            $request->validate([
                'qr_codes' => 'required|array',
                'qr_codes.*' => 'required|string'
            ]);

            $zip = new \ZipArchive();
            $zipName = 'qrcodes_' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/public/' . $zipName);

            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($request->qr_codes as $index => $qrData) {
                    $data = json_decode($qrData, true);
                    $filename = ($data['unique_id'] ?? 'qrcode_' . ($index + 1)) . '.json';
                    $zip->addFromString($filename, $qrData);
                }
                $zip->close();

                Log::info('QR codes ZIP file created successfully: ' . $zipName);

                return response()->download($zipPath)->deleteFileAfterSend();
            }

            throw new \Exception('Failed to create ZIP file');

        } catch (\Exception $e) {
            Log::error('Error creating QR codes ZIP: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat file ZIP: ' . $e->getMessage());
        }
    }

    /**
     * Validate scanned QR code (optional endpoint for validation)
     */
    public function validateScan(Request $request)
    {
        try {
            $request->validate([
                'qr_data' => 'required|string'
            ]);

            $qrData = json_decode($request->qr_data, true);

            if (!$qrData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format'
                ]);
            }

            // Validate if it's our QR code
            if (!isset($qrData['type']) || $qrData['type'] !== 'DIGIPRO_PART') {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code bukan dari sistem DIGIPRO'
                ]);
            }

            // Validate part exists
            $part = Part::find($qrData['part_id']);
            if (!$part) {
                return response()->json([
                    'success' => false,
                    'message' => 'Part tidak ditemukan dalam sistem'
                ]);
            }

            // Return success with part data
            return response()->json([
                'success' => true,
                'message' => 'QR code valid',
                'data' => [
                    'qr_data' => $qrData,
                    'part' => $part,
                    'scanned_at' => Carbon::now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating QR scan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error memvalidasi QR code: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save quality check result (optional)
     */
    public function saveQualityCheck(Request $request)
    {
        try {
            $request->validate([
                'qr_data' => 'required|string',
                'quality' => 'required|in:good,ng,retry',
                'notes' => 'nullable|string'
            ]);

            $qrData = json_decode($request->qr_data, true);
            
            // Here you can save quality check result to database
            // For example, create a QualityCheck model and save the data
            
            Log::info('Quality check saved', [
                'unique_id' => $qrData['unique_id'] ?? 'unknown',
                'quality' => $request->quality,
                'notes' => $request->notes,
                'checked_by' => auth()->user()->nama ?? 'Unknown',
                'checked_at' => Carbon::now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quality check berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving quality check: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error menyimpan quality check: ' . $e->getMessage()
            ]);
        }
    }
}