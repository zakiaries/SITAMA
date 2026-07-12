<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Riwayat percakapan chatbot — tiap pertanyaan mahasiswa beserta jawaban,
 * skor kemiripan, dan apakah terjawab. Dipakai Kaprodi untuk memantau dan
 * memperbaiki basis pengetahuan (pertanyaan yang sering tak terjawab).
 */
class ChatLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'message', 'answer', 'score', 'matched_question', 'category', 'is_answered',
    ];

    protected $casts = [
        'score'       => 'float',
        'is_answered' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
