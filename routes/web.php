<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DataSyncController;
use App\Http\Controllers\ReportingProductGroupController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SalesMapController;



Route::get('/', fn () => redirect()->route('reports.sales.index'));

/*
| Authentication (login-only, no public registration).
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'store'])->name('password.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
| Reporting (requires an authenticated user).
*/
Route::middleware(['auth', \App\Http\Middleware\EnsurePasswordChanged::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Administrator-only user management.
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::get('/reports/sales', [SalesReportController::class, 'index'])
        ->name('reports.sales.index');

    Route::get('/reports/map', [SalesMapController::class, 'index'])
        ->name('reports.map.index');

    Route::get('/reports/product-groups', [ReportingProductGroupController::class, 'index'])
        ->name('reports.product-groups.index');

    Route::post('/reports/product-groups', [ReportingProductGroupController::class, 'store'])
        ->name('reports.product-groups.store');

    Route::get('/reports/product-groups/{productGroup}', [ReportingProductGroupController::class, 'show'])
        ->name('reports.product-groups.show');

    Route::put('/reports/product-groups/{productGroup}', [ReportingProductGroupController::class, 'update'])
        ->name('reports.product-groups.update');

    Route::delete('/reports/product-groups/{productGroup}', [ReportingProductGroupController::class, 'destroy'])
        ->name('reports.product-groups.destroy');

    Route::post('/reports/product-groups/{productGroup}/rules', [ReportingProductGroupController::class, 'storeRule'])
        ->name('reports.product-groups.rules.store');

    Route::put('/reports/product-groups/{productGroup}/rules/{rule}', [ReportingProductGroupController::class, 'updateRule'])
        ->name('reports.product-groups.rules.update');

    Route::delete('/reports/product-groups/{productGroup}/rules/{rule}', [ReportingProductGroupController::class, 'destroyRule'])
        ->name('reports.product-groups.rules.destroy');

    Route::get('/reports/product-groups/{productGroup}/report', [ReportingProductGroupController::class, 'report'])
        ->name('reports.product-groups.report');

    Route::get('/reports/product-groups/{productGroup}/report/export', [ReportingProductGroupController::class, 'exportOrders'])
        ->name('reports.product-groups.report.export');

    Route::post('/reports/resync', [DataSyncController::class, 'resync'])
        ->name('reports.resync');
});
