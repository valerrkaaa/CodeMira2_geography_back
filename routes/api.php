<?php

use App\Http\Controllers\HomeworkController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PupilsController;
use Illuminate\Http\Request;
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

Route::group([
    'middleware' => 'api'
], function ($router) {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});

Route::group([
    'middleware' => ['api', 'role:teacher'],
], function ($router) {
    Route::get('classes', [PupilsController::class, 'getClassesList']);
    Route::get('pupils', [PupilsController::class, 'getPupilList']);
    Route::get('pupil', [PupilsController::class, 'getPupil']);
    Route::get('homework', [HomeworkController::class, 'getHomework']);
});

Route::group([
    'middleware' => 'api'
], function ($router) {
    Route::get('lesson', [LessonController::class, 'getLesson']);
});