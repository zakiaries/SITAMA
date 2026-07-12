<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Entri basis pengetahuan (FAQ) chatbot yang dikelola oleh Kaprodi.
 */
class ChatbotKnowledge extends Model
{
    use HasFactory;

    // "knowledge" dianggap uncountable oleh inflector Laravel, jadi nama
    // tabel dipatok manual agar cocok dengan migrasi (chatbot_knowledges).
    protected $table = 'chatbot_knowledges';

    protected $fillable = [
        'pertanyaan', 'kata_kunci', 'jawaban', 'kategori', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
