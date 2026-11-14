<?php

use App\Http\Controllers\Access\AccountController;
use App\Http\Controllers\Access\AuthController;
use App\Http\Controllers\Access\UserController;
use App\Http\Controllers\Clients\ClientController;
use App\Http\Controllers\Events\EventController;
use App\Http\Controllers\Events\EventProviderAssignmentController;
use App\Http\Controllers\Events\TaskController;
use App\Http\Controllers\Providers\ProviderController;
use App\Http\Controllers\Venues\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| M1 — Authentication routes (public)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated routes (M1 dashboard + M2 clients + M1 user management)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('access.dashboard');
    })->name('dashboard');

    // Home redirect
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // M2 — Clients: read for all authenticated users
    // Note: 'create' route must be before '{client}' to avoid conflict
    Route::get('/clients/create', [ClientController::class, 'create'])
        ->middleware('permission:administrateur,charge_evenements')
        ->name('clients.create');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    // M2 — Clients: write only for admin + charge_evenements (RG17)
    Route::middleware('permission:administrateur,charge_evenements')->group(function () {
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::patch('/clients/{client}', [ClientController::class, 'update']);
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    });

    // M4 — Providers: read for all authenticated users
    // Note: 'create' route must be before '{provider}' to avoid conflict
    Route::get('/providers/create', [ProviderController::class, 'create'])
        ->middleware('permission:administrateur,charge_evenements')
        ->name('providers.create');
    Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
    Route::get('/providers/{provider}', [ProviderController::class, 'show'])->name('providers.show');

    // M4 — Providers: write only for admin + charge_evenements (RG17)
    Route::middleware('permission:administrateur,charge_evenements')->group(function () {
        Route::post('/providers', [ProviderController::class, 'store'])->name('providers.store');
        Route::get('/providers/{provider}/edit', [ProviderController::class, 'edit'])->name('providers.edit');
        Route::put('/providers/{provider}', [ProviderController::class, 'update'])->name('providers.update');
        Route::patch('/providers/{provider}', [ProviderController::class, 'update']);
        Route::delete('/providers/{provider}', [ProviderController::class, 'destroy'])->name('providers.destroy');
    });

    // M3 — Venues: read for all authenticated users
    // Note: 'create' route must be before '{venue}' to avoid conflict
    Route::get('/venues/create', [VenueController::class, 'create'])
        ->middleware('permission:administrateur,charge_evenements')
        ->name('venues.create');
    Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/{venue}', [VenueController::class, 'show'])->name('venues.show');

    // M3 — Venues: write only for admin + charge_evenements (RG17)
    Route::middleware('permission:administrateur,charge_evenements')->group(function () {
        Route::post('/venues', [VenueController::class, 'store'])->name('venues.store');
        Route::get('/venues/{venue}/edit', [VenueController::class, 'edit'])->name('venues.edit');
        Route::put('/venues/{venue}', [VenueController::class, 'update'])->name('venues.update');
        Route::patch('/venues/{venue}', [VenueController::class, 'update']);
        Route::delete('/venues/{venue}', [VenueController::class, 'destroy'])->name('venues.destroy');
    });

    // M5 — Events: read for all authenticated users
    // Note: 'create' route must be before '{event}' to avoid conflict
    Route::get('/events/create', [EventController::class, 'create'])
        ->middleware('permission:administrateur,charge_evenements')
        ->name('events.create');
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

    // M5 — Events: write only for admin + charge_evenements (RG17)
    Route::middleware('permission:administrateur,charge_evenements')->group(function () {
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::patch('/events/{event}', [EventController::class, 'update']);
        Route::patch('/events/{event}/confirm', [EventController::class, 'confirm'])->name('events.confirm');
        Route::post('/events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');

        // M6 — Provider assignments
        Route::post('/events/{event}/assignments', [EventProviderAssignmentController::class, 'store'])->name('events.assignments.store');
        Route::put('/events/{event}/assignments/{assignment}', [EventProviderAssignmentController::class, 'update'])->name('events.assignments.update');
        Route::delete('/events/{event}/assignments/{assignment}', [EventProviderAssignmentController::class, 'destroy'])->name('events.assignments.destroy');

        // M7 — Tasks
        Route::post('/events/{event}/tasks', [TaskController::class, 'store'])->name('events.tasks.store');
        Route::put('/events/{event}/tasks/{task}', [TaskController::class, 'update'])->name('events.tasks.update');
        Route::patch('/events/{event}/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('events.tasks.toggle');
        Route::delete('/events/{event}/tasks/{task}', [TaskController::class, 'destroy'])->name('events.tasks.destroy');
    });

    // M1 — Mon compte (tous les rôles) : profil et changement de mot de passe
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('account.password');

    // M1 — User management (admin only)
    Route::middleware('permission:administrateur')->group(function () {
        Route::resource('users', UserController::class);
    });
});
