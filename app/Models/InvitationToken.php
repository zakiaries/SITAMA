<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationToken extends Model
{
    // `used_at` WAJIB ikut fillable. Tanpa itu, update(['used_at' => now()]) di
    // ActivationController dibuang diam-diam oleh perlindungan mass-assignment —
    // tanpa galat — sehingga tautan undangan tak pernah ditandai terpakai dan
    // tetap sah sampai kedaluwarsa. Siapa pun yang memegang tautan itu bisa
    // memakainya lagi untuk mengganti username & kata sandi akun tersebut.
    protected $fillable = ['user_id', 'token', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        return is_null($this->used_at) && $this->expires_at->isFuture();
    }
}
