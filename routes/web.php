<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::post('/user/toggle-auto-trade', [DashboardController::class, 'toggleAutoTrade'])->name('user.toggleAutoTrade')->middleware('auth');
Route::post('/sell/{coin}', [DashboardController::class, 'sell'])->name('user.sell');

Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('/wallet/reload', [WalletController::class, 'reload'])->name('wallet.reload');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
