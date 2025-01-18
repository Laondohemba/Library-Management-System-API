<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\UserAPIAuth;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/user')->group(function(){
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware(UserAPIAuth::class)->prefix('user')->group(function(){
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::post('admin/login', [AdminController::class, 'login']);
Route::post('/add', [AdminController::class, 'store']);

Route::middleware(AdminAuth::class)->prefix('admin')->group(function(){
    Route::post('/logout', [AdminController::class, 'logout']);
});

