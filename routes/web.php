<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;

// Route::get('/', function () {
//     return view('layout');
// });

Auth::routes();

Route::get('/', [StockController::class, 'index'])->name('stocks.index');
Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');
Route::post('/stocks/{stock}/transactions', [StockController::class, 'addTransaction'])->name('stocks.addTransaction');
Route::post('/stocks/{stock}/add-transaction', [StockController::class, 'addTransaction'])->name('stocks.addTransaction');
Route::post('/stocks/{stock}/add-sell-transaction', [StockController::class, 'addSellTransaction'])->name('stocks.addSellTransaction');
Route::patch('/stocks/{stock}/sell', [StockController::class, 'sellSelectedTransaction'])->name('stocks.sellSelectedTransaction');
Route::delete('/stocks/{id}', [StockController::class, 'destroy'])->name('stocks.destroy');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
