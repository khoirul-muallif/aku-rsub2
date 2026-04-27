<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LaporanController;

Route::middleware(['auth'])->group(function () {
    Route::get('/laporan/laba-rugi/pdf', [LaporanController::class, 'labaRugiPdf'])->name('laporan.laba-rugi.pdf');
    Route::get('/laporan/laba-rugi/excel', [LaporanController::class, 'labaRugiExcel'])->name('laporan.laba-rugi.excel');
    Route::get('/laporan/neraca/pdf', [LaporanController::class, 'neracaPdf'])->name('laporan.neraca.pdf');
    Route::get('/laporan/neraca/excel', [LaporanController::class, 'neracaExcel'])->name('laporan.neraca.excel');
    Route::get('/laporan/buku-besar/pdf', [LaporanController::class, 'bukuBesarPdf'])->name('laporan.buku-besar.pdf');
    Route::get('/laporan/cash-flow/pdf', [LaporanController::class, 'cashFlowPdf'])->name('laporan.cash-flow.pdf');
    Route::get('/laporan/aging/pdf', [LaporanController::class, 'agingPdf'])->name('laporan.aging.pdf');
    Route::get('/laporan/neraca-lajur/pdf', [LaporanController::class, 'neracaLajurPdf'])->name('laporan.neraca-lajur.pdf');
});