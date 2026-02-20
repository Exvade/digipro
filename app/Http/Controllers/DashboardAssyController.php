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
        if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff'])) {
            $isAuthorized = true;
        } elseif ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'assy') {
            $isAuthorized = true;
        } elseif ($user->role === 'foreman') {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return redirect()->route('menu')->with('error', 'You do not have permission to access this page.');
        }

        // Filter data user sesuai role
        if ($user->role === 'foreman') {
            // Foreman -> semua user di grup yang sama
            $users = User::where('grup_id', $user->grup_id)->get();
        } elseif ($user->role === 'leader') {
            // Leader -> hanya operator dari grup & bagian yang sama
            $users = User::where('role', 'operator')
                ->where('bagian', $user->bagian)
                ->where('grup_id', $user->grup_id)
                ->get();
        } else {
            // super_admin, kabag, manager_plant -> semua user
            $users = User::all();
        }

        // Data meja
        $mejas = Meja::all();

        // Hitung total meja
        $totalMejas = $mejas->count();
        $countAktif = $mejas->where('status', 'aktif')->count();
        $countMaintenance = $mejas->where('status', 'maintenance')->count();
        $countTidakAktif = $mejas->where('status', 'tidak_aktif')->count();

        // Ambil semua part
        $parts = Part::all();

        // Get all grups for the form (filter by bagian assy via users)
        $grups = \App\Models\Grup::whereHas('users', function ($query) {
            $query->where('bagian', 'assy');
        })
            ->orderBy('nama_grup')
            ->get();

        // Progress untuk tiap meja
        $mejaProgress = [];
        foreach ($mejas as $meja) {
            $mejaProgress[$meja->id] = 0; // default
        }

        // Status detail tiap meja
        $checksheetDetails = [];
        foreach ($mejas as $meja) {
            $checksheetDetails[$meja->id] = [
                'maintenance' => $meja->maintenance_count > 0,
                'offline' => $meja->offline_count > 0,
            ];
        }

        // Initialize production data
        $mejaProductionData = [];
        $today = \Carbon\Carbon::today();

        foreach ($mejas as $meja) {
            // Get active planning for this meja
            $planning = \App\Models\PlanningProduksi::with(['moldshop'])
                ->where('meja_id', $meja->id_meja)
                ->whereDate('tanggal', $today)
                ->where('status_approval', 'approved')
                ->first();

            $customer = '-';
            if ($planning && $planning->moldshop) {
                $customer = $planning->moldshop->customer ?? '-';
            }

            $mejaProductionData[$meja->id_meja] = [
                'customer' => $customer,
                'has_planning' => $planning ? true : false,
                'part_name' => $planning && $planning->part ? $planning->part->nama_part : null,
            ];
        }

        return view('dashboardasyy.index', compact(
            'mejas',
            'totalMejas',
            'countAktif',
            'countMaintenance',
            'countTidakAktif',
            'users',
            'parts',
            'grups',
            'mejaProgress',
            'mejaProgress',
            'checksheetDetails',
            'mejaProductionData'
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

        // Authorization check for staff
        if (Auth::user()->role === 'staff') {
            return response()->json([
                'status' => 'error',
                'message' => 'Staff Produksi tidak memiliki hak akses untuk mengisi checksheet.'
            ], 403);
        }

        // Validate the request
        $request->validate([
            'meja_id' => 'required|exists:meja,id_meja',
            'user_id' => 'required|exists:users,id',
            'part_id' => 'required|exists:parts,id',
            'grup_id' => 'required|exists:grups,id',
            'grup' => 'nullable|in:A,B,C', // For backward compatibility
            // Shift validation removed - will be retrieved from jadwal kerja automatically
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
            // Shift validation messages removed
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

            // Get grup_id from request (now using actual grup_id from database)
            $grupId = $request->grup_id ?? null;

            // For backward compatibility: if grup is A/B/C, try to find by nama_grup
            if (!$grupId && in_array($request->grup, ['A', 'B', 'C'])) {
                $grup = \App\Models\Grup::where('nama_grup', 'LIKE', '%' . $request->grup . '%')
                    ->whereHas('users', function ($query) {
                        $query->where('bagian', 'assy');
                    })
                    ->first();
                $grupId = $grup ? $grup->id : null;
            }

            // Create checksheet record
            // Note: shift is no longer stored, it will be retrieved from jadwal kerja via accessor
            $checksheetData = [
                'meja_id' => $request->meja_id,
                'user_id' => $request->user_id,
                'grup_id' => $grupId,
                'part_id' => $request->part_id,
                'grup' => $request->grup ?? null,
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

    public function getMejaDocuments($id)
    {
        $meja = \App\Models\Meja::with(['documents' => function ($q) {
            $q->where('enabled', true)->orderByDesc('id');
        }])->findOrFail($id);

        // Defaults: url dan filename null
        $defaults = [
            'apd' => [
                'url' => asset('images/barang/StandarAPDAssy.pdf'),
                'filename' => null,
            ],
            'qpoint' => [
                'url' => asset('images/barang/3Qpointrev.pdf'),
                'filename' => null,
            ],
            'instruksi_kerja' => [
                'url' => asset('images/barang/intruksikerja.pdf'),
                'filename' => null,
            ],
            'limit_sampel' => [
                'url' => asset('images/barang/LimitSampel.pdf'),
                'filename' => null,
            ],
            'amptm' => [
                'url' => asset('images/barang/1AMTPM.pdf'),
                'filename' => null,
            ],
        ];

        $docs = $defaults;

        foreach ($meja->documents as $doc) {
            if (isset($docs[$doc->type])) {
                $docs[$doc->type] = [
                    'url' => $doc->url ? asset($doc->url) : null,
                    'filename' => $doc->filename ?? null,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $docs,
        ]);
    }

    /**
     * Get operators by grup_id for dashboard assy
     */
    public function getOperatorsByGrup(Request $request)
    {
        $grupId = $request->query('grup_id');

        if (!$grupId) {
            return response()->json(['data' => []]);
        }

        // Get operators from the grup
        $operators = User::where('role', 'operator')
            ->where('bagian', 'assy')
            ->where('grup_id', $grupId)
            ->get(['id', 'nama', 'username', 'nik']);

        return response()->json([
            'data' => $operators,
            'message' => $operators->count() > 0
                ? "Ditemukan {$operators->count()} operator di grup ini"
                : "Tidak ada operator di grup ini"
        ]);
    }

    /**
     * Get ploting data for a specific meja on a specific date
     * This will auto-populate operator, grup, and part from existing ploting
     */
    public function getPlotingByMeja(Request $request)
    {
        $mejaId = $request->query('meja_id');
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        if (!$mejaId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Meja ID harus disediakan',
                'data' => []
            ]);
        }

        // Get planning produksi for this meja on this date
        $plannings = \App\Models\PlanningProduksi::with([
            'plotings' => function ($query) {
                $query->whereNotNull('user_id')
                    ->with(['operator', 'grup']);
            },
            'part'
        ])
            ->where('meja_id', $mejaId)
            ->whereDate('tanggal', $tanggal)
            ->where('status_approval', 'approved')
            ->get();

        $plotingData = [];
        foreach ($plannings as $planning) {
            foreach ($planning->plotings as $ploting) {
                if ($ploting->user_id) {
                    // Get jadwal kerja untuk user pada tanggal tersebut untuk mendapatkan shift
                    $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->user_id)
                        ->whereDate('tanggal', $tanggal)
                        ->where('status', 'aktif')
                        ->first();

                    $isCurrentShift = false;
                    $now = \Carbon\Carbon::now();
                    $currentTime = $now->format('H:i:s');

                    if ($jadwal && $jadwal->jam_masuk && $jadwal->jam_keluar) {
                        $masuk = $jadwal->jam_masuk;
                        $keluar = $jadwal->jam_keluar;

                        if ($keluar < $masuk) {
                            // Crosses midnight (Shift 3)
                            if ($currentTime >= $masuk || $currentTime <= $keluar) {
                                $isCurrentShift = true;
                            }
                        } else {
                            // Normal day shift
                            if ($currentTime >= $masuk && $currentTime <= $keluar) {
                                $isCurrentShift = true;
                            }
                        }
                    }

                    $shift = null;
                    $shiftDisplay = null;
                    if ($jadwal) {
                        $shiftMap = [
                            'pagi' => 'shift_1',
                            'siang' => 'shift_2',
                            'malam' => 'shift_3',
                            'off' => null
                        ];
                        $shift = $shiftMap[$jadwal->shift] ?? null;
                        $shiftDisplay = $jadwal->shift === 'off' ? 'Libur' : 'Shift ' . ucfirst($jadwal->shift);
                    }

                    // Check if checksheet already exists for this meja, user, and shift on this date
                    $existingChecksheet = null;
                    $hasChecksheet = false;
                    if ($shift) {
                        // Get checksheets for this meja and user on this date
                        $checksheets = Checksheet::where('meja_id', $mejaId)
                            ->where('user_id', $ploting->user_id)
                            ->whereDate('created_at', $tanggal)
                            ->get();

                        // Check each checksheet's shift (using accessor)
                        foreach ($checksheets as $cs) {
                            if ($cs->shift === $shift) {
                                $hasChecksheet = true;
                                $existingChecksheet = [
                                    'id' => $cs->id,
                                    'shift' => $cs->shift,
                                    'shift_display' => $cs->shift_display ?? $shiftDisplay,
                                    'created_at' => $cs->created_at->format('Y-m-d H:i:s')
                                ];
                                break;
                            }
                        }
                    }

                    $plotingData[] = [
                        'ploting_id' => $ploting->id,
                        'planning_id' => $planning->id,
                        'user_id' => $ploting->user_id,
                        'user_nama' => $ploting->operator->nama ?? '-',
                        'grup_id' => $ploting->grup_id,
                        'grup_nama' => $ploting->grup->nama_grup ?? '-',
                        'part_id' => $planning->part_id,
                        'part_nama' => $planning->part->nama_part ?? '-',
                        'part_no' => $planning->part->no_parts ?? '-',
                        'status' => $ploting->status,
                        'is_active' => $ploting->status === 'on_progress',
                        'shift' => $shift,
                        'shift_display' => $shiftDisplay,
                        'has_checksheet' => $hasChecksheet,
                        'existing_checksheet' => $existingChecksheet,
                        'is_current_shift' => $isCurrentShift,
                        'updated_at' => $ploting->updated_at
                    ];
                }
            }
        }

        // Sort plotingData to prioritize current shift, then most recently updated
        usort($plotingData, function ($a, $b) {
            // Priority 1: Current Shift
            if ($a['is_current_shift'] && !$b['is_current_shift']) return -1;
            if (!$a['is_current_shift'] && $b['is_current_shift']) return 1;

            // Priority 2: Most recently updated
            if ($a['updated_at'] != $b['updated_at']) {
                return $a['updated_at'] > $b['updated_at'] ? -1 : 1;
            }

            return 0;
        });

        return response()->json([
            'status' => 'success',
            'message' => count($plotingData) > 0
                ? "Ditemukan " . count($plotingData) . " operator yang sudah di-ploting untuk meja ini"
                : "Belum ada operator yang di-ploting untuk meja ini pada tanggal tersebut",
            'data' => $plotingData,
            'has_ploting' => count($plotingData) > 0
        ]);
    }

    /**
     * Check if checksheet already exists for meja, user, and shift
     */
    public function checkChecksheetExists(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:meja,id_meja',
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'nullable|date'
        ]);

        $mejaId = $request->meja_id;
        $userId = $request->user_id;
        $tanggal = $request->tanggal ?: date('Y-m-d');

        // Get jadwal kerja untuk user pada tanggal tersebut untuk mendapatkan shift
        $jadwal = \App\Models\JadwalKerja::where('user_id', $userId)
            ->whereDate('tanggal', $tanggal)
            ->where('status', 'aktif')
            ->first();

        $shift = null;
        $shiftDisplay = null;
        if ($jadwal) {
            $shiftMap = [
                'pagi' => 'shift_1',
                'siang' => 'shift_2',
                'malam' => 'shift_3',
                'off' => null
            ];
            $shift = $shiftMap[$jadwal->shift] ?? null;
            $shiftDisplay = $jadwal->shift === 'off' ? 'Libur' : 'Shift ' . ucfirst($jadwal->shift);
        }

        // Check if checksheet already exists for this meja, user, and shift on this date
        $existingChecksheet = null;
        $hasChecksheet = false;
        if ($shift) {
            // Get checksheets for this meja and user on this date
            $checksheets = Checksheet::where('meja_id', $mejaId)
                ->where('user_id', $userId)
                ->whereDate('created_at', $tanggal)
                ->get();

            // Check each checksheet's shift (using accessor)
            foreach ($checksheets as $cs) {
                if ($cs->shift === $shift) {
                    $hasChecksheet = true;
                    $existingChecksheet = [
                        'id' => $cs->id,
                        'shift' => $cs->shift,
                        'shift_display' => $cs->shift_display ?? $shiftDisplay,
                        'created_at' => $cs->created_at->format('Y-m-d H:i:s')
                    ];
                    break;
                }
            }
        }

        return response()->json([
            'success' => true,
            'has_checksheet' => $hasChecksheet,
            'shift' => $shift,
            'shift_display' => $shiftDisplay,
            'existing_checksheet' => $existingChecksheet
        ]);
    }
}
