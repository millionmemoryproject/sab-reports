<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataSyncController;
use App\Http\Controllers\ReportingProductGroupController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\StripeTransactionController;
use App\Http\Controllers\SalesMapController;



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

Route::delete('/reports/product-groups/{productGroup}/rules/{rule}', [ReportingProductGroupController::class, 'destroyRule'])
    ->name('reports.product-groups.rules.destroy');

Route::post('/reports/resync', [DataSyncController::class, 'resync'])
    ->name('reports.resync');

    Route::get('/reports/product-groups/{productGroup}/report', [ReportingProductGroupController::class, 'report'])
    ->name('reports.product-groups.report');