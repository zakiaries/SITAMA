<?php

namespace Database\Seeders;

use App\Models\ChatbotKnowledge;
use App\Services\Chatbot\KnowledgeBase;
use Illuminate\Database\Seeder;

class ChatbotKnowledgeSeeder extends Seeder
{
    /**
     * Isi tabel chatbot_knowledges dari daftar FAQ bawaan.
     * Idempoten: hanya menambah entri yang pertanyaannya belum ada.
     */
    public function run(): void
    {
        foreach (KnowledgeBase::defaultEntries() as $entry) {
            ChatbotKnowledge::firstOrCreate(
                ['pertanyaan' => $entry['pertanyaan']],
                [
                    'kata_kunci' => $entry['kata_kunci'],
                    'jawaban'    => $entry['jawaban'],
                    'kategori'   => $entry['kategori'],
                    'is_active'  => true,
                ]
            );
        }
    }
}
