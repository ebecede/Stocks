<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;

Auth::routes();  // Pastikan autentikasi sudah diaktifkan

// Halaman utama (stock index) dan halaman terkait hanya dapat diakses oleh pengguna yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('stocks.index');
    Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');
    Route::post('/stocks/{stock}/transactions', [StockController::class, 'addTransaction'])->name('stocks.addTransaction');
    Route::post('/stocks/{stock}/add-transaction', [StockController::class, 'addTransaction'])->name('stocks.addTransaction');
    Route::post('/stocks/{stock}/add-sell-transaction', [StockController::class, 'addSellTransaction'])->name('stocks.addSellTransaction');
    Route::patch('/stocks/{stock}/sell', [StockController::class, 'sellSelectedTransaction'])->name('stocks.sellSelectedTransaction');
    Route::delete('/stocks/{id}', [StockController::class, 'destroy'])->name('stocks.destroy');
});

// Halaman home (bisa diakses tanpa login)
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
