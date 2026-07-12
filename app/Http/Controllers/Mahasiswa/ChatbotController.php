<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return response()->json($result);
    }
}
