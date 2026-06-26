<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Todo\TodoController;

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

// Endpoint untuk mengambil data Todos (Secured with auth middleware)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/todos', [TodoController::class, 'apiIndex']);
});

