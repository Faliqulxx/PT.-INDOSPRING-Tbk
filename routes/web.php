<?php

use App\Http\Controllers\LaporanController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/transaksi');

Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
Route::get('/transaksi/{no}', [TransaksiController::class, 'show'])->name('transaksi.show');
Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
Route::put('/transaksi/{no}', [TransaksiController::class, 'update'])->name('transaksi.update');
Route::delete('/transaksi/{no}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');

Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
