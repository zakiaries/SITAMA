<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ChatLog;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    /**
     * Halaman chatbot rekomendasi.
     */
    public function index()
    {
        $service = new ChatbotService();

        return view('mahasiswa.chatbot.index', [
            'popularQuestions' => $service->popularQuestions(),
        ]);
    }

    /**
     * Endpoint AJAX: terima pertanyaan, kembalikan jawaban rekomendasi (JSON).
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $service = new ChatbotService();
        $result  = $service->answer($validated['message']);

        // Catat percakapan untuk pemantauan & perbaikan KB (jangan sampai
        // kegagalan pencatatan mengganggu balasan chatbot).
        try {
            ChatLog::create([
                'user_id'          => Auth::id(),
                'message'          => $validated['message'],
                'answer'           => $result['answer'],
                'score'            => $result['score'],
                'matched_question' => $result['question'],
                'category'         => $result['category'],
                'is_answered'      => $result['found'],
            ]);
        } catch (\Throwable $e) {
            // abaikan
        }

        return response()->json($result);
    }
}
