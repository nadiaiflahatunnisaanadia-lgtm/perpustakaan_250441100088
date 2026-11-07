<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Profil SELALU milik user yang sedang login ($request->user()).
     * Tidak ada parameter {id} di URL, sehingga profil orang lain tidak bisa dibuka/diubah.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user'    => $request->user(),
            'anggota' => $request->user()->anggota,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email ini sudah dipakai akun lain.',
        ]);

        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        // Jaga data anggota tetap sinkron dengan akun.
        $anggota = $user->anggotaAtauBuat($data['no_telepon'] ?? null);
        $anggota->update([
            'nama'       => $user->name,
            'email'      => $user->email,
            'no_telepon' => ($data['no_telepon'] ?? null) ?: $anggota->no_telepon,
        ]);

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
    }
}
