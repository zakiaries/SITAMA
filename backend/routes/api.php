<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Common
    Route::post('/updateProfile', [ProfileController::class, 'updatePhoto']);
    Route::post('/resetPassword', [ProfileController::class, 'resetPassword']);

    // Student
    Route::prefix('student')->group(function () {
        Route::get('/home', [StudentController::class, 'home']);
        Route::get('/profile', [StudentController::class, 'profile']);

        Route::get('/guidance', [StudentController::class, 'getGuidances']);
        Route::post('/guidance', [StudentController::class, 'addGuidance']);
        Route::post('/guidance/{id}', [StudentController::class, 'editGuidance']); // FormData sends _method=PUT
        Route::delete('/guidance/{id}', [StudentController::class, 'deleteGuidance']);

        Route::get('/logBook', [StudentController::class, 'getLogBooks']);
        Route::post('/logBook', [StudentController::class, 'addLogBook']);
        Route::post('/logBook/{id}', [StudentController::class, 'editLogBook']); // FormData sends _method=PUT
        Route::delete('/logBook/{id}', [StudentController::class, 'deleteLogBook']);

        Route::get('/notification', [StudentController::class, 'getNotifications']);
    });

    Route::put('/notification/markAsRead', [StudentController::class, 'markAllAsRead']);

    // Lecturer
    Route::prefix('lecturer')->group(function () {
        Route::get('/home', [LecturerController::class, 'home']);
        Route::get('/profile', [LecturerController::class, 'profile']);

        Route::get('/detailStudent/{id}', [LecturerController::class, 'detailStudent']);
        Route::put('/guidance/{id}', [LecturerController::class, 'updateGuidanceStatus']);
        Route::put('/logBook/{id}', [LecturerController::class, 'updateLogBookNote']);

        Route::get('/assessments/{id}', [LecturerController::class, 'getAssessments']);
        Route::post('/addAssessment/{id}', [LecturerController::class, 'submitScores']);

        Route::put('/finishedStudent/{id}', [LecturerController::class, 'finishStudent']);
        Route::post('/notification', [LecturerController::class, 'addNotification']);
    });
});
