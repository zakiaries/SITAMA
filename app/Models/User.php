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
