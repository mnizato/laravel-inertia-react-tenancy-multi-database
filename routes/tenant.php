<?php

declare(strict_types=1);

use App\Http\Controllers\Tenants\ProfileController;
use App\Http\Controllers\Tenants\Admin\ProfileAdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () { 
    Route::get('/', function () {
        return Inertia::render('Tenants/Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'tenant' => tenant()->id,
        ]);
    });

    Route::get('/dashboard', function () {
        return Inertia::render('Tenants/Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');
    
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth_tenant.php';

    
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->middleware(['auth:admin', 'verifiedadmin'])->name('admin.dashboard');
    
    Route::middleware('auth:admin')->group(function () {
        Route::get('/admin/profile', [ProfileAdminController::class, 'edit'])->name('admin.profile.edit');
        Route::patch('/admin/profile', [ProfileAdminController::class, 'update'])->name('admin.profile.update');
        Route::delete('/admin/profile', [ProfileAdminController::class, 'destroy'])->name('admin.profile.destroy');
    });
    
    
    require __DIR__.'/auth_admin.php';

});