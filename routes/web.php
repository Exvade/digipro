<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BomDetailController;
use App\Http\Controllers\ChecksheetController;
use App\Http\Controllers\DashboardAssyController;
use App\Http\Controllers\DashboardInjectController;
use App\Http\Controllers\GrupController;
use App\Http\Controllers\InputProduksiController;
use App\Http\Controllers\JadwalKerjaController;
use App\Http\Controllers\LaporanProduksiController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\MesinController;
use App\Http\Controllers\MoldshopController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OeeRecordController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PlootingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Public Routes (Outside Auth Middleware)
|--------------------------------------------------------------------------
*/

// Public file serving for storage when symlink not available
Route::get('/storage/{path}', function ($path) {
    $decodedPath = urldecode($path);
    if (Storage::disk('public')->exists($decodedPath)) {
        $fullPath = storage_path('app/public/' . $decodedPath);
        return response()->file($fullPath);
    }
    abort(404);
})->where('path', '.*');

// Public Dashboard Inject Routes
Route::get('/dashboard-inject', [DashboardInjectController::class, 'index'])->name('dashboard.inject.public');
Route::get('/dashboard/inject/realtime-production', [DashboardInjectController::class, 'getRealtimeProductionData'])->name('dashboard.inject.realtime.public');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Basic Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/', fn() => view('menu'))->name('home');
    Route::get('/menu', fn() => view('menu'))->name('menu');
    Route::get('/produksi', fn() => view('produksi.index'))->name('produksi.index');
    Route::get('/databasekaryawan', fn() => view('databasekaryawan.index'))->name('databasekaryawan.index');

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Planning Produksi Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('planning')->name('planning.')->group(function () {
        // Core CRUD
        Route::get('/', [PlanningController::class, 'index'])->name('index');
        Route::get('/history', [PlanningController::class, 'history'])->name('history');
        Route::post('/', [PlanningController::class, 'store'])->name('store');
        Route::put('/{planning}', [PlanningController::class, 'update'])->name('update');
        Route::delete('/{planning}', [PlanningController::class, 'destroy'])->name('destroy');
        Route::get('/{id}', [PlanningController::class, 'show'])->name('show');
        Route::get('/{id}/qrcode/{type}', [PlanningController::class, 'secureQrCode'])->name('qrcode.secure');

        // Approval
        Route::get('/approval/page', [\App\Http\Controllers\PlanningApprovalController::class, 'approvalPage'])->name('approval.page');
        Route::post('/approval', [\App\Http\Controllers\PlanningApprovalController::class, 'approval'])->name('approval');

        // Reports / Printing
        Route::get('/cetak', [\App\Http\Controllers\PlanningReportController::class, 'cetak'])->name('cetak');
        Route::get('/cetak/tanggal', [\App\Http\Controllers\PlanningReportController::class, 'cetakByTanggal'])->name('cetak.tanggal');
        Route::get('/cetak/range', [\App\Http\Controllers\PlanningReportController::class, 'cetakByRange'])->name('cetak.range');

        // Excel Import/Export
        Route::get('/download-template', [\App\Http\Controllers\PlanningExcelController::class, 'downloadTemplate'])->name('download-template');
        Route::post('/import-excel', [\App\Http\Controllers\PlanningExcelController::class, 'importExcel'])->name('import-excel');
    });

    /*
    |--------------------------------------------------------------------------
    | Ploting Routes
    |--------------------------------------------------------------------------
    */

    // Ploting routes (custom routes must be before resource route)
    Route::get('/ploting/operators', [PlootingController::class, 'operatorsByGroup'])->name('ploting.operators');
    Route::post('/ploting/{id}/start', [PlootingController::class, 'start'])->name('ploting.start');
    Route::post('/ploting/{id}/finish', [PlootingController::class, 'finish'])->name('ploting.finish');
    Route::post('/ploting/{id}/runtime', [PlootingController::class, 'runtime'])->name('ploting.runtime');
    Route::get('/ploting/export/excel', [PlootingController::class, 'exportExcel'])->name('ploting.export.excel');
    Route::get('/ploting/export/pdf', [PlootingController::class, 'exportPdf'])->name('ploting.export.pdf');

    // Ploting resource route
    Route::resource('ploting', PlootingController::class);

    /*
    |--------------------------------------------------------------------------
    | Jadwal Kerja Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('jadwal-kerja')->name('jadwal-kerja.')->group(function () {
        Route::get('/', [JadwalKerjaController::class, 'index'])->name('index');
        Route::get('/operator', [JadwalKerjaController::class, 'operatorIndex'])->name('operator-index');
        Route::get('/create', [JadwalKerjaController::class, 'create'])->name('create');
        Route::post('/', [JadwalKerjaController::class, 'store'])->name('store');
        Route::get('/get-user-shift', [JadwalKerjaController::class, 'getUserShift'])->name('get-user-shift');

        // Import Excel routes
        Route::get('/download-template', [JadwalKerjaController::class, 'downloadTemplate'])->name('download-template');
        Route::post('/import', [JadwalKerjaController::class, 'importExcel'])->name('import');

        Route::get('/{id}/edit', [JadwalKerjaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [JadwalKerjaController::class, 'update'])->name('update');
        Route::delete('/{id}', [JadwalKerjaController::class, 'destroy'])->name('destroy');
        Route::get('/{id}', [JadwalKerjaController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | OEE (Overall Equipment Effectiveness) Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('oee')->name('oee.')->group(function () {
        Route::get('/', [OeeRecordController::class, 'dashboard'])->name('index');
        Route::get('/dashboard', [OeeRecordController::class, 'dashboard'])->name('dashboard');
        Route::get('/export/excel', [OeeRecordController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [OeeRecordController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/detail/{id}', [OeeRecordController::class, 'getDetail'])->name('detail');
        Route::get('/{id}', [OeeRecordController::class, 'show'])->name('show');
        Route::post('/generate/{planning}', [OeeRecordController::class, 'generate'])->name('generate');
        Route::get('/api/current', [OeeRecordController::class, 'apiCurrent'])->name('api.current');
    });

    /*


    /*
    |--------------------------------------------------------------------------
    | Input Produksi Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('input-produksi')->name('input-produksi.')->group(function () {
        Route::get('/', [InputProduksiController::class, 'index'])->name('index');
        Route::get('/datatables-data', [InputProduksiController::class, 'getDataTablesData'])->name('datatables-data');
        Route::post('/add-ok/{planning}', [InputProduksiController::class, 'addOk'])->name('addOk');
        Route::post('/add-ng/{planning}', [InputProduksiController::class, 'addNg'])->name('addNg');
        Route::post('/add-call/{planning}', [InputProduksiController::class, 'addCall'])->name('addCall');
        Route::get('/current-data/{planning}', [InputProduksiController::class, 'getCurrentData'])->name('getCurrentData');
        Route::get('/produksi-per-jam/{planning}', [InputProduksiController::class, 'getProduksiPerJam'])->name('getProduksiPerJam');
        Route::post('/produksi-per-jam/{planning}', [InputProduksiController::class, 'saveProduksiPerJam'])->name('saveProduksiPerJam');
        Route::post('/approve/{id}', [InputProduksiController::class, 'approve'])->name('approve');
        Route::post('/reject/{id}', [InputProduksiController::class, 'reject'])->name('reject');
        Route::get('/persiapan/{ploting}', [InputProduksiController::class, 'getPersiapan'])->name('getPersiapan');
        Route::post('/persiapan/{ploting}', [InputProduksiController::class, 'storePersiapan'])->name('storePersiapan');

        // Super Admin Only - Show route must be before {input} routes
        Route::get('/{input}/show', [InputProduksiController::class, 'show'])->name('show');
        Route::get('/{input}/edit', [InputProduksiController::class, 'edit'])->name('edit');
        Route::put('/{input}', [InputProduksiController::class, 'update'])->name('update');
        Route::delete('/{input}', [InputProduksiController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Laporan Produksi Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('laporan')->name('laporan-produksi.')->group(function () {
        // Cetak routes (must be before {id} routes)
        Route::get('/', [LaporanProduksiController::class, 'index'])->name('index');
        Route::get('/cetak', [LaporanProduksiController::class, 'cetak'])->name('cetak');
        Route::get('/{id}/cetak', [LaporanProduksiController::class, 'cetakSingle'])->name('cetak-single');
        Route::get('/{id}/detail', [LaporanProduksiController::class, 'getDetail'])->name('get-detail');
        Route::get('/{id}', [LaporanProduksiController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | QR Code Generator Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::get('/', [QrCodeController::class, 'index'])->name('index');
        Route::post('/generate', [QrCodeController::class, 'generate'])->name('generate');
        Route::post('/download-all', [QrCodeController::class, 'downloadAll'])->name('download-all');
        Route::get('/test', fn() => view('qrcode.test'))->name('test');
    });

    /*
    |--------------------------------------------------------------------------
    | Approval Leader Routes (Leader, Foreman, Kabag & Super Admin)
    |--------------------------------------------------------------------------
    */

    Route::prefix('approval-leader')->name('approval-leader.')->group(function () {
        Route::get('/', function () {
            $user = Auth::user();
            if (!in_array($user->role, ['leader', 'foreman', 'kabag', 'super_admin'])) {
                abort(403, 'Unauthorized access');
            }
            return view('approval-leader.index');
        })->name('index');
    });

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/approval/data', [InputProduksiController::class, 'getApprovalData'])->name('approval.data');
        Route::get('/approval/detail/{id}', [InputProduksiController::class, 'getApprovalDetail'])->name('approval.detail');
    });

    /*
    |--------------------------------------------------------------------------
    | Role-based Dashboard Routes
    |--------------------------------------------------------------------------
    */

    // Super Admin Dashboards
    Route::middleware('role:super_admin')->prefix('super-admin/dashboard')->name('super-admin.dashboard.')->group(function () {
        Route::get('/assy', [DashboardAssyController::class, 'index'])->name('assy');
        Route::get('/inject', [DashboardInjectController::class, 'index'])->name('inject');
    });

    // Leader & Foreman Dashboards
    Route::middleware('role:leader,foreman')->prefix('leader/dashboard')->name('leader.dashboard.')->group(function () {
        Route::get('/assy', [DashboardAssyController::class, 'index'])->name('assy');
        Route::get('/inject', [DashboardInjectController::class, 'index'])->name('inject');
    });

    // Staff Dashboards
    Route::middleware('role:staff')->prefix('staff/dashboard')->name('staff.dashboard.')->group(function () {
        Route::get('/assy', [DashboardAssyController::class, 'index'])->name('assy');
        Route::get('/inject', [DashboardInjectController::class, 'index'])->name('inject');
    });

    // Kabag Dashboards
    Route::middleware('role:kabag')->prefix('kabag/dashboard')->name('kabag.dashboard.')->group(function () {
        Route::get('/assy', [DashboardAssyController::class, 'index'])->name('assy');
        Route::get('/inject', [DashboardInjectController::class, 'index'])->name('inject');
    });

    // Manager Plant Dashboards
    Route::middleware('role:manager_plant')->prefix('manager-plant/dashboard')->name('manager-plant.dashboard.')->group(function () {
        Route::get('/assy', [DashboardAssyController::class, 'index'])->name('assy');
        Route::get('/inject', [DashboardInjectController::class, 'index'])->name('inject');
    });

    // Foreman Dashboards
    Route::middleware('role:foreman')->prefix('foreman/dashboard')->name('foreman.dashboard.')->group(function () {
        Route::get('/assy', [DashboardAssyController::class, 'index'])->name('assy');
        Route::get('/inject', [DashboardInjectController::class, 'index'])->name('inject');
    });

    // Common Dashboard AJAX/API Routes
    Route::middleware('role:super_admin,leader,kabag,foreman,staff')->prefix('dashboard')->name('dashboard.')->group(function () {
        Route::post('/inject/checksheet', [DashboardInjectController::class, 'storeChecksheet'])->name('inject.checksheet.store');
        Route::post('/assy/checksheet', [DashboardAssyController::class, 'storeChecksheet'])->name('assy.checksheet.store');
        Route::get('/inject/machine/{id}', [DashboardInjectController::class, 'getMachineDetails'])->name('inject.machine.details');
        Route::get('/inject/machine/{mesinId}/checksheets', [DashboardInjectController::class, 'getMachineChecksheets'])->name('inject.machine.checksheets');
        Route::get('/inject/machine/{id}/documents', [DashboardInjectController::class, 'getMachineDocuments'])->name('inject.machine.documents');
        Route::get('/inject/operators-by-grup', [DashboardInjectController::class, 'getOperatorsByGrup'])->name('inject.operators-by-grup');
        Route::get('/inject/ploting-by-mesin', [DashboardInjectController::class, 'getPlotingByMesin'])->name('inject.ploting-by-mesin');
        Route::get('/assy/meja/{id}/documents', [DashboardAssyController::class, 'getMejaDocuments'])->name('assy.meja.documents');
        Route::get('/assy/operators-by-grup', [DashboardAssyController::class, 'getOperatorsByGrup'])->name('assy.operators-by-grup');
        Route::get('/assy/ploting-by-meja', [DashboardAssyController::class, 'getPlotingByMeja'])->name('assy.ploting-by-meja');
        Route::post('/assy/check-checksheet', [DashboardAssyController::class, 'checkChecksheetExists'])->name('assy.check-checksheet');
    });

    /*
    |--------------------------------------------------------------------------
    | Resource Routes (Role-based Access)
    |--------------------------------------------------------------------------
    */

    // Mesin, Parts, Meja, Checksheet, Approval (Kabag, Manager Plant, Staff, Super Admin, Leader)
    Route::middleware('role:super_admin,kabag,manager_plant,staff,leader')->group(function () {
        Route::resource('mesin', MesinController::class);
        Route::resource('parts', PartController::class);
        Route::get('/moldshop/history', [MoldshopController::class, 'history'])->name('moldshop.history');
        Route::get('/moldshop/{id}/usage', [MoldshopController::class, 'usageDetails'])->name('moldshop.usage');
        Route::resource('moldshop', MoldshopController::class);
        Route::get('/parts/search', [PartController::class, 'search'])->name('parts.search');
        Route::resource('meja', MejaController::class);
        Route::get('/meja/{meja}/edit-modal', [MejaController::class, 'editModal'])->name('meja.editModal');
        Route::resource('checksheet', ChecksheetController::class);

        // Approval routes
        Route::prefix('approval')->name('approval.')->group(function () {
            Route::get('/', [ApprovalController::class, 'index'])->name('index');
            Route::get('/history', [ApprovalController::class, 'history'])->name('history');
            Route::get('/{checksheet}', [ApprovalController::class, 'show'])->name('show');
            Route::post('/{checksheet}/approve', [ApprovalController::class, 'approve'])->name('approve');
            Route::post('/{checksheet}/reject', [ApprovalController::class, 'reject'])->name('reject');
            Route::post('/approve-all', [ApprovalController::class, 'approveAll'])->name('approveAll');
        });
    });

    // Report Routes (Super Admin, Kabag, Manager Plant, Leader, Foreman, Staff)
    Route::middleware('role:super_admin,kabag,manager_plant,leader,foreman,staff')->group(function () {
        Route::get('/dashboard/inject/history', [DashboardInjectController::class, 'history'])->name('dashboard.inject.history');
        Route::get('/dashboard/inject/history/print', [DashboardInjectController::class, 'historyPrint'])->name('dashboard.inject.history.print');
        Route::resource('report', ReportController::class);
        Route::post('/report/export/excel', [ReportController::class, 'exportExcel'])->name('report.export.excel');
        Route::post('/report/export/pdf', [ReportController::class, 'exportPdf'])->name('report.export.pdf');
    });

    // Group Routes (Super Admin, Kabag, Manager Plant)
    Route::middleware('role:super_admin,kabag,manager_plant')->group(function () {
        Route::get('/grup/by-bagian-shift', [GrupController::class, 'getByBagianAndShift'])->name('grup.by-bagian-shift');
    });

    // User Management (Super Admin, Kabag, Manager Plant)
    Route::middleware('role:super_admin,kabag,manager_plant')->group(function () {
        Route::resource('user', UserController::class);
        Route::get('/user/{user}/edit-modal', [UserController::class, 'editModal'])->name('user.editModal');
        Route::get('/user/{user}/show-modal', [UserController::class, 'showModal'])->name('user.showModal');
    });

    // Documents Management (Super Admin, Kabag, Manager Plant, Leader, Foreman)
    Route::middleware('role:super_admin,kabag,manager_plant,leader,foreman')->prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DocumentController::class, 'index'])->name('index');

        // Mesin Documents
        Route::get('/mesin/{mesin}/edit', [\App\Http\Controllers\DocumentController::class, 'editMesin'])->name('mesin.edit');
        Route::post('/mesin/{mesin}', [\App\Http\Controllers\DocumentController::class, 'updateMesin'])->name('mesin.update');
        Route::post('/mesin/{mesin}/upload', [\App\Http\Controllers\DocumentController::class, 'uploadMesin'])->name('upload.mesin');
        Route::delete('/mesin-doc/{doc}', [\App\Http\Controllers\DocumentController::class, 'destroyMesinDoc'])->name('mesin.doc.destroy');

        // Meja Documents
        Route::get('/meja/{meja}/edit', [\App\Http\Controllers\DocumentController::class, 'editMeja'])->name('meja.edit');
        Route::post('/meja/{meja}', [\App\Http\Controllers\DocumentController::class, 'updateMeja'])->name('meja.update');
        Route::post('/meja/{meja}/upload', [\App\Http\Controllers\DocumentController::class, 'uploadMeja'])->name('upload.meja');
        Route::delete('/meja-doc/{doc}', [\App\Http\Controllers\DocumentController::class, 'destroyMejaDoc'])->name('meja.doc.destroy');
    });

    // BOM Details (Authenticated Users)
    Route::resource('bom-details', BomDetailController::class);

    /*
    |--------------------------------------------------------------------------
    | Utility Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notifications');

    Route::get('/secure-signature', function () {
        $path = public_path('images/TTD.png');
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, no-cache, must-revalidate',
        ]);
    })->name('secure.signature');
});

require __DIR__ . '/auth.php';
