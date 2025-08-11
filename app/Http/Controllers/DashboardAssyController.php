<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\User;
use App\Models\Part;
use App\Models\Checksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DashboardAssyController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Authorization Check
        $isAuthorized = false;
        if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant'])) {
            $isAuthorized = true;
        } elseif ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'assy') {
            $isAuthorized = true;
        } elseif ($user->role === 'foreman') { // Ubah: tidak perlu cek bagian
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return redirect()->route('menu')->with('error', 'You do not have permission to access this page.');
        }

        // Jika foreman, hanya tampilkan user di grup-nya, meja tetap semua
        if ($user->role === 'foreman') {
            $mejas = Meja::all();
            $users = User::where('grup_id', $user->grup_id)->get();
        } else {
            $mejas = Meja::all();
            $users = User::where('role', '!=', 'super_admin')->get();
        }
        $totalMejas = $mejas->count();
        $countAktif = $mejas->where('status', 'aktif')->count();
        $countMaintenance = $mejas->where('status', 'maintenance')->count();
        $countTidakAktif = $mejas->where('status', 'tidak_aktif')->count();
        $parts = Part::all();
        return view('dashboardasyy.index', compact(
            'mejas',
            'totalMejas',
            'countAktif',
            'countMaintenance',
            'countTidakAktif',
            'users',
            'parts'
        ));
    }

    /**
     * Store checksheet data from dashboard
     */
    public function storeChecksheet(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('Assembly Checksheet store request received', [
            'data' => $request->all(),
            'files' => $request->allFiles(),
            'has_files' => $request->hasFile('foto_apd') || $request->hasFile('foto_instruksi_kerja') || $request->hasFile('foto_quality') || $request->hasFile('foto_limit_sampel'),
            'file_count' => count($request->allFiles()),
            'content_type' => $request->header('Content-Type')
        ]);

        // Validate the request
        $request->validate([
            'meja_id' => 'required|exists:meja,id_meja',
            'user_id' => 'required|exists:users,id',
            'part_id' => 'required|exists:parts,id',
            'grup' => 'required|in:A,B,C',
            'shift' => 'required|in:shift_1,shift_2,shift_3',
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
            'meja_id.required' => 'ID Meja harus dipilih',
            'meja_id.exists' => 'Meja yang dipilih tidak valid atau tidak ditemukan di database.',
            'user_id.required' => 'User harus dipilih',
            'user_id.exists' => 'User yang dipilih tidak ditemukan',
            'part_id.required' => 'Part harus dipilih',
            'part_id.exists' => 'Part yang dipilih tidak ditemukan',
            'grup.required' => 'Grup harus dipilih',
            'grup.in' => 'Grup harus salah satu dari: A, B, C',
            'shift.required' => 'Shift harus dipilih',
            'shift.in' => 'Shift harus salah satu dari: Shift 1, Shift 2, Shift 3',
            'apd_topi.required' => 'Jawaban untuk topi harus dipilih',
            'apd_topi.in' => 'Jawaban untuk topi harus Ya atau Tidak',
            'apd_sepatu.required' => 'Jawaban untuk sepatu safety harus dipilih',
            'apd_sepatu.in' => 'Jawaban untuk sepatu safety harus Ya atau Tidak',
            'quality_qpoint.required' => 'Jawaban untuk Q Point harus dipilih',
            'quality_qpoint.in' => 'Jawaban untuk Q Point harus Ya atau Tidak',
            'instruksi_kerja.required' => 'Jawaban untuk Instruksi Kerja harus dipilih',
            'instruksi_kerja.in' => 'Jawaban untuk Instruksi Kerja harus Ya atau Tidak',
            'limit_sampel.required' => 'Jawaban untuk Limit Sampel harus dipilih',
            'limit_sampel.in' => 'Jawaban untuk Limit Sampel harus Ya atau Tidak',
            'amptm_standar.required' => 'Jawaban untuk Standar AMTPM harus dipilih',
            'amptm_standar.in' => 'Jawaban untuk Standar AMTPM harus Ya atau Tidak',
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

            // Check if meja exists and is active
            $meja = Meja::find($request->meja_id);
            if (!$meja) {
                throw new \Exception('Meja tidak ditemukan');
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

            // Create checksheet record
            $checksheetData = [
                'meja_id' => $request->meja_id,
                'user_id' => $request->user_id,
                'grup_id' => Auth::user()->grup_id,
                'part_id' => $request->part_id,
                'shift' => $request->shift,
                'grup' => $request->grup,
                'apd_topi' => $request->apd_topi,
                'apd_sepatu' => $request->apd_sepatu,
                'quality_qpoint' => $request->quality_qpoint,
                'instruksi_kerja' => $request->instruksi_kerja,
                'limit_sampel' => $request->limit_sampel,
                'amptm_standar' => $request->amptm_standar,
                'approval_status' => 'pending',
                'mesin_id' => null, // For assembly dashboard, mesin_id is null
            ];

            Log::info('Creating assembly checksheet with data', $checksheetData);

            $checksheet = Checksheet::create($checksheetData);

            Log::info('Assembly checksheet created successfully', ['checksheet_id' => $checksheet->id]);

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

            Log::info('Assembly checksheet transaction completed successfully', [
                'checksheet_id' => $checksheet->id,
                'photos_count' => count($uploadedPhotos),
                'uploaded_photos' => $uploadedPhotos,
                'total_photos_in_db' => $checksheet->photos()->count()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Checksheet assembly berhasil disimpan dengan ' . count($uploadedPhotos) . ' foto',
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
            
            Log::error('Validation error in assembly checksheet creation', ['errors' => $e->errors()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Assembly checksheet creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
} 