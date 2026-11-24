<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MaterialInController;
use App\Http\Controllers\MaterialOutController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CompanyProfileController;
use App\Livewire\TrashComponent;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WebsiteThemeController;
use App\Http\Controllers\DebuggingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectConsumptionController;
use App\Http\Controllers\ItemBarcodeController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\FinishGoodController;
use App\Http\Controllers\PurchaseOrderController;

/*
|--------------------------------------------------------------------------
| Web Routes - PT Mudalaya Energy Indonesia WMS
|--------------------------------------------------------------------------
*/

// Authentication & Session Switching (Public)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// =========================================================================
// ROUTE TERAUTENTIKASI (Wajib Login)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    // Session Quick Switching (Hanya Web Administrator)
    Route::get('/switch-user/{id}', [AuthController::class, 'switchUser'])->name('switch-user');

    // WMS Dashboard (Dapat diakses Admin & Warehouse, PPIC diarahkan ke items, QC ke material-in)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory Items - Ketersediaan Stok (Akses Baca untuk Admin & PPIC)
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/export', [ItemController::class, 'export'])->name('items.export');
    Route::get('/items/{item}/barcode-print', [ItemBarcodeController::class, 'printSingle'])->name('items.barcode.print');
    Route::match(['get', 'post'], '/items/barcode-bulk-print', [ItemBarcodeController::class, 'printBulk'])->name('items.barcode.bulk');

    // User Profile & Account Settings
    Route::get('/user-profile', [UserProfileController::class, 'index'])->name('user.index');
    Route::put('/user-profile', [UserProfileController::class, 'updateProfile'])->name('user.update-profile');
    Route::put('/user-profile/password', [UserProfileController::class, 'updatePassword'])->name('user.update-password');

    // =========================================================================
    // ROUTE KHUSUS ADMINISTRATOR GUDANG (Dilindungi Middleware 'admin')
    // Akun PPIC DIBATASI (Hanya Memiliki Hak Akses Melihat Stok Barang)
    // =========================================================================
    Route::middleware(['admin'])->group(function () {
    // Kelola Barang (Tambah, Ubah, Hapus, Import, FPBJ Export)
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/items/template', [ItemController::class, 'downloadTemplate'])->name('items.template');
    Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');
    Route::match(['get', 'post'], '/items/purchase-request/export', [ItemController::class, 'exportPurchaseRequest'])->name('items.purchase-request.export');

    // Purchase Order (Menu Pengadaan PO & WMS)
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');

    // Material Masuk & Keluar (Menu WMS)
    Route::get('/material-in', [MaterialInController::class, 'index'])->name('material-in.index');
    Route::get('/material-out', [MaterialOutController::class, 'index'])->name('material-out.index');
    Route::get('/material-out/export', [MaterialOutController::class, 'exportExcel'])->name('material-out.export');

    // Kategori Barang (Menu WMS)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Satuan Barang (Master Data Satuan Terpusat)
    Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    Route::post('/units', [UnitController::class, 'store'])->name('units.store');
    Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

    // Manajemen Proyek & Penggunaan Material (Menu WMS)
    Route::redirect('/projects', '/projects-summary');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects-summary', [ProjectConsumptionController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::patch('/projects/{project}/toggle-status', [ProjectController::class, 'toggleStatus'])->name('projects.toggle-status');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project}/add-material', [ProjectController::class, 'addMaterial'])->name('projects.add-material');

    // Manajemen Barang Jadi / Finish Good (Menu WMS)
    Route::get('/finish-goods', [FinishGoodController::class, 'index'])->name('finish-goods.index');
    Route::post('/finish-goods', [FinishGoodController::class, 'store'])->name('finish-goods.store');
    Route::post('/finish-goods/wip', [FinishGoodController::class, 'storeWip'])->name('finish-goods.store-wip');
    Route::put('/finish-goods/{finishGood}', [FinishGoodController::class, 'update'])->name('finish-goods.update');
    Route::patch('/finish-goods/{finishGood}/delivery', [FinishGoodController::class, 'updateDelivery'])->name('finish-goods.delivery');
    Route::delete('/finish-goods/{finishGood}', [FinishGoodController::class, 'destroy'])->name('finish-goods.destroy');
    Route::get('/finish-goods-export', [FinishGoodController::class, 'exportExcel'])->name('finish-goods.export');

    // Warehouse Stock Movement Logs & Reports (Pencatatan Gudang)
    Route::get('/stock-logs', [StockLogController::class, 'index'])->name('logs.index');
    Route::post('/stock-logs', [StockLogController::class, 'store'])->name('logs.store');
    Route::get('/stock-logs/{log}/pdf', [StockLogController::class, 'downloadPdf'])->name('logs.pdf');
    Route::get('/stock-opnames', [StockOpnameController::class, 'index'])->name('stock-opnames.index');
    Route::get('/stock-opnames/export', [StockOpnameController::class, 'exportExcel'])->name('stock-opnames.export');
    Route::get('/stock-opnames/print', [StockOpnameController::class, 'print'])->name('stock-opnames.print');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // 1. Laporan Inspeksi Incoming Material & Cat (Prompt Section 6)
    Route::get('/reports/incoming-inspection', [ReportController::class, 'incomingInspectionReport'])->name('reports.incoming-inspection');
    Route::get('/reports/incoming-inspection/export', [ReportController::class, 'exportIncomingInspectionExcel'])->name('reports.incoming-inspection.export');
    Route::get('/reports/incoming-inspection/pdf', [ReportController::class, 'exportIncomingInspectionPdf'])->name('reports.incoming-inspection.pdf');

    // 2. Laporan Pengeluaran Cat / Painting (Prompt Section 6)
    // 2. Laporan Pengeluaran Material (Gabungan: All / Consumable / Secondary Part / Cat & Coating)
    Route::get('/reports/material-out', [ReportController::class, 'materialOutReport'])->name('reports.material-out');
    Route::get('/reports/material-out/export', [ReportController::class, 'exportMaterialOutExcel'])->name('reports.material-out.export');
    Route::get('/reports/material-out/pdf', [ReportController::class, 'exportMaterialOutPdf'])->name('reports.material-out.pdf');

    // 3. Laporan Material Rusak / Reject (Report/Reject Material & BAP)
    Route::get('/material-in/rejects/{id}/bap-pdf', [ReportController::class, 'exportRejectMaterialBapPdf'])->name('material-in.rejects.bap-pdf');
    Route::get('/reports/reject-material', [ReportController::class, 'rejectMaterialReport'])->name('reports.reject-material');
    Route::get('/reports/reject-material/export', [ReportController::class, 'exportRejectMaterialExcel'])->name('reports.reject-material.export');
    Route::get('/reports/reject-material/pdf', [ReportController::class, 'exportRejectMaterialPdf'])->name('reports.reject-material.pdf');
    Route::get('/reports/reject-material/{id}/bap', [ReportController::class, 'exportRejectMaterialBapPdf'])->name('reports.reject-material.bap');
    
    // 4. Analisis Barang Lambat Bergerak & Dead Stock
    Route::get('/reports/slow-moving-stock', [ReportController::class, 'slowMovingStockReport'])->name('reports.slow-moving-stock');
    Route::get('/reports/slow-moving-stock/export', [ReportController::class, 'exportSlowMovingStockExcel'])->name('reports.slow-moving-stock.export');
    Route::get('/reports/slow-moving-stock/pdf', [ReportController::class, 'exportSlowMovingStockPdf'])->name('reports.slow-moving-stock.pdf');

    // Specific Category Reports
    Route::get('/reports/cat-painting', [ReportController::class, 'catPaintingReport'])->name('reports.cat-painting');
    Route::get('/reports/cat-painting/export', [ReportController::class, 'exportCatPaintingExcel'])->name('reports.cat-painting.export');
    Route::get('/reports/cat-painting/pdf', [ReportController::class, 'exportCatPaintingPdf'])->name('reports.cat-painting.pdf');

    Route::get('/reports/secondary-part', [ReportController::class, 'secondaryPartReport'])->name('reports.secondary-part');
    Route::get('/reports/secondary-part/export', [ReportController::class, 'exportSecondaryPartExcel'])->name('reports.secondary-part.export');
    Route::get('/reports/secondary-part/pdf', [ReportController::class, 'exportSecondaryPartPdf'])->name('reports.secondary-part.pdf');

    Route::get('/reports/consumable', [ReportController::class, 'consumableReport'])->name('reports.consumable');
    Route::get('/reports/consumable/export', [ReportController::class, 'exportConsumableExcel'])->name('reports.consumable.export');
    Route::get('/reports/consumable/pdf', [ReportController::class, 'exportConsumablePdf'])->name('reports.consumable.pdf');

    // Company Profile & Custom Logo Settings
    Route::get('/company-profile', [CompanyProfileController::class, 'index'])->name('company.index');
    Route::put('/company-profile', [CompanyProfileController::class, 'update'])->name('company.update');
    Route::get('/website-theme', [WebsiteThemeController::class, 'index'])->name('website-theme.index');
    Route::put('/website-theme', [WebsiteThemeController::class, 'update'])->name('website-theme.update');

    // Pengaturan System (Akses Admin)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Log Aktivitas User (Pengaturan System)
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::delete('/activity-logs/clear', [ActivityLogController::class, 'clear'])->name('activity-logs.clear');

    // Backup Database (Pengaturan System)
    Route::get('/database-backup', [DatabaseBackupController::class, 'index'])->name('backup.index');
    Route::post('/database-backup/settings', [DatabaseBackupController::class, 'updateSettings'])->name('backup.settings');
    Route::post('/database-backup/create', [DatabaseBackupController::class, 'createManual'])->name('backup.create');
    Route::get('/database-backup/download', [DatabaseBackupController::class, 'download'])->name('backup.download');
    Route::get('/database-backup/files/{filename}/download', [DatabaseBackupController::class, 'downloadFile'])->name('backup.file.download');
    Route::post('/database-backup/files/{filename}/restore', [DatabaseBackupController::class, 'restoreFile'])->name('backup.file.restore');
    Route::delete('/database-backup/files/{filename}', [DatabaseBackupController::class, 'deleteFile'])->name('backup.file.delete');
    Route::post('/database-backup/restore', [DatabaseBackupController::class, 'restore'])->name('backup.restore');

    // Tong Sampah Data (Recycle Bin)
    Route::view('/trash', 'pages.trash.index', ['title' => 'Tong Sampah Data (Recycle Bin)'])->name('trash.index');

    // Debugging Menu
    Route::get('/debugging/reset', [DebuggingController::class, 'resetIndex'])->name('debugging.reset');
    });
});
