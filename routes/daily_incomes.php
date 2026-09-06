<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DailyIncomeController;

Route::middleware(['auth'])->group(function () {
    Route::get('/daily-incomes', [DailyIncomeController::class, 'index'])->name('daily-incomes.index');
    Route::get('/daily-incomes/create', [DailyIncomeController::class, 'create'])->name('daily-incomes.create');
    Route::get('/daily-incomes/check-date', [DailyIncomeController::class, 'checkDate'])->name('daily-incomes.check-date');
    Route::get('/daily-incomes/movements', [DailyIncomeController::class, 'getMovements'])->name('daily-incomes.movements');
    Route::get('/daily-incomes/dni-movements', [DailyIncomeController::class, 'dniMovements'])->name('daily-incomes.dni-movements');
    Route::post('/daily-incomes', [DailyIncomeController::class, 'store'])->name('daily-incomes.store');
    Route::delete('/daily-incomes/{dailyIncome}', [DailyIncomeController::class, 'destroy'])->name('daily-incomes.destroy');
    Route::delete('/daily-incomes/{dailyIncome}/details/{dailyIncomeDetail}', [DailyIncomeController::class, 'removeMovement'])->name('daily-incomes.details.destroy');
});
