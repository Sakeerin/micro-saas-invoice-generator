<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/company/wizard', [CompanyController::class, 'wizard'])->name('company.wizard');
    Route::post('/company/wizard', [CompanyController::class, 'store'])->name('company.store');

    Route::middleware([\App\Http\Middleware\EnsureCompanyIsSet::class])->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Clients
        Route::resource('clients', ClientController::class);

        // Products
        Route::resource('products', ProductController::class);

        // Invoices
        Route::post('invoices/{invoice}/duplicate', [\App\Http\Controllers\InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
        Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);

        // PDF
        Route::get('invoices/{invoice}/download', [\App\Http\Controllers\PdfController::class, 'download'])->name('invoices.download');
        Route::get('invoices/{invoice}/preview', [\App\Http\Controllers\PdfController::class, 'preview'])->name('invoices.preview');

        // API
        Route::get('/api/dbd/lookup/{tax_id}', [\App\Http\Controllers\Api\DbdController::class, 'lookup'])->name('api.dbd.lookup');
    });
});

require __DIR__.'/auth.php';
