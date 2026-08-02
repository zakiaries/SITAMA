<?php

use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Mahasiswa\BimbinganController;
use App\Http\Controllers\Mahasiswa\ChatbotController;
use App\Http\Controllers\Mahasiswa\DashboardController;
use App\Http\Controllers\Mahasiswa\LogBookController;
use App\Http\Controllers\Mahasiswa\LowonganController;
use App\Http\Controllers\Mahasiswa\MagangSayaController;
use App\Http\Controllers\Mahasiswa\MagangRequestController;
use App\Http\Controllers\Mahasiswa\NilaiController;
use App\Http\Controllers\Mahasiswa\LaporanController;
use App\Http\Controllers\Mahasiswa\NotificationController;
use App\Http\Controllers\Mahasiswa\ProfileController;
use App\Http\Controllers\Mahasiswa\SeminarController;
use App\Http\Controllers\Dosen\DashboardController as DosenDashboardController;
use App\Http\Controllers\Dosen\MahasiswaController as DosenMahasiswaController;
use App\Http\Controllers\Dosen\NotificationController as DosenNotificationController;
use App\Http\Controllers\Dosen\ProfileController as DosenProfileController;
use App\Http\Controllers\Dosen\SeminarController as DosenSeminarController;
use App\Http\Controllers\DosenIndustri\DashboardController as IndustriDashboardController;
use App\Http\Controllers\DosenIndustri\MahasiswaController as IndustriMahasiswaController;
use App\Http\Controllers\DosenIndustri\NotificationController as IndustriNotificationController;
use App\Http\Controllers\DosenIndustri\ProfileController as IndustriProfileController;
use App\Http\Controllers\Kaprodi\DashboardController as KaprodiDashboardController;
use App\Http\Controllers\Kaprodi\MahasiswaController as KaprodiMahasiswaController;
use App\Http\Controllers\Kaprodi\DosenController as KaprodiDosenController;
use App\Http\Controllers\Kaprodi\MagangRequestController as KaprodiMagangRequestController;
use App\Http\Controllers\Kaprodi\SeminarController as KaprodiSeminarController;
use App\Http\Controllers\Kaprodi\LowonganController as KaprodiLowonganController;
use App\Http\Controllers\Kaprodi\ChatbotController as KaprodiChatbotController;
use App\Http\Controllers\Kaprodi\ProfileController as KaprodiProfileController;
use App\Http\Controllers\Kaprodi\NotificationController as KaprodiNotificationController;
use App\Http\Controllers\BeritaAcaraController;
use App\Http\Controllers\BerkasController;
use Illuminate\Support\Facades\Route;

// Root redirect ke login
Route::get('/', fn() => redirect()->route('login'));

// Halaman publik (info)
Route::view('/tentang', 'public.about')->name('about');
Route::view('/bantuan', 'public.help')->name('bantuan');
Route::view('/kontak', 'public.contact')->name('contact');

// Berkas dokumen mahasiswa. Sengaja TIDAK memakai middleware auth: controller
// menerima dua cara masuk — sesi/token yang lolos pemeriksaan hak akses, atau
// URL bertanda tangan berbatas waktu untuk aplikasi HP yang membuka berkas di
// browser luar. Berkasnya sendiri kini di luar public/ sehingga tak bisa
// diambil langsung dari web server.
Route::get('/berkas/bimbingan/{guidance}',    [BerkasController::class, 'bimbingan'])->name('berkas.bimbingan');
Route::get('/berkas/laporan/{report}',        [BerkasController::class, 'laporan'])->name('berkas.laporan');
Route::get('/berkas/sertifikat/{internship}', [BerkasController::class, 'sertifikat'])->name('berkas.sertifikat');
Route::get('/berkas/bukti/{magangRequest}',   [BerkasController::class, 'bukti'])->name('berkas.bukti');

// Daftar hadir audiens seminar — wajib login (audiens = mahasiswa, anti-manipulasi)
Route::get('/seminar/hadir/{token}',  [BeritaAcaraController::class, 'show'])->name('berita-acara.show')->middleware('auth');
Route::post('/seminar/hadir/{token}', [BeritaAcaraController::class, 'store'])->name('berita-acara.store')->middleware('auth');

// Aktivasi akun pembimbing industri (publik)
Route::view('/aktivasi', 'auth.aktivasi-entry')->name('aktivasi.entry');
Route::get('/aktivasi/{token}',  [ActivationController::class, 'show'])->name('aktivasi.show');
Route::post('/aktivasi/{token}', [ActivationController::class, 'activate'])->name('aktivasi.submit');

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Lupa / reset password (mandiri, via link ke email terdaftar)
// throttle backstop anti spam email / tebak token (cukup longgar utk WiFi kampus share-IP).
Route::get('/lupa-password',  [PasswordResetController::class, 'showRequestForm'])->name('password.request');
Route::post('/lupa-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:12,1')->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:12,1')->name('password.update');

// Mahasiswa Routes (protected)
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth', 'role:student'])->group(function () {

    // Accessible to pending students too
    Route::get('/menunggu', fn() => view('mahasiswa.menunggu'))->name('menunggu');

    // Requires approved status
    Route::middleware('student.approved')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan');
        Route::post('/bimbingan', [BimbinganController::class, 'store'])->name('bimbingan.store');
        Route::put('/bimbingan/{guidance}', [BimbinganController::class, 'update'])->name('bimbingan.update');
        Route::delete('/bimbingan/{guidance}', [BimbinganController::class, 'destroy'])->name('bimbingan.destroy');

        Route::get('/logbook', [LogBookController::class, 'index'])->name('logbook');
        Route::post('/logbook', [LogBookController::class, 'store'])->name('logbook.store');
        Route::put('/logbook/{logBook}', [LogBookController::class, 'update'])->name('logbook.update');
        Route::delete('/logbook/{logBook}', [LogBookController::class, 'destroy'])->name('logbook.destroy');

        Route::get('/seminar', [SeminarController::class, 'index'])->name('seminar');
        Route::post('/seminar/{seminar}/availability', [SeminarController::class, 'submitAvailability'])->name('seminar.availability');
        Route::get('/seminar/{seminar}/berita-acara', [SeminarController::class, 'beritaAcaraPdf'])->name('seminar.berita-acara');
        Route::get('/seminar/{seminar}', [SeminarController::class, 'detail'])->name('seminar.detail');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/lowongan',            [LowonganController::class, 'index'])->name('lowongan');
        Route::get('/lowongan/{jobListing}', [LowonganController::class, 'show'])->name('lowongan.detail');

        Route::get('/ajukan-magang',  [MagangRequestController::class, 'index'])->name('ajukan-magang');
        Route::post('/ajukan-magang', [MagangRequestController::class, 'store'])->name('ajukan-magang.store');
        Route::delete('/ajukan-magang/{magangRequest}', [MagangRequestController::class, 'cancel'])->name('ajukan-magang.cancel');

        Route::get('/magang-saya', [MagangSayaController::class, 'index'])->name('magang-saya');
        Route::post('/magang-saya/sertifikat', [MagangSayaController::class, 'uploadCertificate'])->name('magang-saya.sertifikat');
        Route::post('/magang-saya/ajukan-selesai', [MagangSayaController::class, 'requestFinish'])->name('magang-saya.ajukan-selesai');
        Route::post('/magang-saya/batal-selesai',  [MagangSayaController::class, 'cancelFinish'])->name('magang-saya.batal-selesai');

        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai');

        Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot');
        Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');

        Route::get('/laporan',  [LaporanController::class, 'index'])->name('laporan');
        Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');

        Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi');
        Route::get('/notifikasi/{notification}/open', [NotificationController::class, 'open'])->name('notifikasi.open');
        Route::post('/notifikasi/read-all', [NotificationController::class, 'markAllRead'])->name('notifikasi.read-all');
        Route::post('/notifikasi/{notification}/read', [NotificationController::class, 'markRead'])->name('notifikasi.read');
    });
});

// ── Dosen Routes (protected) ──────────────────────────────────────────────────
Route::prefix('dosen')->name('dosen.')->middleware(['auth', 'role:lecturer'])->group(function () {

    Route::get('/dashboard', [DosenDashboardController::class, 'index'])->name('dashboard');

    Route::get('/mahasiswa/{student}',                                [DosenMahasiswaController::class, 'detail'])->name('mahasiswa.detail');
    Route::post('/mahasiswa/{student}/bimbingan/{guidance}/approve',  [DosenMahasiswaController::class, 'approveBimbingan'])->name('mahasiswa.bimbingan.approve');
    Route::post('/mahasiswa/{student}/bimbingan/{guidance}/revisi',   [DosenMahasiswaController::class, 'revisiBimbingan'])->name('mahasiswa.bimbingan.revisi');
    Route::post('/mahasiswa/{student}/logbook/{logBook}/note',        [DosenMahasiswaController::class, 'logBookNote'])->name('mahasiswa.logbook.note');
    Route::delete('/mahasiswa/{student}/logbook/{logBook}/note',      [DosenMahasiswaController::class, 'hapusLogBookNote'])->name('mahasiswa.logbook.note.hapus');
    Route::get('/mahasiswa/{student}/nilai',                          [DosenMahasiswaController::class, 'nilaiPage'])->name('mahasiswa.nilai');
    Route::post('/mahasiswa/{student}/nilai',                         [DosenMahasiswaController::class, 'updateNilai'])->name('mahasiswa.nilai.update');
    Route::post('/mahasiswa/{student}/laporan/{report}/approve',      [DosenMahasiswaController::class, 'approveLaporan'])->name('mahasiswa.laporan.approve');
    Route::post('/mahasiswa/{student}/laporan/{report}/revisi',       [DosenMahasiswaController::class, 'revisiLaporan'])->name('mahasiswa.laporan.revisi');

    Route::get('/seminar',                    [DosenSeminarController::class, 'index'])->name('seminar.index');
    Route::get('/seminar/{seminar}/qr',       [DosenSeminarController::class, 'qr'])->name('seminar.qr');
    Route::post('/seminar',                   [DosenSeminarController::class, 'store'])->name('seminar.store');
    Route::post('/seminar/{seminar}/finalize', [DosenSeminarController::class, 'finalize'])->name('seminar.finalize');
    Route::put('/seminar/{seminar}',           [DosenSeminarController::class, 'update'])->name('seminar.update');
    Route::post('/seminar/{seminar}/sahkan',  [DosenSeminarController::class, 'sahkan'])->name('seminar.sahkan');
    Route::delete('/seminar/{seminar}',       [DosenSeminarController::class, 'destroy'])->name('seminar.destroy');

    Route::get('/profile',  [DosenProfileController::class, 'index'])->name('profile');
    Route::put('/profile',  [DosenProfileController::class, 'update'])->name('profile.update');

    Route::get('/notifikasi', [DosenNotificationController::class, 'index'])->name('notifikasi');
    Route::get('/notifikasi/{notification}/open', [DosenNotificationController::class, 'open'])->name('notifikasi.open');
    Route::post('/notifikasi/read-all', [DosenNotificationController::class, 'markAllRead'])->name('notifikasi.read-all');
    Route::post('/notifikasi/{notification}/read', [DosenNotificationController::class, 'markRead'])->name('notifikasi.read');
});

// ── Dosen Industri Routes (protected) ─────────────────────────────────────────
Route::prefix('dosen-industri')->name('dosen-industri.')->middleware(['auth', 'role:lecturer_industry'])->group(function () {

    Route::get('/dashboard', [IndustriDashboardController::class, 'index'])->name('dashboard');

    Route::get('/mahasiswa/{student}',                              [IndustriMahasiswaController::class, 'detail'])->name('mahasiswa.detail');
    Route::post('/mahasiswa/{student}/logbook/{logBook}/komentar',  [IndustriMahasiswaController::class, 'kirimKomentar'])->name('mahasiswa.logbook.komentar');
    Route::delete('/mahasiswa/{student}/logbook/{logBook}/komentar',[IndustriMahasiswaController::class, 'hapusKomentar'])->name('mahasiswa.logbook.komentar.hapus');
    Route::get('/mahasiswa/{student}/penilaian',                    [IndustriMahasiswaController::class, 'penilaianPage'])->name('mahasiswa.penilaian');
    Route::post('/mahasiswa/{student}/penilaian',                   [IndustriMahasiswaController::class, 'simpanPenilaian'])->name('mahasiswa.penilaian.simpan');

    Route::get('/profile',  [IndustriProfileController::class, 'index'])->name('profile');
    Route::put('/profile',  [IndustriProfileController::class, 'update'])->name('profile.update');

    Route::get('/notifikasi', [IndustriNotificationController::class, 'index'])->name('notifikasi');
    Route::get('/notifikasi/{notification}/open', [IndustriNotificationController::class, 'open'])->name('notifikasi.open');
    Route::post('/notifikasi/read-all', [IndustriNotificationController::class, 'markAllRead'])->name('notifikasi.read-all');
    Route::post('/notifikasi/{notification}/read', [IndustriNotificationController::class, 'markRead'])->name('notifikasi.read');
});

// ── Kaprodi Routes (protected) ────────────────────────────────────────────────
Route::prefix('kaprodi')->name('kaprodi.')->middleware(['auth', 'role:kaprodi'])->group(function () {

    Route::get('/dashboard', [KaprodiDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export-excel', [KaprodiDashboardController::class, 'exportExcel'])->name('dashboard.export-excel');

    Route::get('/mahasiswa',                              [KaprodiMahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('/mahasiswa/{student}',                    [KaprodiMahasiswaController::class, 'detail'])->name('mahasiswa.detail');
    Route::post('/mahasiswa/{student}/approve-finish',    [KaprodiMahasiswaController::class, 'approveFinish'])->name('mahasiswa.approve-finish');
    Route::post('/mahasiswa/{student}/buka-finish',       [KaprodiMahasiswaController::class, 'bukaKembaliFinish'])->name('mahasiswa.buka-finish');
    Route::post('/mahasiswa/{student}/reset-password',    [KaprodiMahasiswaController::class, 'resetPassword'])->name('mahasiswa.reset-password');
    Route::post('/mahasiswa/{student}/internship',      [KaprodiMahasiswaController::class, 'storeInternship'])->name('mahasiswa.internship.store');
    Route::post('/mahasiswa/{student}/assign-lecturer', [KaprodiMahasiswaController::class, 'assignLecturer'])->name('mahasiswa.assign');
    Route::post('/mahasiswa/{student}/approve',         [KaprodiMahasiswaController::class, 'approve'])->name('mahasiswa.approve');
    Route::post('/mahasiswa/{student}/reject',          [KaprodiMahasiswaController::class, 'reject'])->name('mahasiswa.reject');

    Route::get('/dosen',                                  [KaprodiDosenController::class, 'index'])->name('dosen.index');
    Route::post('/dosen',                                 [KaprodiDosenController::class, 'store'])->name('dosen.store');
    Route::get('/dosen/{lecturer}',                       [KaprodiDosenController::class, 'detail'])->name('dosen.detail');
    Route::post('/dosen/{lecturer}/reset-password',       [KaprodiDosenController::class, 'resetPassword'])->name('dosen.reset-password');

    Route::get('/pengajuan-magang', [KaprodiMagangRequestController::class, 'index'])->name('pengajuan-magang.index');
    Route::post('/pengajuan-magang/{magangRequest}/approve', [KaprodiMagangRequestController::class, 'approve'])->name('pengajuan-magang.approve');
    Route::post('/pengajuan-magang/{magangRequest}/reject',  [KaprodiMagangRequestController::class, 'reject'])->name('pengajuan-magang.reject');
    Route::post('/pengajuan-magang/{magangRequest}/resend',  [KaprodiMagangRequestController::class, 'resendInvitation'])->name('pengajuan-magang.resend');

    Route::get('/seminar', [KaprodiSeminarController::class, 'index'])->name('seminar.index');

    // Kelola lowongan/tempat magang perusahaan afiliasi (tampil ke mahasiswa).
    Route::get('/lowongan',                  [KaprodiLowonganController::class, 'index'])->name('lowongan.index');
    Route::get('/lowongan/create',           [KaprodiLowonganController::class, 'create'])->name('lowongan.create');
    Route::post('/lowongan',                 [KaprodiLowonganController::class, 'store'])->name('lowongan.store');
    Route::get('/lowongan/{lowongan}/edit',  [KaprodiLowonganController::class, 'edit'])->name('lowongan.edit');
    Route::put('/lowongan/{lowongan}',       [KaprodiLowonganController::class, 'update'])->name('lowongan.update');
    Route::post('/lowongan/{lowongan}/toggle',[KaprodiLowonganController::class, 'toggle'])->name('lowongan.toggle');
    Route::delete('/lowongan/{lowongan}',    [KaprodiLowonganController::class, 'destroy'])->name('lowongan.destroy');

    Route::get('/chatbot',                          [KaprodiChatbotController::class, 'index'])->name('chatbot.index');
    Route::get('/chatbot/logs',                     [KaprodiChatbotController::class, 'logs'])->name('chatbot.logs');
    Route::get('/chatbot/create',                   [KaprodiChatbotController::class, 'create'])->name('chatbot.create');
    Route::post('/chatbot',                         [KaprodiChatbotController::class, 'store'])->name('chatbot.store');
    Route::get('/chatbot/{chatbotKnowledge}/edit',  [KaprodiChatbotController::class, 'edit'])->name('chatbot.edit');
    Route::put('/chatbot/{chatbotKnowledge}',       [KaprodiChatbotController::class, 'update'])->name('chatbot.update');
    Route::post('/chatbot/{chatbotKnowledge}/toggle', [KaprodiChatbotController::class, 'toggle'])->name('chatbot.toggle');
    Route::delete('/chatbot/{chatbotKnowledge}',    [KaprodiChatbotController::class, 'destroy'])->name('chatbot.destroy');

    Route::get('/profile',  [KaprodiProfileController::class, 'index'])->name('profile');
    Route::put('/profile',  [KaprodiProfileController::class, 'update'])->name('profile.update');

    Route::get('/notifikasi', [KaprodiNotificationController::class, 'index'])->name('notifikasi');
    Route::get('/notifikasi/{notification}/open', [KaprodiNotificationController::class, 'open'])->name('notifikasi.open');
    Route::post('/notifikasi/read-all', [KaprodiNotificationController::class, 'markAllRead'])->name('notifikasi.read-all');
    Route::post('/notifikasi/{notification}/read', [KaprodiNotificationController::class, 'markRead'])->name('notifikasi.read');
});

