<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect root ke mahasiswa dashboard
Route::get('/', fn() => redirect()->route('mahasiswa.dashboard'));

// Mahasiswa Routes
Route::prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', fn() => view('mahasiswa.dashboard.index', ['title' => 'Dashboard']))->name('dashboard');
    Route::get('/bimbingan', fn() => view('mahasiswa.bimbingan.index', ['title' => 'Bimbingan']))->name('bimbingan');
    Route::get('/logbook', fn() => view('mahasiswa.logbook.index', ['title' => 'Log Book']))->name('logbook');
    Route::get('/lowongan', fn() => view('mahasiswa.lowongan.index', ['title' => 'Lowongan Magang']))->name('lowongan');
    Route::get('/seminar', fn() => view('mahasiswa.seminar.index', ['title' => 'Seminar']))->name('seminar');
    Route::get('/seminar/{id}', fn($id) => view('mahasiswa.seminar.detail', ['title' => 'Detail Seminar']))->name('seminar.detail');
    Route::get('/profile', fn() => view('mahasiswa.profile.index', ['title' => 'Profile']))->name('profile');
});
