<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Mahasiswa\BimbinganController as MhsBimbingan;
use App\Http\Controllers\Api\Mahasiswa\ChatbotController as MhsChatbot;
use App\Http\Controllers\Api\Mahasiswa\DashboardController as MhsDashboard;
use App\Http\Controllers\Api\Mahasiswa\LaporanController as MhsLaporan;
use App\Http\Controllers\Api\Mahasiswa\LogBookController as MhsLogBook;
use App\Http\Controllers\Api\Mahasiswa\LowonganController as MhsLowongan;
use App\Http\Controllers\Api\Mahasiswa\MagangController as MhsMagang;
use App\Http\Controllers\Api\Mahasiswa\NilaiController as MhsNilai;
use App\Http\Controllers\Api\Mahasiswa\NotificationController as MhsNotif;
use App\Http\Controllers\Api\Mahasiswa\ProfileController as MhsProfile;
use App\Http\Controllers\Api\Mahasiswa\SeminarController as MhsSeminar;
use App\Http\Controllers\Api\Dosen\DashboardController as DsnDashboard;
use App\Http\Controllers\Api\Dosen\MahasiswaController as DsnMahasiswa;
use App\Http\Controllers\Api\Dosen\NotificationController as DsnNotif;
use App\Http\Controllers\Api\Dosen\ProfileController as DsnProfile;
use App\Http\Controllers\Api\Dosen\SeminarController as DsnSeminar;
use App\Http\Controllers\Api\DosenIndustri\DashboardController as IndDashboard;
use App\Http\Controllers\Api\DosenIndustri\MahasiswaController as IndMahasiswa;
use App\Http\Controllers\Api\DosenIndustri\NotificationController as IndNotif;
use App\Http\Controllers\Api\DosenIndustri\ProfileController as IndProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — SIMAMA Mobile
|--------------------------------------------------------------------------
| Dipakai aplikasi mobile (Mahasiswa, Dosen, Pembimbing Industri).
| Auth: token via Laravel Sanctum. Tidak mengubah web/DB yang sudah ada.
*/

// ── Publik ──
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/aktivasi/{token}', [AuthController::class, 'activationShow']);
Route::post('/aktivasi/{token}', [AuthController::class, 'activate']);

// Reset password mandiri (link ke email terdaftar). throttle backstop anti spam.
Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:12,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:12,1');

// PDF berita acara seminar — dilindungi signed URL (bisa dibuka di browser HP
// tanpa token). URL bertanda-tangan hanya diberikan ke pemilik seminar.
Route::get('/mahasiswa/seminar/{seminar}/berita-acara', [MhsSeminar::class, 'beritaAcaraPdf'])
    ->middleware('signed')->name('mobile.seminar.berita-acara');

// ── Perlu token (Authorization: Bearer <token>) ──
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user());

    // ══════════ MAHASISWA ══════════
    Route::prefix('mahasiswa')->group(function () {
        Route::get('/dashboard', [MhsDashboard::class, 'index']);

        Route::get('/logbook', [MhsLogBook::class, 'index']);
        Route::post('/logbook', [MhsLogBook::class, 'store']);
        Route::put('/logbook/{logBook}', [MhsLogBook::class, 'update']);
        Route::delete('/logbook/{logBook}', [MhsLogBook::class, 'destroy']);

        Route::get('/bimbingan', [MhsBimbingan::class, 'index']);
        Route::post('/bimbingan', [MhsBimbingan::class, 'store']);
        Route::put('/bimbingan/{guidance}', [MhsBimbingan::class, 'update']);
        Route::delete('/bimbingan/{guidance}', [MhsBimbingan::class, 'destroy']);

        Route::get('/laporan', [MhsLaporan::class, 'index']);
        Route::post('/laporan', [MhsLaporan::class, 'store']);

        Route::get('/nilai', [MhsNilai::class, 'index']);

        Route::get('/lowongan', [MhsLowongan::class, 'index']);
        Route::get('/lowongan/{jobListing}', [MhsLowongan::class, 'show']);

        Route::get('/ajukan-magang', [MhsMagang::class, 'ajukanIndex']);
        Route::post('/ajukan-magang', [MhsMagang::class, 'ajukanStore']);
        Route::delete('/ajukan-magang/{magangRequest}', [MhsMagang::class, 'cancelAjukan']);
        Route::get('/magang-saya', [MhsMagang::class, 'magangSaya']);
        Route::post('/magang-saya/sertifikat', [MhsMagang::class, 'uploadCertificate']);
        Route::post('/magang-saya/ajukan-selesai', [MhsMagang::class, 'requestFinish']);

        // Seminar model sesi-grup: mahasiswa penyaji isi ketersediaan + lihat jadwal.
        Route::get('/seminar', [MhsSeminar::class, 'index']);
        Route::post('/seminar/attend', [MhsSeminar::class, 'attend']); // audiens absen via scan QR in-app
        Route::get('/seminar/{seminar}', [MhsSeminar::class, 'show']);
        Route::post('/seminar/{seminar}/availability', [MhsSeminar::class, 'submitAvailability']);

        Route::get('/notifikasi', [MhsNotif::class, 'index']);
        Route::post('/notifikasi/read-all', [MhsNotif::class, 'markAllRead']);
        Route::post('/notifikasi/{notification}/read', [MhsNotif::class, 'markRead']);

        Route::get('/profile', [MhsProfile::class, 'index']);
        Route::put('/profile', [MhsProfile::class, 'update']);
        Route::post('/profile/photo', [MhsProfile::class, 'photo']);

        Route::get('/chatbot', [MhsChatbot::class, 'index']);
        Route::post('/chatbot', [MhsChatbot::class, 'ask']);
    });

    // ══════════ DOSEN ══════════
    Route::prefix('dosen')->group(function () {
        Route::get('/dashboard', [DsnDashboard::class, 'index']);

        Route::get('/mahasiswa/{student}', [DsnMahasiswa::class, 'detail']);
        Route::post('/mahasiswa/{student}/bimbingan/{guidance}/approve', [DsnMahasiswa::class, 'approveBimbingan']);
        Route::post('/mahasiswa/{student}/bimbingan/{guidance}/revisi', [DsnMahasiswa::class, 'revisiBimbingan']);
        Route::post('/mahasiswa/{student}/logbook/{logBook}/note', [DsnMahasiswa::class, 'logBookNote']);
        Route::delete('/mahasiswa/{student}/logbook/{logBook}/note', [DsnMahasiswa::class, 'hapusLogBookNote']);
        Route::post('/mahasiswa/{student}/laporan/{report}/approve', [DsnMahasiswa::class, 'approveLaporan']);
        Route::post('/mahasiswa/{student}/laporan/{report}/revisi', [DsnMahasiswa::class, 'revisiLaporan']);
        Route::get('/mahasiswa/{student}/nilai', [DsnMahasiswa::class, 'nilaiPage']);
        Route::post('/mahasiswa/{student}/nilai', [DsnMahasiswa::class, 'updateNilai']);

        // Seminar Bimbingan (sesi-grup): dosen buat/finalkan/sahkan/batalkan sesi.
        Route::get('/seminar', [DsnSeminar::class, 'index']);
        Route::post('/seminar', [DsnSeminar::class, 'store']);
        Route::put('/seminar/{seminar}', [DsnSeminar::class, 'update']);
        Route::get('/seminar/{seminar}/qr', [DsnSeminar::class, 'qrToken']);
        Route::post('/seminar/{seminar}/finalize', [DsnSeminar::class, 'finalize']);
        Route::post('/seminar/{seminar}/sahkan', [DsnSeminar::class, 'sahkan']);
        Route::delete('/seminar/{seminar}', [DsnSeminar::class, 'destroy']);

        Route::get('/notifikasi', [DsnNotif::class, 'index']);
        Route::post('/notifikasi/read-all', [DsnNotif::class, 'markAllRead']);
        Route::post('/notifikasi/{notification}/read', [DsnNotif::class, 'markRead']);

        Route::get('/profile', [DsnProfile::class, 'index']);
        Route::put('/profile', [DsnProfile::class, 'update']);
        Route::post('/profile/photo', [DsnProfile::class, 'photo']);
    });

    // ══════════ PEMBIMBING INDUSTRI ══════════
    Route::prefix('dosen-industri')->group(function () {
        Route::get('/dashboard', [IndDashboard::class, 'index']);

        Route::get('/mahasiswa/{student}', [IndMahasiswa::class, 'detail']);
        Route::post('/mahasiswa/{student}/logbook/{logBook}/komentar', [IndMahasiswa::class, 'kirimKomentar']);
        Route::delete('/mahasiswa/{student}/logbook/{logBook}/komentar', [IndMahasiswa::class, 'hapusKomentar']);
        Route::get('/mahasiswa/{student}/penilaian', [IndMahasiswa::class, 'penilaianPage']);
        Route::post('/mahasiswa/{student}/penilaian', [IndMahasiswa::class, 'simpanPenilaian']);

        Route::get('/notifikasi', [IndNotif::class, 'index']);
        Route::post('/notifikasi/read-all', [IndNotif::class, 'markAllRead']);
        Route::post('/notifikasi/{notification}/read', [IndNotif::class, 'markRead']);

        Route::get('/profile', [IndProfile::class, 'index']);
        Route::put('/profile', [IndProfile::class, 'update']);
        Route::post('/profile/photo', [IndProfile::class, 'photo']);
    });
});
