<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SeminarController;
use App\Http\Controllers\IndustriController;
use App\Http\Controllers\KaprodiController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\LecturerIndustryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Public job listings (viewed by students)
Route::get('/job-listings', [IndustriController::class, 'getPublicListings']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Common — any authenticated role
    Route::post('/updateProfile', [ProfileController::class, 'updatePhoto']);
    Route::post('/resetPassword', [ProfileController::class, 'resetPassword']);

    // Seminars — student
    Route::middleware('role:student')->group(function () {
        Route::get('/seminars', [SeminarController::class, 'index']);
        Route::post('/seminars/{id}/register', [SeminarController::class, 'register']);
    });

    // Student only
    Route::middleware('role:student')->group(function () {
        Route::prefix('student')->group(function () {
            Route::get('/home', [StudentController::class, 'home']);
            Route::get('/profile', [StudentController::class, 'profile']);

            Route::get('/guidance', [StudentController::class, 'getGuidances']);
            Route::post('/guidance', [StudentController::class, 'addGuidance']);
            Route::post('/guidance/{id}', [StudentController::class, 'editGuidance']);
            Route::delete('/guidance/{id}', [StudentController::class, 'deleteGuidance']);
            Route::get('/guidance/{id}/download', [StudentController::class, 'downloadGuidanceFile']);

            Route::get('/logBook', [StudentController::class, 'getLogBooks']);
            Route::post('/logBook', [StudentController::class, 'addLogBook']);
            Route::post('/logBook/{id}', [StudentController::class, 'editLogBook']);
            Route::delete('/logBook/{id}', [StudentController::class, 'deleteLogBook']);

            Route::get('/notification', [StudentController::class, 'getNotifications']);
        });

        Route::put('/notification/markAsRead', [StudentController::class, 'markAllAsRead']);
    });

    // Lecturer Industry only
    Route::middleware('role:lecturer_industry')->prefix('lecturer-industry')->group(function () {
        Route::get('/home', [LecturerIndustryController::class, 'home']);
        Route::get('/profile', [LecturerIndustryController::class, 'profile']);
        Route::get('/detailStudent/{id}', [LecturerIndustryController::class, 'detailStudent']);
        Route::post('/logBook/{id}/comment', [LecturerIndustryController::class, 'addLogbookComment']);
        Route::post('/addAssessment/{id}', [LecturerIndustryController::class, 'submitScores']);
    });

    // Lecturer only
    Route::middleware('role:lecturer')->prefix('lecturer')->group(function () {
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

    // Kaprodi only
    Route::middleware('role:kaprodi')->prefix('kaprodi')->group(function () {
        Route::get('/seminar', [SeminarController::class, 'kaprodiIndex']);
        Route::post('/seminar', [SeminarController::class, 'store']);
        Route::put('/seminar/{id}/schedule', [SeminarController::class, 'updateSchedule']);
        Route::get('/profile', [KaprodiController::class, 'profile']);
        Route::get('/dashboard', [KaprodiController::class, 'dashboard']);

        Route::get('/mahasiswa', [KaprodiController::class, 'getMahasiswa']);
        Route::put('/mahasiswa/{id}/assign-lecturer', [KaprodiController::class, 'assignLecturer']);

        Route::get('/dosen', [KaprodiController::class, 'getDosen']);
        Route::get('/dosen/{id}/students', [KaprodiController::class, 'getDosenStudents']);

        Route::get('/industri', [KaprodiController::class, 'getIndustri']);
        Route::post('/industri/{id}/verify', [KaprodiController::class, 'verifyIndustri']);
        Route::post('/industri/{id}/reject', [KaprodiController::class, 'rejectIndustri']);
    });

    // Industri only
    Route::middleware('role:industri')->prefix('industri')->group(function () {
        Route::get('/profile', [IndustriController::class, 'profile']);
        Route::put('/profile', [IndustriController::class, 'updateProfile']);

        Route::get('/lowongan', [IndustriController::class, 'getLowongan']);
        Route::post('/lowongan', [IndustriController::class, 'createLowongan']);
        Route::put('/lowongan/{id}', [IndustriController::class, 'updateLowongan']);
        Route::delete('/lowongan/{id}', [IndustriController::class, 'deleteLowongan']);

        Route::get('/pelamar', [IndustriController::class, 'getPelamar']);
        Route::post('/pelamar/{id}/accept', [IndustriController::class, 'acceptPelamar']);
        Route::post('/pelamar/{id}/reject', [IndustriController::class, 'rejectPelamar']);
    });
});
