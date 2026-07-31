<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'message', 'date', 'category', 'is_read', 'detail_text',
    ];

    protected $casts = [
        'date' => 'date',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kirim notifikasi ke satu pengguna. Dipakai di seluruh alur agar lonceng
     * notifikasi benar-benar terisi — sebelumnya hanya sebagian kejadian yang
     * membuat notifikasi, sehingga penanda di sidebar menyala tapi lonceng
     * di kanan atas tetap kosong.
     *
     * $userId null (mis. dosen pembimbing belum diplot) diabaikan diam-diam,
     * supaya kegagalan mengirim notifikasi tak pernah menggagalkan aksi utama.
     */
    public static function kirim(?int $userId, string $message, string $category, ?string $detail = null): void
    {
        if (! $userId) {
            return;
        }

        static::create([
            'user_id'     => $userId,
            'message'     => $message,
            'date'        => now()->toDateString(),
            'category'    => $category,
            'is_read'     => false,
            'detail_text' => $detail,
        ]);
    }
}
