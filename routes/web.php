<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect root to converter
Route::get('/', function () {
    return redirect()->route('currency.converter');
});

// Currency Routes
Route::prefix('currency')->name('currency.')->middleware(['throttle:currency', 'global.block'])->group(function () {
    // Converter
    Route::get('/converter', [CurrencyController::class, 'converter'])->name('converter');
    Route::post('/convert', [CurrencyController::class, 'convert'])->name('convert')->middleware('throttle:sensitive');
    
    // Historical Data
    Route::get('/historical', [CurrencyController::class, 'historical'])->name('historical');
    Route::post('/historical/data', [CurrencyController::class, 'getHistorical'])->name('historical.data')->middleware('throttle:sensitive');
    
    // Trend Analysis
    Route::get('/trend-analysis', [CurrencyController::class, 'trendAnalysis'])->name('trend-analysis');
    Route::post('/trend/data', [CurrencyController::class, 'getTrend'])->name('trend.data')->middleware('throttle:sensitive');
    
    // Multi-Currency Comparison
    Route::get('/multi-currency', [CurrencyController::class, 'multiCurrency'])->name('multi-currency');
    Route::post('/compare', [CurrencyController::class, 'compareMultiple'])->name('compare')->middleware('throttle:sensitive');
    
    // Dashboard
    Route::get('/dashboard', [CurrencyController::class, 'dashboard'])->name('dashboard');
    Route::get('/live-rates', [CurrencyController::class, 'getLiveRates'])->name('live-rates');
});
