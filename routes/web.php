<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'index'])->name('dashboard');
Route::get('/completed', [TaskController::class, 'index'])->name('tasks.completed');
Route::patch('/tasks/{task}/status', [TaskController::class, 'status'])->name('tasks.status');
Route::resource('tasks', TaskController::class);
