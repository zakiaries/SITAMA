<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Menangani unggah foto profil (field 'photo') untuk semua peran.
 * Dipakai controller web (opsional, menyatu dengan form edit) maupun API
 * (endpoint khusus, wajib ada file).
 */
trait HandlesProfilePhoto
{
    /**
     * Simpan foto profil dari request ke user.
     *
     * @param  bool  $required  true untuk endpoint khusus foto (file wajib);
     *                          false untuk form edit gabungan (foto opsional).
     * @return string|null  path foto baru, atau null bila tak ada file diunggah.
     */
    protected function storeProfilePhoto(Request $request, User $user, bool $required = false): ?string
    {
        if (! $required && ! $request->hasFile('photo')) {
            return null;
        }

        $request->validate([
            'photo' => ($required ? 'required|' : '') . 'image|mimes:jpg,jpeg,png|max:4096',
        ], [
            'photo.required' => 'Silakan pilih foto terlebih dahulu.',
            'photo.image'    => 'Foto harus berupa gambar.',
            'photo.mimes'    => 'Foto harus berformat JPG atau PNG.',
            'photo.max'      => 'Ukuran foto maksimal 4 MB.',
        ]);

        // Hapus foto lama agar storage tidak menumpuk.
        if ($user->photo_profile) {
            Storage::disk('public')->delete($user->photo_profile);
        }

        $path = $request->file('photo')->store('avatars', 'public');
        $user->update(['photo_profile' => $path]);

        return $path;
    }
}
