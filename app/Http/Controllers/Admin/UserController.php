<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with('roles')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(), 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan($request), $this->pesan());

        DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->syncRoles($request->user()->can('kelola role pengguna') ? $data['role'] : 'siswa');

            Anggota::create([
                'user_id'    => $user->id,
                'nama'       => $user->name,
                'email'      => $user->email,
                'no_telepon' => $data['no_telepon'] ?? '-',
            ]);
        });

        return redirect()->route('admin.users.index')->with('sukses', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user->load('roles', 'anggota'), 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate($this->aturan($request, $user), $this->pesan());

        $bolehUbahRole = $request->user()->can('kelola role pengguna');

       
        if ($bolehUbahRole && $user->hasRole('admin') && $data['role'] !== 'admin'
            && User::role('admin')->count() <= 1) {
            return back()->withInput()->with('gagal', 'Tidak bisa mengubah role admin terakhir.');
        }

        DB::transaction(function () use ($data, $user, $bolehUbahRole) {
            $user->fill(['name' => $data['name'], 'email' => $data['email']]);

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            if ($bolehUbahRole) {
                $user->syncRoles($data['role']);
            }

            $anggota = $user->anggotaAtauBuat($data['no_telepon'] ?? null);
            $anggota->update(['nama' => $user->name, 'email' => $user->email]);
        });

        return redirect()->route('admin.users.index')->with('sukses', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('gagal', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return back()->with('gagal', 'Tidak bisa menghapus admin terakhir.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('sukses', 'Pengguna berhasil dihapus.');
    }

    private function aturan(Request $request, ?User $user = null): array
    {
        $aturan = [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user?->id)],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'password'   => $user
                ? ['nullable', 'confirmed', Password::defaults()]
                : ['required', 'confirmed', Password::defaults()],
        ];

        if ($request->user()->can('kelola role pengguna')) {
            $aturan['role'] = ['required', Rule::in(Role::pluck('name')->all())];
        }

        return $aturan;
    }

    private function pesan(): array
    {
        return [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah terdaftar.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'role.required'      => 'Role wajib dipilih.',
            'role.in'            => 'Role yang dipilih tidak valid.',
        ];
    }
}
