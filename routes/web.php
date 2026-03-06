<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard - weiterleiten je nach Rolle
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('mitarbeiter.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Bereich
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
});

// Mitarbeiter Bereich
Route::middleware(['auth', 'mitarbeiter'])->prefix('mitarbeiter')->name('mitarbeiter.')->group(function () {
    Route::get('/dashboard', function () {
        return view('mitarbeiter.dashboard');
    })->name('dashboard');
});

// Profil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
