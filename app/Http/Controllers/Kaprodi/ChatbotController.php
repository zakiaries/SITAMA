<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\ChatbotKnowledge;
use App\Models\ChatLog;
use Illuminate\Http\Request;

/**
 * Manajemen basis pengetahuan (FAQ) chatbot oleh Kaprodi.
 *
 * Perubahan pada entri otomatis melatih ulang model TF-IDF chatbot karena
 * cache model di-kunci berdasarkan hash isi KB (lihat ChatbotService).
 */
class ChatbotController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all'); // all | active | inactive

        $query = ChatbotKnowledge::query();
        if ($status === 'active')   $query->where('is_active', true);
        if ($status === 'inactive') $query->where('is_active', false);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pertanyaan', 'like', "%$search%")
                  ->orWhere('kata_kunci', 'like', "%$search%")
                  ->orWhere('kategori', 'like', "%$search%");
            });
        }

        $items = $query->orderBy('kategori')->orderBy('id')->get();

        $counts = [
            'all'      => ChatbotKnowledge::count(),
            'active'   => ChatbotKnowledge::where('is_active', true)->count(),
            'inactive' => ChatbotKnowledge::where('is_active', false)->count(),
        ];

        return view('kaprodi.chatbot.index', compact('items', 'status', 'counts'));
    }

    /**
     * Riwayat percakapan + statistik chatbot.
     * Menyoroti pertanyaan yang belum terjawab agar KB bisa diperkaya.
     */
    public function logs(Request $request)
    {
        $total          = ChatLog::count();
        $answered       = ChatLog::where('is_answered', true)->count();
        $unansweredNum  = $total - $answered;
        $rate           = $total > 0 ? round($answered / $total * 100) : 0;

        $stats = compact('total', 'answered', 'unansweredNum', 'rate');

        // Pertanyaan tak terjawab yang paling sering muncul (untuk perbaikan KB).
        $topUnanswered = ChatLog::where('is_answered', false)
            ->selectRaw('message, COUNT(*) as cnt, MAX(created_at) as last_at')
            ->groupBy('message')
            ->orderByDesc('cnt')
            ->limit(15)
            ->get();

        $filter = $request->input('filter', 'all'); // all | answered | unanswered
        $query  = ChatLog::with('user')->latest();
        if ($filter === 'answered')   $query->where('is_answered', true);
        if ($filter === 'unanswered') $query->where('is_answered', false);

        $logs = $query->paginate(20)->withQueryString();

        return view('kaprodi.chatbot.logs', compact('stats', 'topUnanswered', 'logs', 'filter'));
    }

    public function create()
    {
        return view('kaprodi.chatbot.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        ChatbotKnowledge::create($this->validated($request));

        return redirect()->route('kaprodi.chatbot.index')
            ->with('success', 'Entri FAQ chatbot berhasil ditambahkan.');
    }

    public function edit(ChatbotKnowledge $chatbotKnowledge)
    {
        return view('kaprodi.chatbot.form', ['item' => $chatbotKnowledge]);
    }

    public function update(Request $request, ChatbotKnowledge $chatbotKnowledge)
    {
        $chatbotKnowledge->update($this->validated($request));

        return redirect()->route('kaprodi.chatbot.index')
            ->with('success', 'Entri FAQ chatbot berhasil diperbarui.');
    }

    public function toggle(ChatbotKnowledge $chatbotKnowledge)
    {
        $chatbotKnowledge->update(['is_active' => ! $chatbotKnowledge->is_active]);

        return back()->with('success', 'Status entri FAQ diperbarui.');
    }

    public function destroy(ChatbotKnowledge $chatbotKnowledge)
    {
        $chatbotKnowledge->delete();

        return back()->with('success', 'Entri FAQ chatbot berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'pertanyaan' => 'required|string|max:500',
            'kata_kunci' => 'required|string|max:2000',
            'jawaban'    => 'required|string|max:2000',
            'kategori'   => 'required|string|max:100',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
