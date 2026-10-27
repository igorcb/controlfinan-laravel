<?php

use App\Http\Controllers\CurrentAccountsController;
use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\PasswordsController;
use App\Http\Controllers\RegistrationsController;
use App\Http\Controllers\SessionsController;
use Illuminate\Support\Facades\Route;

// Same URLs as the Rails app (resource :session, resources :current_accounts, ...).
Route::middleware('authenticate')->group(function () {
    Route::get('/session/new', [SessionsController::class, 'create']);
    Route::post('/session', [SessionsController::class, 'store'])->middleware('rate_limit:sessions,/session/new');
    Route::get('/registration/new', [RegistrationsController::class, 'create']);
    Route::post('/registration', [RegistrationsController::class, 'store'])->middleware('rate_limit:registrations,/registration/new');
    Route::get('/passwords/new', [PasswordsController::class, 'create']);
    Route::post('/passwords', [PasswordsController::class, 'store'])->middleware('rate_limit:passwords,/passwords/new');
    Route::get('/passwords/{token}/edit', [PasswordsController::class, 'edit']);
    Route::match(['put', 'patch'], '/passwords/{token}', [PasswordsController::class, 'update']);
});

Route::middleware('authenticate:required')->group(function () {
    Route::delete('/session', [SessionsController::class, 'destroy']);

    Route::get('/', [CurrentAccountsController::class, 'index']);
    Route::get('/current_accounts.json', [CurrentAccountsController::class, 'index'])->defaults('format', 'json');
    Route::get('/current_accounts', [CurrentAccountsController::class, 'index']);
    Route::post('/current_accounts', [CurrentAccountsController::class, 'store']);
    Route::get('/current_accounts/new', [CurrentAccountsController::class, 'create']);
    Route::get('/current_accounts/{id}.json', [CurrentAccountsController::class, 'show'])->defaults('format', 'json');
    Route::get('/current_accounts/{id}', [CurrentAccountsController::class, 'show']);
    Route::get('/current_accounts/{id}/edit', [CurrentAccountsController::class, 'edit']);
    Route::match(['put', 'patch'], '/current_accounts/{id}', [CurrentAccountsController::class, 'update']);
    Route::delete('/current_accounts/{id}', [CurrentAccountsController::class, 'destroy']);

    Route::get('/dashboard', [DashboardsController::class, 'show']);
});

// Legacy daily balance search, now a filter on the listing.
Route::get('/search_account', fn () => redirect('/current_accounts?date='.request('date_calendar_now', ''), 301));
