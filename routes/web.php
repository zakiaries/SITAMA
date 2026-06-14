<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Mahasiswa\BimbinganController;
use App\Http\Controllers\Mahasiswa\DashboardController;
use App\Http\Controllers\Mahasiswa\InternshipGroupController;
use App\Http\Controllers\Mahasiswa\LogBookController;
use App\Http\Controllers\Mahasiswa\LowonganController;
use App\Http\Controllers\Mahasiswa\MagangSayaController;
use App\Http\Controllers\Mahasiswa\NilaiController;
use App\Http\Controllers\Mahasiswa\LaporanController;
use App\Http\Controllers\Mahasiswa\IndustriRequestController;
use App\Http\Controllers\Mahasiswa\NotificationController;
use App\Http\Controllers\Mahasiswa\ProfileController;
use App\Http\Controllers\Mahasiswa\SeminarController;
use App\Http\Controllers\Dosen\DashboardController as DosenDashboardController;
use App\Http\Controllers\Dosen\MahasiswaController as DosenMahasiswaController;
use App\Http\Controllers\Dosen\NotificationController as DosenNotificationController;
use App\Http\Controllers\Dosen\ProfileController as DosenProfileController;
use App\Http\Controllers\DosenIndustri\DashboardController as IndustriDashboardController;
use App\Http\Controllers\DosenIndustri\MahasiswaController as IndustriMahasiswaController;
use App\Http\Controllers\DosenIndustri\NotificationController as IndustriNotificationController;
use App\Http\Controllers\DosenIndustri\ProfileController as IndustriProfileController;
use App\Http\Controllers\Kaprodi\DashboardController as KaprodiDashboardController;
use App\Http\Controllers\Kaprodi\MahasiswaController as KaprodiMahasiswaController;
use App\Http\Controllers\Kaprodi\DosenController as KaprodiDosenController;
use App\Http\Controllers\Kaprodi\IndustriController as KaprodiIndustriController;
use App\Http\Controllers\Kaprodi\IndustriRequestController as KaprodiIndustriRequestController;
use App\Http\Controllers\Kaprodi\LowonganController as KaprodiLowonganController;
use App\Http\Controllers\Kaprodi\ProfileController as KaprodiProfileController;
use App\Http\Controllers\Industri\DashboardController as IndustriHomeController;
use App\Http\Controllers\Industri\ProfileController as IndustriCompanyProfileController;
use Illuminate\Support\Facades\Route;

// Root redirect ke login
Route::get('/', fn() => redirect()->route('login'));

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Mahasiswa Routes (protected)
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware('auth')->group(function () {

    // Accessible to pending students too
    Route::get('/menunggu', fn() => view('mahasiswa.menunggu'))->name('menunggu');

    // Requires approved status
    Route::middleware('student.approved')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan');
        Route::post('/bimbingan', [BimbinganController::class, 'store'])->name('bimbingan.store');
        Route::put('/bimbingan/{guidance}', [BimbinganController::class, 'update'])->name('bimbingan.update');

        Route::get('/logbook', [LogBookController::class, 'index'])->name('logbook');
        Route::post('/logbook', [LogBookController::class, 'store'])->name('logbook.store');
        Route::delete('/logbook/{logBook}', [LogBookController::class, 'destroy'])->name('logbook.destroy');

        Route::get('/lowongan', [LowonganController::class, 'index'])->name('lowongan');

        Route::get('/seminar', [SeminarController::class, 'index'])->name('seminar');
        Route::post('/seminar', [SeminarController::class, 'store'])->name('seminar.store');
        Route::put('/seminar/{seminar}', [SeminarController::class, 'update'])->name('seminar.update');
        Route::delete('/seminar/{seminar}', [SeminarController::class, 'destroy'])->name('seminar.destroy');
        Route::get('/seminar/{seminar}', [SeminarController::class, 'detail'])->name('seminar.detail');
        Route::post('/seminar/{seminar}/register', [SeminarController::class, 'register'])->name('seminar.register');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/magang-saya', [MagangSayaController::class, 'index'])->name('magang-saya');
        Route::post('/magang-saya/sertifikat', [MagangSayaController::class, 'uploadCertificate'])->name('magang-saya.sertifikat');

        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai');

        Route::get('/laporan',  [LaporanController::class, 'index'])->name('laporan');
        Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');

        Route::get('/permintaan-industri',  [IndustriRequestController::class, 'index'])->name('industri-request');
        Route::post('/permintaan-industri', [IndustriRequestController::class, 'store'])->name('industri-request.store');

        Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi');
        Route::post('/notifikasi/read-all', [NotificationController::class, 'markAllRead'])->name('notifikasi.read-all');
        Route::post('/notifikasi/{notification}/read', [NotificationController::class, 'markRead'])->name('notifikasi.read');

        Route::get('/internship-groups/{internshipGroup}/invite', [InternshipGroupController::class, 'invitePage'])->name('internship-groups.invite');
        Route::post('/internship-groups/{internshipGroup}/invite', [InternshipGroupController::class, 'invite'])->name('internship-groups.invite.store');

        Route::post('/internship-group-members/{member}/accept', [InternshipGroupController::class, 'accept'])->name('internship-group-members.accept');
        Route::post('/internship-group-members/{member}/decline', [InternshipGroupController::class, 'decline'])->name('internship-group-members.decline');
    });
});

// ── Dosen Routes (protected) ──────────────────────────────────────────────────
Route::prefix('dosen')->name('dosen.')->middleware('auth')->group(function () {

    Route::get('/dashboard', [DosenDashboardController::class, 'index'])->name('dashboard');

    Route::get('/mahasiswa/{student}',                                [DosenMahasiswaController::class, 'detail'])->name('mahasiswa.detail');
    Route::post('/mahasiswa/{student}/bimbingan/{guidance}/approve',  [DosenMahasiswaController::class, 'approveBimbingan'])->name('mahasiswa.bimbingan.approve');
    Route::post('/mahasiswa/{student}/bimbingan/{guidance}/revisi',   [DosenMahasiswaController::class, 'revisiBimbingan'])->name('mahasiswa.bimbingan.revisi');
    Route::post('/mahasiswa/{student}/logbook/{logBook}/note',        [DosenMahasiswaController::class, 'logBookNote'])->name('mahasiswa.logbook.note');
    Route::get('/mahasiswa/{student}/nilai',                          [DosenMahasiswaController::class, 'nilaiPage'])->name('mahasiswa.nilai');
    Route::post('/mahasiswa/{student}/nilai',                         [DosenMahasiswaController::class, 'updateNilai'])->name('mahasiswa.nilai.update');
    Route::post('/mahasiswa/{student}/laporan/{report}/approve',      [DosenMahasiswaController::class, 'approveLaporan'])->name('mahasiswa.laporan.approve');
    Route::post('/mahasiswa/{student}/laporan/{report}/revisi',       [DosenMahasiswaController::class, 'revisiLaporan'])->name('mahasiswa.laporan.revisi');

    Route::get('/profile',  [DosenProfileController::class, 'index'])->name('profile');
    Route::put('/profile',  [DosenProfileController::class, 'update'])->name('profile.update');

    Route::get('/notifikasi', [DosenNotificationController::class, 'index'])->name('notifikasi');
    Route::post('/notifikasi/read-all', [DosenNotificationController::class, 'markAllRead'])->name('notifikasi.read-all');
    Route::post('/notifikasi/{notification}/read', [DosenNotificationController::class, 'markRead'])->name('notifikasi.read');
});

// ── Dosen Industri Routes (protected) ─────────────────────────────────────────
Route::prefix('dosen-industri')->name('dosen-industri.')->middleware('auth')->group(function () {

    Route::get('/dashboard', [IndustriDashboardController::class, 'index'])->name('dashboard');

    Route::get('/mahasiswa/{student}',                              [IndustriMahasiswaController::class, 'detail'])->name('mahasiswa.detail');
    Route::post('/mahasiswa/{student}/logbook/{logBook}/komentar',  [IndustriMahasiswaController::class, 'kirimKomentar'])->name('mahasiswa.logbook.komentar');
    Route::delete('/mahasiswa/{student}/logbook/{logBook}/komentar',[IndustriMahasiswaController::class, 'hapusKomentar'])->name('mahasiswa.logbook.komentar.hapus');
    Route::get('/mahasiswa/{student}/penilaian',                    [IndustriMahasiswaController::class, 'penilaianPage'])->name('mahasiswa.penilaian');
    Route::post('/mahasiswa/{student}/penilaian',                   [IndustriMahasiswaController::class, 'simpanPenilaian'])->name('mahasiswa.penilaian.simpan');

    Route::get('/profile',  [IndustriProfileController::class, 'index'])->name('profile');
    Route::put('/profile',  [IndustriProfileController::class, 'update'])->name('profile.update');

    Route::get('/notifikasi', [IndustriNotificationController::class, 'index'])->name('notifikasi');
    Route::post('/notifikasi/read-all', [IndustriNotificationController::class, 'markAllRead'])->name('notifikasi.read-all');
    Route::post('/notifikasi/{notification}/read', [IndustriNotificationController::class, 'markRead'])->name('notifikasi.read');
});

// ── Kaprodi Routes (protected) ────────────────────────────────────────────────
Route::prefix('kaprodi')->name('kaprodi.')->middleware('auth')->group(function () {

    Route::get('/dashboard', [KaprodiDashboardController::class, 'index'])->name('dashboard');

    Route::get('/mahasiswa',                            [KaprodiMahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('/mahasiswa/{student}',                  [KaprodiMahasiswaController::class, 'detail'])->name('mahasiswa.detail');
    Route::post('/mahasiswa/{student}/toggle-finished', [KaprodiMahasiswaController::class, 'toggleFinished'])->name('mahasiswa.toggle-finished');
    Route::post('/mahasiswa/{student}/internship',      [KaprodiMahasiswaController::class, 'storeInternship'])->name('mahasiswa.internship.store');
    Route::post('/mahasiswa/{student}/assign-lecturer', [KaprodiMahasiswaController::class, 'assignLecturer'])->name('mahasiswa.assign');
    Route::post('/mahasiswa/{student}/approve',         [KaprodiMahasiswaController::class, 'approve'])->name('mahasiswa.approve');
    Route::post('/mahasiswa/{student}/reject',          [KaprodiMahasiswaController::class, 'reject'])->name('mahasiswa.reject');

    Route::get('/dosen',            [KaprodiDosenController::class, 'index'])->name('dosen.index');
    Route::get('/dosen/{lecturer}', [KaprodiDosenController::class, 'detail'])->name('dosen.detail');

    Route::get('/industri',                     [KaprodiIndustriController::class, 'index'])->name('industri.index');
    Route::post('/industri/{company}/verify',   [KaprodiIndustriController::class, 'verify'])->name('industri.verify');
    Route::post('/industri/{company}/reject',   [KaprodiIndustriController::class, 'reject'])->name('industri.reject');

    Route::post('/industri-request/{companyRequest}/approve', [KaprodiIndustriRequestController::class, 'approve'])->name('industri-request.approve');
    Route::post('/industri-request/{companyRequest}/reject',  [KaprodiIndustriRequestController::class, 'reject'])->name('industri-request.reject');

    Route::get('/lowongan',                       [KaprodiLowonganController::class, 'index'])->name('lowongan.index');
    Route::get('/lowongan/create',                [KaprodiLowonganController::class, 'create'])->name('lowongan.create');
    Route::post('/lowongan',                      [KaprodiLowonganController::class, 'store'])->name('lowongan.store');
    Route::get('/lowongan/{jobListing}/edit',     [KaprodiLowonganController::class, 'edit'])->name('lowongan.edit');
    Route::put('/lowongan/{jobListing}',          [KaprodiLowonganController::class, 'update'])->name('lowongan.update');
    Route::patch('/lowongan/{jobListing}/toggle', [KaprodiLowonganController::class, 'toggleStatus'])->name('lowongan.toggle');
    Route::delete('/lowongan/{jobListing}',       [KaprodiLowonganController::class, 'destroy'])->name('lowongan.destroy');

    Route::get('/profile',  [KaprodiProfileController::class, 'index'])->name('profile');
    Route::put('/profile',  [KaprodiProfileController::class, 'update'])->name('profile.update');
});

// ── Industri (Perusahaan/HR) Routes (protected) ───────────────────────────────
Route::prefix('industri')->name('industri.')->middleware('auth')->group(function () {

    Route::get('/dashboard', [IndustriHomeController::class, 'index'])->name('dashboard');

    // Catatan: fitur kelola lowongan + review pelamar dipindah ke kaprodi
    // (lowongan kini berupa pengumuman yang diinput admin/kampus - item 27).

    Route::get('/profile',  [IndustriCompanyProfileController::class, 'index'])->name('profile');
    Route::put('/profile',  [IndustriCompanyProfileController::class, 'update'])->name('profile.update');
});
