<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\SeminarAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Form berita acara publik: tamu memindai QR seminar lalu mengisi identitas
 * dan tanda tangan. Tanpa autentikasi (mirip alur aktivasi akun).
 */
class BeritaAcaraController extends Controller
{
    public function show(string $token)
    {
        $seminar = Seminar::where('access_token', $token)->first();

        if (!$seminar || $seminar->status !== 'scheduled') {
            return view('public.berita-acara-closed', ['seminar' => $seminar]);
        }

        return view('public.berita-acara', compact('seminar'));
    }

    public function store(Request $request, string $token)
    {
        $seminar = Seminar::where('access_token', $token)->first();

        if (!$seminar || $seminar->status !== 'scheduled') {
            return view('public.berita-acara-closed', ['seminar' => $seminar]);
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'nim'       => 'required|string|max:50',
            'kelas'     => 'nullable|string|max:50',
            'prodi'     => 'nullable|string|max:100',
            'signature' => 'required|string', // data URL PNG dari canvas
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'nim.required'       => 'NIM wajib diisi.',
            'signature.required' => 'Tanda tangan wajib diisi.',
        ]);

        // Cegah tanda tangan ganda dari NIM yang sama pada seminar ini.
        $exists = SeminarAttendance::where('seminar_id', $seminar->id)
            ->where('nim', $validated['nim'])->exists();
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['nim' => 'NIM ini sudah mengisi berita acara seminar ini.']);
        }

        $signaturePath = $this->storeSignature($seminar, $validated['signature']);
        if (!$signaturePath) {
            return back()->withInput()->withErrors(['signature' => 'Tanda tangan tidak valid, silakan ulangi.']);
        }

        SeminarAttendance::create([
            'seminar_id'     => $seminar->id,
            'name'           => $validated['name'],
            'nim'            => $validated['nim'],
            'kelas'          => $validated['kelas'] ?? null,
            'prodi'          => $validated['prodi'] ?? null,
            'signature_path' => $signaturePath,
        ]);

        return view('public.berita-acara-success', compact('seminar'));
    }

    /** Simpan data URL base64 (image/png) menjadi file di disk public. */
    private function storeSignature(Seminar $seminar, string $dataUrl): ?string
    {
        if (!preg_match('/^data:image\/png;base64,/', $dataUrl)) {
            return null;
        }

        $encoded = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $binary  = base64_decode(strtr($encoded, ' ', '+'), true);
        if ($binary === false) {
            return null;
        }

        $path = 'berita-acara/' . $seminar->id . '/' . Str::random(24) . '.png';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
