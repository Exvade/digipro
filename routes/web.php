<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardInjectController;
use App\Http\Controllers\DashboardAssyController;
use App\Http\Controllers\MesinController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChecksheetController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\GrupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // Menu, Profile, etc.
    Route::get('/menu', fn() => view('menu'))->name('menu');
    Route::get('/produksi', fn() => view('produksi.index'))->name('produksi.index');
    Route::get('/databasekaryawan', fn() => view('databasekaryawan.index'))->name('databasekaryawan.index');
    Route::get('/jadwalkerja', fn() => view('jadwalkerja.index'))->name('jadwalkerja.index');
    Route::get('/oee', [\App\Http\Controllers\OeeController::class, 'index'])->name('oee.dashboard.index');
    Route::post('/oee', [\App\Http\Controllers\OeeController::class, 'store'])->name('oee.store');
    Route::put('/oee/{id}', [\App\Http\Controllers\OeeController::class, 'update'])->name('oee.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // QR Code Generator Routes
    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::get('/', [\App\Http\Controllers\QrCodeController::class, 'index'])->name('index');
        Route::post('/generate', [\App\Http\Controllers\QrCodeController::class, 'generate'])->name('generate');
        Route::post('/download-all', [\App\Http\Controllers\QrCodeController::class, 'downloadAll'])->name('download-all');
        Route::get('/test', function() {
            return view('qrcode.test');
        })->name('test');
    });

    // --- OEE System Routes ---
    Route::prefix('oee')->name('oee.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Oee\DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/dashboard/machine-status', [\App\Http\Controllers\Oee\DashboardController::class, 'getMachineStatus'])->name('dashboard.machine-status');
        Route::get('/dashboard/recent-events', [\App\Http\Controllers\Oee\DashboardController::class, 'getRecentEvents'])->name('dashboard.recent-events');
        Route::get('/dashboard/chart-data', [\App\Http\Controllers\Oee\DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
        
        // Production Data
        Route::prefix('production')->name('production.')->group(function () {
            Route::get('/downtime', [\App\Http\Controllers\Oee\ProductionController::class, 'downtime'])->name('downtime');
            Route::get('/downtime/data', [\App\Http\Controllers\Oee\ProductionController::class, 'getDowntimeData'])->name('downtime.data');
            Route::get('/downtime/export', [\App\Http\Controllers\Oee\ProductionController::class, 'exportDowntime'])->name('downtime.export');
            Route::get('/quality', [\App\Http\Controllers\Oee\ProductionController::class, 'quality'])->name('quality');
            Route::get('/performance', [\App\Http\Controllers\Oee\ProductionController::class, 'performance'])->name('performance');
            Route::get('/shifts', [\App\Http\Controllers\Oee\ProductionController::class, 'shifts'])->name('shifts');
        });
        
        // Production Planning
        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Oee\PlanningController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Oee\PlanningController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Oee\PlanningController::class, 'store'])->name('store');
            Route::get('/{planning}', [\App\Http\Controllers\Oee\PlanningController::class, 'show'])->name('show');
            Route::get('/{planning}/edit', [\App\Http\Controllers\Oee\PlanningController::class, 'edit'])->name('edit');
            Route::put('/{planning}', [\App\Http\Controllers\Oee\PlanningController::class, 'update'])->name('update');
            Route::delete('/{planning}', [\App\Http\Controllers\Oee\PlanningController::class, 'destroy'])->name('destroy');
            Route::get('/schedule', [\App\Http\Controllers\Oee\PlanningController::class, 'schedule'])->name('schedule');
            Route::get('/analysis', [\App\Http\Controllers\Oee\PlanningController::class, 'analysis'])->name('analysis');
            Route::get('/export', [\App\Http\Controllers\Oee\PlanningController::class, 'export'])->name('export');
            Route::post('/{planning}/start', [\App\Http\Controllers\Oee\PlanningController::class, 'start'])->name('start');
            Route::post('/{planning}/pause', [\App\Http\Controllers\Oee\PlanningController::class, 'pause'])->name('pause');
            Route::post('/{planning}/complete', [\App\Http\Controllers\Oee\PlanningController::class, 'complete'])->name('complete');
        });
        
        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/targets', [\App\Http\Controllers\Oee\SettingController::class, 'targets'])->name('targets');
            Route::post('/targets', [\App\Http\Controllers\Oee\SettingController::class, 'storeTarget'])->name('targets.store');
            Route::get('/targets/import', [\App\Http\Controllers\Oee\SettingController::class, 'importTargets'])->name('targets.import');
            Route::post('/targets/import', [\App\Http\Controllers\Oee\SettingController::class, 'processImportTargets'])->name('targets.process-import');
            Route::get('/targets/export', [\App\Http\Controllers\Oee\SettingController::class, 'exportTargets'])->name('targets.export');
            Route::get('/targets/{target}/details', [\App\Http\Controllers\Oee\SettingController::class, 'targetDetails'])->name('targets.details');
            Route::get('/targets/{target}/edit', [\App\Http\Controllers\Oee\SettingController::class, 'editTarget'])->name('targets.edit');
            Route::put('/targets/{target}', [\App\Http\Controllers\Oee\SettingController::class, 'updateTarget'])->name('targets.update');
            Route::delete('/targets/{target}', [\App\Http\Controllers\Oee\SettingController::class, 'deleteTarget'])->name('targets.delete');
            Route::get('/downtime-codes', [\App\Http\Controllers\Oee\SettingController::class, 'downtimeCodes'])->name('downtime-codes');
            Route::post('/downtime-codes', [\App\Http\Controllers\Oee\SettingController::class, 'storeDowntimeCode'])->name('downtime-codes.store');
            Route::put('/downtime-codes/{downtimeCode}', [\App\Http\Controllers\Oee\SettingController::class, 'updateDowntimeCode'])->name('downtime-codes.update');
            Route::delete('/downtime-codes/{downtimeCode}', [\App\Http\Controllers\Oee\SettingController::class, 'deleteDowntimeCode'])->name('downtime-codes.delete');
            Route::get('/downtime-codes/export', [\App\Http\Controllers\Oee\SettingController::class, 'exportDowntimeCodes'])->name('downtime-codes.export');
        });
    });
    
    // --- Role-based Dashboards ---
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/super-admin/dashboard/assy', [DashboardAssyController::class, 'index'])->name('super-admin.dashboard.assy');
        Route::get('/super-admin/dashboard/inject', [DashboardInjectController::class, 'index'])->name('super-admin.dashboard.inject');
    });

    Route::middleware('role:leader,foreman')->group(function () {
        Route::get('/leader/dashboard/assy', [DashboardAssyController::class, 'index'])->name('leader.dashboard.assy');
        Route::get('/leader/dashboard/inject', [DashboardInjectController::class, 'index'])->name('leader.dashboard.inject');
    });
    
    Route::middleware('role:kabag')->group(function () {
        Route::get('/kabag/dashboard/assy', [DashboardAssyController::class, 'index'])->name('kabag.dashboard.assy');
        Route::get('/kabag/dashboard/inject', [DashboardInjectController::class, 'index'])->name('kabag.dashboard.inject');
    });

    Route::middleware('role:manager_plant')->group(function () {
        Route::get('/manager-plant/dashboard/assy', [DashboardAssyController::class, 'index'])->name('manager-plant.dashboard.assy');
        Route::get('/manager-plant/dashboard/inject', [DashboardInjectController::class, 'index'])->name('manager-plant.dashboard.inject');
    });

    Route::middleware('role:foreman')->group(function () {
        Route::get('/foreman/dashboard/assy', [DashboardAssyController::class, 'index'])->name('foreman.dashboard.assy');
        Route::get('/foreman/dashboard/inject', [DashboardInjectController::class, 'index'])->name('foreman.dashboard.inject');
    });

    // --- Kabag, Manager Plant & Super Admin Access ---
    Route::middleware('role:super_admin,kabag,manager_plant')->group(function () {
        Route::resource('mesin', MesinController::class);
        
        // Test route for debugging mesin access
        Route::get('/mesin/test/debug', function() {
            $user = auth()->user();
            $mesins = \App\Models\Mesin::all();
            return response()->json([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->nama,
                'mesins_count' => $mesins->count(),
                'csrf_token' => csrf_token(),
                'message' => 'Mesin access test successful'
            ]);
        })->name('mesin.test.debug');
        
        Route::resource('parts', PartController::class);
        Route::get('/parts/search', [PartController::class, 'search'])->name('parts.search');

        Route::resource('meja', MejaController::class);
        Route::get('/meja/{meja}/edit-modal', [MejaController::class, 'editModal'])->name('meja.editModal');

        Route::resource('checksheet', ChecksheetController::class);

        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::get('/approval/history', [ApprovalController::class, 'history'])->name('approval.history');
        Route::get('/approval/{checksheet}', [ApprovalController::class, 'show'])->name('approval.show');
        Route::post('/approval/{checksheet}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{checksheet}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
        Route::post('/approval/approve-all', [ApprovalController::class, 'approveAll'])->name('approval.approveAll');
        
        // Test route for debugging approval functionality
        Route::get('/approval/test/debug', function() {
            $pendingChecksheets = \App\Models\Checksheet::where('approval_status', 'pending')->count();
            $user = auth()->user();
            return response()->json([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'pending_checksheets' => $pendingChecksheets,
                'csrf_token' => csrf_token(),
                'message' => 'Approval test route working'
            ]);
        })->name('approval.test.debug');
    });

    // --- Report Access ---
    Route::middleware('role:super_admin,kabag,manager_plant,leader,foreman')->group(function () {
        Route::resource('report', ReportController::class);
        Route::post('/report/export/excel', [ReportController::class, 'exportExcel'])->name('report.export.excel');
        Route::post('/report/export/pdf', [ReportController::class, 'exportPdf'])->name('report.export.pdf');
        
        // Test route for debugging
        Route::get('/report/test-form', function() {
            $grups = App\Models\Grup::all();
            $users = App\Models\User::with('grup')->get();
            return view('report.test-form', compact('grups', 'users'));
        })->name('report.test-form');
        
        // Simple test route
        Route::get('/report/simple-test', function() {
            $grups = App\Models\Grup::all();
            return view('report.simple-test', compact('grups'));
        })->name('report.simple-test');
        
        // Test route for debugging report functionality
        Route::get('/report/test/debug', function() {
            $user = auth()->user();
            $grups = \App\Models\Grup::all();
            $users = \App\Models\User::with('grup')->get();
            $mesins = \App\Models\Mesin::all();
            $parts = \App\Models\Part::all();
            $mejas = \App\Models\Meja::all();
            
            return response()->json([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_department' => $user->bagian ?? null,
                'grups_count' => $grups->count(),
                'users_count' => $users->count(),
                'mesins_count' => $mesins->count(),
                'parts_count' => $parts->count(),
                'mejas_count' => $mejas->count(),
                'csrf_token' => csrf_token(),
                'report_store_url' => route('report.store'),
                'export_excel_url' => route('report.export.excel'),
                'export_pdf_url' => route('report.export.pdf'),
                'message' => 'Report test route working'
            ]);
        })->name('report.test.debug');
    });

    // --- Group Access ---
    Route::middleware('role:super_admin,kabag,manager_plant')->group(function () {
        Route::get('/grup/by-bagian-shift', [GrupController::class, 'getByBagianAndShift'])->name('grup.by-bagian-shift');
    });

    // --- Super Admin Only Access ---
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('user', UserController::class);
        Route::get('/user/{user}/edit-modal', [UserController::class, 'editModal'])->name('user.editModal');
    });

    // --- Common Dashboard AJAX/API Routes ---
    Route::middleware('role:super_admin,leader,kabag,foreman')->group(function () {
        Route::post('/dashboard/inject/checksheet', [DashboardInjectController::class, 'storeChecksheet'])
            ->name('dashboard.inject.checksheet.store');
            
        Route::post('/dashboard/assy/checksheet', [DashboardAssyController::class, 'storeChecksheet'])
            ->name('dashboard.assy.checksheet.store');
            
        Route::get('/dashboard/inject/machine/{id}', [DashboardInjectController::class, 'getMachineDetails'])
            ->name('dashboard.inject.machine.details');
            
        Route::get('/dashboard/inject/machine/{mesinId}/checksheets', [DashboardInjectController::class, 'getMachineChecksheets'])
            ->name('dashboard.inject.machine.checksheets');
    });

    // Dashboard Inject Routes
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/inject', [DashboardInjectController::class, 'index'])->name('inject');
        Route::post('/inject/store', [DashboardInjectController::class, 'storeChecksheet'])->name('inject.store');
        Route::get('/inject/machine/{id}', [DashboardInjectController::class, 'getMachineDetails'])->name('inject.machine.details');
        Route::get('/inject/machine/{mesinId}/checksheets', [DashboardInjectController::class, 'getMachineChecksheets'])->name('inject.machine.checksheets');
    });

    // Notifications Route
    Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notifications');
});

require __DIR__.'/auth.php';
