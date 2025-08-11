<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QrCodeController extends Controller
{
    public function index()
    {
        $parts = Part::all();
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

        Log::info('Generating QR codes for part: ' . $part->nama_part . ', quantity: ' . $quantity);

        $qrCodes = [];
        
        for ($i = 1; $i <= $quantity; $i++) {
            // Generate unique identifier for each QR code
            $uniqueId = $part->no_parts . '_' . time() . '_' . $i;
            
            // Create QR code data
            $qrData = [
                'part_id' => $part->id,
                'no_parts' => $part->no_parts,
                'nama_part' => $part->nama_part,
                'unique_id' => $uniqueId,
                'generated_at' => now()->toISOString()
            ];

            $qrCodes[] = [
                'data' => $qrData,
                'unique_id' => $uniqueId,
                'qr_text' => json_encode($qrData)
            ];
        }

        Log::info('Generated ' . count($qrCodes) . ' QR codes');

        return view('qrcode.result', compact('qrCodes', 'part', 'quantity'));
    }

    public function downloadAll(Request $request)
    {
        $request->validate([
            'qr_codes' => 'required|array',
            'qr_codes.*' => 'required|string'
        ]);

        $zip = new \ZipArchive();
        $zipName = 'qrcodes_' . time() . '.zip';
        $zipPath = storage_path('app/public/' . $zipName);

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($request->qr_codes as $qrData) {
                $data = json_decode($qrData, true);
                $filename = $data['unique_id'] . '.txt';
                $zip->addFromString($filename, $qrData);
            }
            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend();
        }

        return back()->with('error', 'Gagal membuat file ZIP');
    }
} 