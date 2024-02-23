<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResponseController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('v1')->group(function () {
    // Routes for authentication
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', 'logout');
            Route::get('/me', 'me');
        });
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('forms')->group(function () {
            // Routes for forms
            Route::controller(FormController::class)->group(function () {
                Route::post('/', 'create');
                Route::get('/', 'getAll');
                Route::get('/{slug}', 'detail');
            });
            // Routes for questions
            Route::controller(QuestionController::class)->group(function () {
                Route::post('/{slug}/questions', 'create');
                Route::delete('/{slug}/questions/{question_id}', 'delete');
            });
            // Route for answers/responses
            Route::controller(ResponseController::class)->group(function () {
                Route::post('/{slug}/responses', 'create');
            });
        });
    });
});