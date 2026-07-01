<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WebhookController;
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

// Public pages
Route::get('/pricing', function () {
    return Inertia::render('Pricing');
})->name('pricing');

// Omise webhook (no auth, no CSRF)
Route::post('/webhooks/omise', [WebhookController::class, 'omise'])
    ->name('webhooks.omise')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Public Invoice Sharing
Route::get('invoice/share/{token}', [\App\Http\Controllers\InvoiceController::class, 'showPublic'])->name('invoices.show_public');
Route::get('invoice/share/{token}/preview', [\App\Http\Controllers\PdfController::class, 'previewPublic'])->name('invoices.preview_public');
Route::get('tracking/pixel/{token}.gif', [\App\Http\Controllers\TrackingController::class, 'pixel'])->name('tracking.pixel');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/company/wizard', [CompanyController::class, 'wizard'])->name('company.wizard');
    Route::post('/company/wizard', [CompanyController::class, 'store'])->name('company.store');

    Route::middleware([\App\Http\Middleware\EnsureCompanyIsSet::class])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Clients
        Route::resource('clients', ClientController::class);

        // Products
        Route::resource('products', ProductController::class);

        // Invoices
        Route::post('invoices/{invoice}/duplicate', [\App\Http\Controllers\InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
        Route::post('invoices/{invoice}/share', [\App\Http\Controllers\InvoiceController::class, 'share'])->name('invoices.share');
        Route::post('invoices/{invoice}/send', [\App\Http\Controllers\InvoiceController::class, 'sendByEmail'])->name('invoices.send');
        Route::post('invoices/{invoice}/mark-as-paid', [\App\Http\Controllers\InvoiceController::class, 'markAsPaid'])->name('invoices.mark_as_paid');
        Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);

        // PDF
        Route::get('invoices/{invoice}/download', [\App\Http\Controllers\PdfController::class, 'download'])->name('invoices.download');
        Route::get('invoices/{invoice}/preview', [\App\Http\Controllers\PdfController::class, 'preview'])->name('invoices.preview');
        Route::post('invoices/preview', [\App\Http\Controllers\PdfController::class, 'previewDraft'])->name('invoices.preview_draft');

        // API
        Route::get('/api/dbd/lookup/{tax_id}', [\App\Http\Controllers\Api\DbdController::class, 'lookup'])->name('api.dbd.lookup');
        Route::post('/api/ai/suggest-items', [\App\Http\Controllers\Api\AiSuggestController::class, 'suggest'])->name('api.ai.suggest');
        Route::get('/api/clients/{clientId}/top-items', [\App\Http\Controllers\Api\AiSuggestController::class, 'clientTopItems'])->name('api.clients.top-items');
        Route::get('/api/invoices/next-number', [\App\Http\Controllers\InvoiceController::class, 'nextNumber'])->name('api.invoices.next-number');

        // Billing
        Route::get('/settings/billing', [BillingController::class, 'index'])->name('settings.billing');
        Route::post('/settings/billing/upgrade', [BillingController::class, 'upgrade'])->name('settings.billing.upgrade');
        Route::post('/settings/billing/cancel', [BillingController::class, 'cancel'])->name('settings.billing.cancel');

        // Settings
        Route::get('/settings/company', [SettingsController::class, 'company'])->name('settings.company');
        Route::post('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');
        Route::get('/settings/invoice', [SettingsController::class, 'invoice'])->name('settings.invoice');
        Route::patch('/settings/invoice', [SettingsController::class, 'updateInvoice'])->name('settings.invoice.update');
        Route::get('/settings/account', [SettingsController::class, 'account'])->name('settings.account');
        Route::patch('/settings/account', [SettingsController::class, 'updateAccount'])->name('settings.account.update');
        Route::put('/settings/account/password', [SettingsController::class, 'updatePassword'])->name('settings.account.password');
        Route::delete('/settings/account', [SettingsController::class, 'deleteAccount'])->name('settings.account.delete');
    });
});

require __DIR__.'/auth.php';
