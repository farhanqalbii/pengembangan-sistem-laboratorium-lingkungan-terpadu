<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KeuanganController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LogAktivitasController;
use App\Http\Controllers\Admin\ParameterController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\KepuasanPelangganController as CustomerKepuasanPelangganController;
use App\Http\Controllers\Customer\PembayaranController as CustomerPembayaranController;
use App\Http\Controllers\Customer\PeminjamanController as CustomerPeminjamanController;
use App\Http\Controllers\Customer\SampleController as CustomerSampleController;
use App\Http\Controllers\Customer\SertifikatController as CustomerSertifikatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\VerifikasiController as ManagerVerifikasiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PeralatanScanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\PeminjamanController as StaffPeminjamanController;
use App\Http\Controllers\Staff\PeralatanController as StaffPeralatanController;
use App\Http\Controllers\Staff\SampleController as StaffSampleController;
use Illuminate\Support\Facades\Route;

// Landing Page & Public Services
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/track-sample', [HomeController::class, 'trackSample'])->name('sample.track');
Route::get('/verify-certificate/{hash?}', [CertificateVerificationController::class, 'verify'])->name('certificate.verify');
Route::post('/payment/webhook', [PaymentWebhookController::class, 'handle'])->name('payment.webhook');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Password Reset Routes (AUT-F-003)
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public Scan & Detail Routes
Route::get('/equipment/scan', [PeralatanScanController::class, 'scan'])->name('equipment.scan');
Route::get('/equipment/detail/{code}', [PeralatanScanController::class, 'detail'])->name('equipment.detail');

// Shared Auth Routes (Notifications & Profile)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/counts', [NotificationController::class, 'getCounts'])->name('notifications.counts');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::match(['get', 'post'], '/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// CUSTOMER ROUTES
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Samples
    Route::get('/samples', [CustomerSampleController::class, 'index'])->name('samples.index');
    Route::get('/samples/create', [CustomerSampleController::class, 'create'])->name('samples.create');
    Route::post('/samples', [CustomerSampleController::class, 'store'])->name('samples.store');
    Route::get('/samples/{sample}', [CustomerSampleController::class, 'show'])->name('samples.show');

    // Borrowings
    Route::get('/borrowings', [CustomerPeminjamanController::class, 'index'])->name('borrowings.index');
    Route::get('/borrowings/create', [CustomerPeminjamanController::class, 'create'])->name('borrowings.create');
    Route::post('/borrowings', [CustomerPeminjamanController::class, 'store'])->name('borrowings.store');
    Route::get('/borrowings/{borrowing}', [CustomerPeminjamanController::class, 'show'])->name('borrowings.show');
    Route::post('/borrowings/{borrowing}/extend', [CustomerPeminjamanController::class, 'requestExtension'])->name('borrowings.extend');

    // Certificates
    Route::get('/certificates', [CustomerSertifikatController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}', [CustomerSertifikatController::class, 'show'])->name('certificates.show');
    Route::get('/certificates/{certificate}/download', [CustomerSertifikatController::class, 'download'])
        ->withoutMiddleware('role:customer')
        ->name('certificates.download');

    // Payments & Invoices
    Route::get('/payments', [CustomerPembayaranController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/process', [CustomerPembayaranController::class, 'process'])->name('payments.process');
    Route::post('/payments/{payment}/check-status', [CustomerPembayaranController::class, 'checkStatus'])->name('payments.check-status');
    Route::get('/invoices/{invoice}/download', [CustomerPembayaranController::class, 'downloadInvoice'])
        ->withoutMiddleware('role:customer')
        ->name('invoices.download');

    // Satisfaction Feedback
    Route::get('/satisfaction', [CustomerKepuasanPelangganController::class, 'create'])->name('satisfaction.create');
    Route::post('/satisfaction', [CustomerKepuasanPelangganController::class, 'store'])->name('satisfaction.store');
});

// STAFF / ANALYST ROUTES
Route::middleware(['auth', 'role:staff,admin'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

    // Samples
    Route::get('/samples', [StaffSampleController::class, 'index'])->name('samples.index');
    Route::get('/samples/{sample}', [StaffSampleController::class, 'show'])->name('samples.show');
    Route::post('/samples/{sample}/receive', [StaffSampleController::class, 'receive'])->name('samples.receive');
    Route::post('/samples/{sample}/assign', [StaffSampleController::class, 'assign'])->name('samples.assign');
    Route::get('/samples/{sample}/input-results', [StaffSampleController::class, 'editResults'])->name('samples.input-results');
    Route::post('/samples/{sample}/store-results', [StaffSampleController::class, 'storeResults'])->name('samples.store-results');

    // Equipment & Inventory
    Route::resource('equipment', StaffPeralatanController::class);
    Route::post('/equipment/{equipment}/calibration', [StaffPeralatanController::class, 'recordCalibration'])->name('equipment.calibration');
    Route::post('/equipment/{equipment}/service', [StaffPeralatanController::class, 'recordService'])->name('equipment.service');

    // Borrowings Return & Approval Processing
    Route::get('/borrowings', [StaffPeminjamanController::class, 'index'])->name('borrowings.index');
    Route::get('/borrowings/{borrowing}', [StaffPeminjamanController::class, 'show'])->name('borrowings.show');
    Route::post('/borrowings/{borrowing}/approve', [StaffPeminjamanController::class, 'approve'])->name('borrowings.approve');
    Route::post('/borrowings/{borrowing}/reject', [StaffPeminjamanController::class, 'reject'])->name('borrowings.reject');
    Route::post('/borrowings/{borrowing}/return', [StaffPeminjamanController::class, 'processReturn'])->name('borrowings.return');
});

// MANAGER LAB ROUTES
Route::middleware(['auth', 'role:manager,admin'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');

    // Verifications (GATE for COA generation!)
    Route::get('/verifications', [ManagerVerifikasiController::class, 'index'])->name('verifications.index');
    Route::get('/verifications/{sample}', [ManagerVerifikasiController::class, 'show'])->name('verifications.show');
    Route::post('/verifications/{sample}/approve', [ManagerVerifikasiController::class, 'approve'])->name('verifications.approve');
    Route::post('/verifications/{sample}/reject', [ManagerVerifikasiController::class, 'reject'])->name('verifications.reject');

    // Rekap Kepuasan Pelanggan (SRS FDB-F-002: Manajer Lab dan Admin)
    Route::get('/reports/satisfaction', [LaporanController::class, 'satisfaction'])->name('reports.satisfaction');
});

// ADMIN ROUTES
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserController::class)->except(['show']);

    // Equipment Master (Admin link to staff equipment)
    Route::get('/equipment', [StaffPeralatanController::class, 'index'])->name('equipment.index');

    // Borrowing Approval & Extension
    Route::get('/borrowings', [AdminPeminjamanController::class, 'index'])->name('borrowings.index');
    Route::get('/borrowings/{borrowing}', [AdminPeminjamanController::class, 'show'])->name('borrowings.show');
    Route::post('/borrowings/{borrowing}/approve', [AdminPeminjamanController::class, 'approve'])->name('borrowings.approve');
    Route::post('/borrowings/{borrowing}/reject', [AdminPeminjamanController::class, 'reject'])->name('borrowings.reject');
    Route::post('/borrowings/{borrowing}/approve-extension', [AdminPeminjamanController::class, 'approveExtension'])->name('borrowings.approve-extension');

    // Finance & Deposit Refund
    Route::get('/finance', [KeuanganController::class, 'index'])->name('finance.index');
    Route::post('/finance/refund/{borrowing}', [KeuanganController::class, 'processRefund'])->name('finance.refund');

    // Parameters & Baku Mutu Master
    Route::resource('parameters', ParameterController::class)->except(['show']);

    // Activity Logs
    Route::get('/activity-logs', [LogAktivitasController::class, 'index'])->name('activity-logs.index');

    // Reports Module
    Route::get('/reports/samples', [LaporanController::class, 'samples'])->name('reports.samples');
    Route::get('/reports/inventory', [LaporanController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/borrowings', [LaporanController::class, 'borrowings'])->name('reports.borrowings');
    Route::get('/reports/financial', [LaporanController::class, 'financial'])->name('reports.financial');
    Route::get('/reports/satisfaction', [LaporanController::class, 'satisfaction'])->name('reports.satisfaction');
});
