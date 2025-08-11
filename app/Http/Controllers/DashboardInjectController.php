<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use App\Models\User;
use App\Models\Part;
use App\Models\Checksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardInjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Authorization Check
        $isAuthorized = false;
        if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant'])) {
            $isAuthorized = true;
        } elseif ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'inject') {
            $isAuthorized = true;
        } elseif ($user->role === 'foreman') { // Ubah: tidak perlu cek bagian
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return redirect()->route('menu')->with('error', 'You do not have permission to access this page.');
        }

        // Jika foreman, hanya tampilkan user di grup-nya, mesin tetap semua
        if ($user->role === 'foreman') {
            $machines = Mesin::all();
            $users = User::where('grup_id', $user->grup_id)->get();
        } else {
            $machines = Mesin::all();
            $users = User::all();
        }
        
        // Count total machines
        $totalMesins = $machines->count();
        
        // Count machines by status
        $countAktif = $machines->where('status', 'aktif')->count();
        $countMaintenance = $machines->where('status', 'maintenance')->count();
        $countTidakAktif = $machines->where('status', 'tidak aktif')->count();
        
        // Get all parts for the form
        $parts = Part::all();
        
        // Initialize machine progress array
        $machineProgress = [];
        foreach ($machines as $mesin) {
            $machineProgress[$mesin->id] = 0; // Default progress
        }
        
        // Initialize checksheet details array
        $checksheetDetails = [];
        foreach ($machines as $mesin) {
            $checksheetDetails[$mesin->id] = [
                'maintenance' => $mesin->maintenance_count > 0,
                'offline' => $mesin->offline_count > 0
            ];
        }

        return view('dashboardinject.index', compact(
            'machines',
            'totalMesins',
            'countAktif',
            'countMaintenance',
            'countTidakAktif',
            'users',
            'parts',
            'machineProgress',
            'checksheetDetails'
        ));
    }

    /**
     * Store checksheet data from dashboard
     */
    public function storeChecksheet(Request $request)
    {
        Log::info('=== STORE CHECKSHEET METHOD CALLED ===');
        
        // Log user info for debugging
        $user = Auth::user();
        Log::info('User attempting to store checksheet', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->nama,
            'user_bagian' => $user->bagian ?? 'N/A'
        ]);

        // Log the incoming request for debugging
        Log::info('Checksheet store request received', [
            'data' => $request->all(),
            'files' => $request->allFiles(),
            'has_files' => $request->hasFile('foto_apd') || $request->hasFile('foto_instruksi_kerja') || $request->hasFile('foto_quality') || $request->hasFile('foto_limit_sampel'),
            'file_count' => count($request->allFiles()),
            'content_type' => $request->header('Content-Type')
        ]);

        // Validate the request
        $request->validate([
            'mesin_id' => 'required|exists:mesin,id',
            'user_id' => 'required|exists:users,id',
            'part_id' => 'required|exists:parts,id',
            'shift' => 'required|in:shift_1,shift_2,shift_3',
            'grup' => 'required|in:A,B,C',
            'apd_topi' => 'required|in:ya,tidak',
            'apd_sepatu' => 'required|in:ya,tidak',
            'quality_qpoint' => 'required|in:ya,tidak',
            'instruksi_kerja' => 'required|in:ya,tidak',
            'limit_sampel' => 'required|in:ya,tidak',
            'amptm_standar' => 'required|in:ya,tidak',
            'foto_apd' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'foto_instruksi_kerja' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'foto_quality' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'foto_limit_sampel' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'foto_amptm' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ], [
            'mesin_id.required' => 'ID Mesin harus dipilih',
            'mesin_id.exists' => 'Mesin yang dipilih tidak ditemukan',
            'user_id.required' => 'User harus dipilih',
            'user_id.exists' => 'User yang dipilih tidak ditemukan',
            'part_id.required' => 'Part harus dipilih',
            'part_id.exists' => 'Part yang dipilih tidak ditemukan',
            'shift.required' => 'Shift harus dipilih',
            'shift.in' => 'Shift harus salah satu dari: Shift 1 (07.00-15.00), Shift 2 (15.00-23.00), Shift 3 (23.00-07.00)',
            'grup.required' => 'Grup harus dipilih',
            'grup.in' => 'Grup harus salah satu dari: A, B, C',
            'apd_topi.required' => 'Silakan pilih apakah memakai topi atau tidak',
            'apd_topi.in' => 'Pilihan topi harus Ya atau Tidak',
            'apd_sepatu.required' => 'Silakan pilih apakah memakai sepatu safety atau tidak',
            'apd_sepatu.in' => 'Pilihan sepatu safety harus Ya atau Tidak',
            'quality_qpoint.required' => 'Silakan pilih apakah terdapat Q Point atau tidak',
            'quality_qpoint.in' => 'Pilihan Q Point harus Ya atau Tidak',
            'instruksi_kerja.required' => 'Silakan pilih apakah terdapat instruksi kerja atau tidak',
            'instruksi_kerja.in' => 'Pilihan instruksi kerja harus Ya atau Tidak',
            'limit_sampel.required' => 'Silakan pilih apakah terdapat limit sampel atau tidak',
            'limit_sampel.in' => 'Pilihan limit sampel harus Ya atau Tidak',
            'amptm_standar.required' => 'Silakan pilih apakah terdapat standar AMTPM atau tidak',
            'amptm_standar.in' => 'Pilihan standar AMTPM harus Ya atau Tidak',
            'foto_apd.file' => 'Foto APD harus berupa file',
            'foto_apd.mimes' => 'Foto APD harus berformat: jpeg, png, jpg',
            'foto_apd.max' => 'Foto APD maksimal 2MB',
            'foto_instruksi_kerja.file' => 'Foto Instruksi Kerja harus berupa file',
            'foto_instruksi_kerja.mimes' => 'Foto Instruksi Kerja harus berformat: jpeg, png, jpg',
            'foto_instruksi_kerja.max' => 'Foto Instruksi Kerja maksimal 2MB',
            'foto_quality.file' => 'Foto Quality harus berupa file',
            'foto_quality.mimes' => 'Foto Quality harus berformat: jpeg, png, jpg',
            'foto_quality.max' => 'Foto Quality maksimal 2MB',
            'foto_limit_sampel.file' => 'Foto Limit Sampel harus berupa file',
            'foto_limit_sampel.mimes' => 'Foto Limit Sampel harus berformat: jpeg, png, jpg',
            'foto_limit_sampel.max' => 'Foto Limit Sampel maksimal 2MB',
            'foto_amptm.file' => 'Foto Standar AMTPM harus berupa file',
            'foto_amptm.mimes' => 'Foto Standar AMTPM harus berformat: jpeg, png, jpg',
            'foto_amptm.max' => 'Foto Standar AMTPM maksimal 2MB',
        ]);

        try {
            DB::beginTransaction();

            // Check if machine exists and is active
            $mesin = Mesin::find($request->mesin_id);
            if (!$mesin) {
                throw new \Exception('Mesin tidak ditemukan');
            }

            // Check if user exists
            $user = User::find($request->user_id);
            if (!$user) {
                throw new \Exception('User tidak ditemukan');
            }

            // Check if part exists
            $part = Part::find($request->part_id);
            if (!$part) {
                throw new \Exception('Part tidak ditemukan');
            }

            // Mapping grup A/B/C ke integer 1/2/3
            $grupMap = ['A' => 1, 'B' => 2, 'C' => 3];
            $grupId = $grupMap[$request->grup] ?? null;

            // Create checksheet record
            $checksheetData = [
                'mesin_id' => $request->mesin_id,
                'user_id' => $request->user_id,
                'grup_id' => $grupId,
                'part_id' => $request->part_id,
                'shift' => $request->shift,
                'approval_status' => 'pending',
                'meja_id' => null, // For inject dashboard, meja_id is null
                'apd_topi' => $request->apd_topi,
                'apd_sepatu' => $request->apd_sepatu,
                'quality_qpoint' => $request->quality_qpoint,
                'instruksi_kerja' => $request->instruksi_kerja,
                'limit_sampel' => $request->limit_sampel,
                'amptm_standar' => $request->amptm_standar,
            ];

            Log::info('Creating checksheet with data', $checksheetData);

            $checksheet = Checksheet::create($checksheetData);

            Log::info('Checksheet created successfully', ['checksheet_id' => $checksheet->id]);

            // Handle photo uploads (from camera or file)
            $photoTypes = ['foto_apd', 'foto_instruksi_kerja', 'foto_quality', 'foto_limit_sampel', 'foto_amptm'];
            $uploadedPhotos = [];
            
            foreach ($photoTypes as $type) {
                Log::info("Processing photo type: {$type}", [
                    'has_file' => $request->hasFile($type),
                    'is_valid' => $request->hasFile($type) ? $request->file($type)->isValid() : false,
                    'file_size' => $request->hasFile($type) ? $request->file($type)->getSize() : 0,
                    'file_name' => $request->hasFile($type) ? $request->file($type)->getClientOriginalName() : null,
                    'file_mime' => $request->hasFile($type) ? $request->file($type)->getMimeType() : null,
                    'file_error' => $request->hasFile($type) ? $request->file($type)->getError() : null,
                    'file_error_message' => $request->hasFile($type) ? $request->file($type)->getErrorMessage() : null
                ]);
                
                if ($request->hasFile($type)) {
                    $file = $request->file($type);
                    
                    // Check if file is valid
                    if (!$file->isValid()) {
                        Log::warning("Invalid file for type: {$type}", [
                            'error' => $file->getError(),
                            'error_message' => $file->getErrorMessage()
                        ]);
                        continue;
                    }
                    
                    // Check file size
                    if ($file->getSize() > 2 * 1024 * 1024) {
                        Log::warning("File too large for type: {$type}", [
                            'size' => $file->getSize(),
                            'max_size' => 2 * 1024 * 1024
                        ]);
                        continue;
                    }
                    
                    // Check file type
                    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        Log::warning("Invalid file type for type: {$type}", [
                            'mime_type' => $file->getMimeType(),
                            'allowed_mimes' => $allowedMimes
                        ]);
                        continue;
                    }
                    
                    try {
                        // Generate unique filename
                        $timestamp = time();
                        $extension = $file->getClientOriginalExtension() ?: 'jpg';
                        $filename = "{$timestamp}_{$type}_{$checksheet->id}.{$extension}";
                        
                        // Ensure the directory exists
                        $directory = 'checksheet-photos';
                        if (!Storage::disk('public')->exists($directory)) {
                            Storage::disk('public')->makeDirectory($directory);
                            Log::info("Created directory: {$directory}");
                        }
                        
                        // Store the file
                        $path = $file->storeAs($directory, $filename, 'public');
                        
                        if (!$path) {
                            throw new \Exception("Failed to store file: {$filename}");
                        }
                        
                        Log::info("File stored successfully", [
                            'original_name' => $file->getClientOriginalName(),
                            'stored_path' => $path,
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType()
                        ]);
                        
                        // Create photo record in database
                        $photoData = [
                            'checksheet_id' => $checksheet->id,
                            'file_path' => $path,
                            'type' => str_replace('foto_', '', $type),
                            'description' => ucfirst(str_replace('foto_', '', $type)) . ' Photo - Captured via Camera'
                        ];
                        
                        Log::info("Creating photo record", $photoData);
                        
                        $photo = $checksheet->photos()->create($photoData);
                        
                        if (!$photo) {
                            throw new \Exception("Failed to create photo record for type: {$type}");
                        }
                        
                        $uploadedPhotos[] = $photo;
                        Log::info('Photo uploaded and saved to database successfully', [
                            'photo_id' => $photo->id, 
                            'type' => $type,
                            'filename' => $filename,
                            'path' => $path,
                            'database_id' => $photo->id
                        ]);
                        
                    } catch (\Exception $e) {
                        // Log the error but don't fail the entire transaction
                        Log::error('Failed to upload photo: ' . $e->getMessage(), [
                            'type' => $type,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                } else {
                    Log::info("No file provided for type: {$type}");
                }
            }

            DB::commit();

            Log::info('Checksheet transaction completed successfully', [
                'checksheet_id' => $checksheet->id,
                'photos_count' => count($uploadedPhotos),
                'uploaded_photos' => $uploadedPhotos,
                'total_photos_in_db' => $checksheet->photos()->count()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Checksheet berhasil disimpan dengan ' . count($uploadedPhotos) . ' foto',
                'data' => [
                    'checksheet' => $checksheet,
                    'photos_count' => count($uploadedPhotos),
                    'checksheet_id' => $checksheet->id,
                    'photos' => $uploadedPhotos,
                    'total_photos_in_db' => $checksheet->photos()->count()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            
            Log::error('Validation error in checksheet creation', ['errors' => $e->errors()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Checksheet creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Get machine details for modal
     */
    public function getMachineDetails($id)
    {
        $mesin = Mesin::findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $mesin->id,
                'nama_mesin' => $mesin->nama_mesin,
                'brand_mesin' => $mesin->brand_mesin,
                'tonase_mesin' => $mesin->tonase_mesin,
                'status' => $mesin->status,
            ]
        ]);
    }

    /**
     * Get recent checksheets for a machine
     */
    public function getMachineChecksheets($mesinId)
    {
        $checksheets = Checksheet::with(['user', 'part'])
            ->where('mesin_id', $mesinId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $checksheets
        ]);
    }
} 