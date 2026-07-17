<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Models\ChatLog;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Jembatan API mobile untuk chatbot rekomendasi.
 *
 * Memakai ulang App\Services\Chatbot\ChatbotService & model ChatLog yang sama
 * persis dengan versi web (Mahasiswa\ChatbotController) — tidak mengubah logika,
 * basis pengetahuan, maupun database chatbot.
 */
class ChatbotController extends ApiController
{
    /** Data awal layar chatbot: pertanyaan populer + riwayat percakapan terakhir. */
    public function index(Request $request): JsonResponse
    {
        $service = new ChatbotService();

        $history = ChatLog::where('user_id', $request->user()->id)
            ->latest()
            ->take(30)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($log) => [
                'message'     => $log->message,
                'answer'      => $log->answer,
                'is_answered' => (bool) $log->is_answered,
                'category'    => $log->category,
                'date'        => optional($log->created_at)->toDateTimeString(),
            ]);

        return response()->json([
            'popular_questions' => $service->popularQuestions(),
            'history'           => $history,
        ]);
    }

    /**
     * Terima pertanyaan, kembalikan jawaban rekomendasi (JSON) + catat ke ChatLog.
     * Alur identik dengan endpoint web `ask`.
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $service = new ChatbotService();
        $result  = $service->answer($validated['message']);

        try {
            ChatLog::create([
                'user_id'          => $request->user()->id,
                'message'          => $validated['message'],
                'answer'           => $result['answer'],
                'score'            => $result['score'],
                'matched_question' => $result['question'],
                'category'         => $result['category'],
                'is_answered'      => $result['found'],
            ]);
        } catch (\Throwable $e) {
            // abaikan kegagalan pencatatan
        }

        return response()->json($result);
    }
}
