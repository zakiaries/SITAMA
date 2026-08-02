<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Mail\ResetPasswordMail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Ganti kata sandi mencabut token aplikasi HP.
     *
     * Kata sandi berubah di 18 tempat (profil tiap peran di web & API, reset
     * mandiri lewat email, reset oleh Kaprodi, aktivasi akun industri).
     * Menambalnya satu per satu pasti menyisakan yang terlewat, jadi aturannya
     * dipasang di sini — berlaku untuk semua jalur, termasuk yang dibuat nanti.
     *
     * Tanpa ini, mengganti kata sandi karena curiga akun disusupi sama sekali
     * tak memutus akses lewat aplikasi: token Sanctum tetap sah.
     *
     * Token yang SEDANG dipakai sengaja dipertahankan bila pemiliknya sendiri
     * yang mengganti sandi lewat API — aplikasi Flutter belum menangani 401,
     * jadi mencabutnya akan membuat pengguna tersangkut tanpa penjelasan.
     * Saat Kaprodi mereset sandi orang lain, $user bukan pengguna terautentikasi
     * sehingga tak ada token yang dikecualikan — semuanya dicabut.
     */
    protected static function booted(): void
    {
        static::updated(function (self $user) {
            if (! $user->wasChanged('password')) {
                return;
            }

            $query   = $user->tokens();
            $current = $user->currentAccessToken();

            if ($current instanceof \Laravel\Sanctum\PersonalAccessToken) {
                $query->whereKeyNot($current->getKey());
            }

            $query->delete();
        });
    }

    /**
     * Kirim email reset password (dipicu password broker) — pakai Mailable
     * berbahasa Indonesia, link menuju halaman reset di website.
     */
    public function sendPasswordResetNotification($token)
    {
        $url = route('password.reset', ['token' => $token, 'email' => $this->getEmailForPasswordReset()]);
        Mail::to($this->email)->send(new ResetPasswordMail($this, $url, config('auth.passwords.users.expire', 60)));
    }

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'photo_profile', 'is_activated',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_activated'      => 'boolean',
    ];

    /**
     * URL foto profil (null bila belum ada) — dipakai web & API.
     */
    public function photoUrl(): ?string
    {
        return $this->photo_profile
            ? \Illuminate\Support\Facades\Storage::url($this->photo_profile)
            : null;
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function lecturer()
    {
        return $this->hasOne(Lecturer::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }
}
