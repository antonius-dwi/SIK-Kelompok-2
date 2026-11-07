<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Applicant;
use App\Http\Controllers\Approver;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Management;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SystemAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SIK - Sistem Izin Kerja Sisi Udara (Bandara Ahmad Yani)
|--------------------------------------------------------------------------
| Salin isi file ini ke routes/web.php. Middleware alias `role` harus
| didaftarkan di bootstrap/app.php (lihat README).
*/

// Tamu
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::view('/lupa-kata-sandi', 'auth.password')->name('password.help');
});

// Pengguna masuk
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('home');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Notifikasi (per pengguna)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');

    // Berkas privat (otorisasi per pengajuan di controller/policy)
    Route::get('/documents/{document}', [DocumentController::class, 'document'])->name('documents.show');
    Route::get('/item-photos/{item}', [DocumentController::class, 'itemPhoto'])->name('items.photo');

    // Applicant
    Route::middleware('role:applicant')->prefix('pemohon')->name('applicant.')->group(function () {
        Route::get('/', [Applicant\ApplicationController::class, 'dashboard'])->name('dashboard');
        Route::get('/pengajuan/baru', [Applicant\ApplicationController::class, 'create'])->name('applications.create');
        Route::post('/pengajuan', [Applicant\ApplicationController::class, 'store'])->name('applications.store');
        Route::get('/pengajuan/{application}', [Applicant\ApplicationController::class, 'show'])->name('applications.show');
        Route::get('/pengajuan/{application}/ubah', [Applicant\ApplicationController::class, 'edit'])->name('applications.edit');
        Route::put('/pengajuan/{application}', [Applicant\ApplicationController::class, 'update'])->name('applications.update');
        Route::get('/pengajuan/{application}/sik', [Applicant\ApplicationController::class, 'permit'])->name('applications.permit');
        Route::get('/pengajuan/{application}/sik/pdf', [Applicant\ApplicationController::class, 'permitPdf'])->name('applications.permit.pdf');
    });

    // Admin / Verifier
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/antrean', [Admin\QueueController::class, 'index'])->name('queues');

        Route::post('/pengajuan/{application}/verifikasi/mulai', [Admin\VerificationController::class, 'start'])->name('verification.start');
        Route::get('/pengajuan/{application}/verifikasi', [Admin\VerificationController::class, 'show'])->name('verification.show');
        Route::post('/pengajuan/{application}/verifikasi', [Admin\VerificationController::class, 'process'])->name('verification.process');

        Route::get('/pengajuan/{application}/koreksi', [Admin\CorrectionController::class, 'edit'])->name('correction.edit');
        Route::put('/pengajuan/{application}/koreksi', [Admin\CorrectionController::class, 'update'])->name('correction.update');
        Route::post('/pengajuan/{application}/koreksi/tutup', [Admin\CorrectionController::class, 'close'])->name('correction.close');

        Route::get('/pengajuan/{application}/terbitkan', [Admin\IssuanceController::class, 'create'])->name('issuance.create');
        Route::post('/pengajuan/{application}/terbitkan', [Admin\IssuanceController::class, 'store'])->name('issuance.store');
        Route::get('/pengajuan/{application}/sik', [Applicant\ApplicationController::class, 'permit'])->name('permit');
        Route::get('/pengajuan/{application}/sik/pdf', [Applicant\ApplicationController::class, 'permitPdf'])->name('permit.pdf');
    });

    // Approver
    Route::middleware('role:approver')->prefix('approver')->name('approver.')->group(function () {
        Route::get('/antrean', [Approver\ApprovalController::class, 'index'])->name('queue');
        Route::get('/pengajuan/{application}', [Approver\ApprovalController::class, 'show'])->name('show');
        Route::post('/pengajuan/{application}/keputusan', [Approver\ApprovalController::class, 'decide'])->name('decide');
    });

    // Management (hanya-baca)
    Route::middleware('role:management')->prefix('manajemen')->name('management.')->group(function () {
        Route::get('/', [Management\MonitoringController::class, 'index'])->name('index');
        Route::get('/pengajuan/{application}', [Management\MonitoringController::class, 'show'])->name('show');
    });

    // System administrator
    Route::middleware('role:system-admin')->prefix('sistem')->name('system-admin.')->group(function () {
        // Halaman gabungan lama (kompatibilitas tautan/anchor yang sudah ada).
        Route::get('/', [SystemAdmin\MasterDataController::class, 'index'])->name('index');
        Route::get('/dashboard', [SystemAdmin\DashboardController::class, 'index'])->name('dashboard');

        // Halaman terpisah per modul (navigasi sidebar).
        Route::get('/pengguna', [SystemAdmin\UserController::class, 'index'])->name('users.index');
        Route::get('/peran', [SystemAdmin\RoleController::class, 'index'])->name('roles.index');
        Route::get('/departemen', [SystemAdmin\DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/area-kerja', [SystemAdmin\AreaController::class, 'index'])->name('areas.index');
        Route::get('/jenis-pekerjaan', [SystemAdmin\WorkTypeController::class, 'index'])->name('work-types.index');
        Route::get('/template-alur', [SystemAdmin\WorkflowTemplateController::class, 'index'])->name('workflows.index');
        Route::get('/langkah-persetujuan', [SystemAdmin\WorkflowTemplateController::class, 'steps'])->name('steps.index');
        Route::get('/log-aktivitas', [SystemAdmin\AuditLogController::class, 'index'])->name('audit.index');

        // Rekap SIK: daftar per periode + unduhan per dokumen & massal (ZIP).
        Route::get('/rekap-sik', [SystemAdmin\RecapController::class, 'index'])->name('recap.index');
        Route::get('/rekap-sik/unduh', [SystemAdmin\RecapController::class, 'downloadZip'])->name('recap.zip');
        Route::get('/rekap-sik/{application}/pdf', [SystemAdmin\RecapController::class, 'downloadPdf'])
            ->whereNumber('application')->name('recap.pdf');

        foreach ([
            'areas' => SystemAdmin\AreaController::class,
            'work-types' => SystemAdmin\WorkTypeController::class,
            'departments' => SystemAdmin\DepartmentController::class,
        ] as $slug => $controller) {
            Route::post("/{$slug}", [$controller, 'store'])->name("{$slug}.store");
            Route::put("/{$slug}/{id}", [$controller, 'update'])->whereNumber('id')->name("{$slug}.update");
            Route::patch("/{$slug}/{id}/toggle", [$controller, 'toggle'])->whereNumber('id')->name("{$slug}.toggle");
            Route::delete("/{$slug}/{id}", [$controller, 'destroy'])->whereNumber('id')->name("{$slug}.destroy");
        }

        Route::post('/users', [SystemAdmin\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [SystemAdmin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle', [SystemAdmin\UserController::class, 'toggle'])->name('users.toggle');
        Route::delete('/users/{user}', [SystemAdmin\UserController::class, 'destroy'])->name('users.destroy');
        Route::delete('/peran/{user}', [SystemAdmin\RoleController::class, 'revoke'])->name('roles.revoke');

        Route::post('/workflows', [SystemAdmin\WorkflowTemplateController::class, 'store'])->name('workflows.store');
        Route::put('/workflows/{workflow}', [SystemAdmin\WorkflowTemplateController::class, 'update'])->name('workflows.update');
        Route::patch('/workflows/{workflow}/toggle', [SystemAdmin\WorkflowTemplateController::class, 'toggle'])->name('workflows.toggle');
    });
});

/*
|--------------------------------------------------------------------------
| Verifikasi dokumen SIK (publik, hanya-baca)
|--------------------------------------------------------------------------
| Dipakai sebagai tujuan QR code pada surat SIK. Token = nomor SIK dengan
| "/" diganti "-" (mis. SIK-AYANI-2026-004). Hanya dokumen berstatus ISSUED
| yang ditampilkan; data personel tidak pernah ditampilkan.
*/
Route::get('/verifikasi/{token}', [App\Http\Controllers\PermitVerificationController::class, 'show'])
    ->where('token', '[A-Za-z0-9\-]+')
    ->name('permits.verify');
