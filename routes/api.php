<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Example public rates endpoint (rate-limited)
Route::get('/rates', [CurrencyController::class, 'getLiveRates'])->middleware('throttle:currency');

// Example conversion endpoint (sensitive, lower rate limit)
Route::post('/convert', [CurrencyController::class, 'convert'])->middleware('throttle:sensitive');
