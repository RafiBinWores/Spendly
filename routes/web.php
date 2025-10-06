<?php

use App\Http\Controllers\ReportController;
use App\Livewire\Category\CategoryIndex;
use App\Livewire\Dashboard;
use App\Livewire\Expense\ExpenseIndex;
use App\Livewire\Income\IncomeIndex;
use App\Livewire\Reports\Transactions;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\SubCategory\SubCategoryIndex;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', Dashboard::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');


    // Categories
    Route::get('categories', CategoryIndex::class)->name('categories.index');

    // Sub Categories
    Route::get('sub-categories', SubCategoryIndex::class)->name('subCategories.index');

    // Income routes
    Route::get('incomes', IncomeIndex::class)->name('incomes.index');

    // expense routes
    Route::get('expenses', ExpenseIndex::class)->name('expenses.index');

    // Filter page
    Route::get('/transactions', Transactions::class)->name('reports.transactions');

    Route::get('/transactions/export/excel', [ReportController::class, 'exportExcel'])
        ->name('transactions.export_excel');
    Route::get('/transactions/export/pdf', [ReportController::class, 'exportPdf'])
        ->name('transactions.export_pdf');
});

require __DIR__ . '/auth.php';
