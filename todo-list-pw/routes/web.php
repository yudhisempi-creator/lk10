<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Todo\TodoController;

// Route Welcome/Home
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Route Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Route Todo (Protected - Hanya untuk user yang sudah login)
Route::middleware('auth')->group(function () {
    Route::get('/todo', [TodoController::class, 'index'])->name('todo');
    Route::post('/todo', [TodoController::class, 'store'])->name('todo.store');
    // Route model binding: Laravel otomatis inject Todo model berdasarkan parameter {todo}
    Route::put('/todo/{todo}', [TodoController::class, 'update'])->name('todo.update');
    Route::delete('/todo/{todo}', [TodoController::class, 'destroy'])->name('todo.destroy');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});