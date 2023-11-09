<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserConfirmationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/user/confirmation', [AuthController::class, 'activate']);
Route::post('/auth/user/confirmation/resend', [UserConfirmationController::class, 'resend']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::group([
    'middleware' => ['jwt.verify']
], function(){
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', function () {
        return \Illuminate\Support\Facades\Auth::user();
    });
});
