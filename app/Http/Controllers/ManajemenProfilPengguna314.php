<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanManajemenProfilPengguna314;
use App\Models\User314;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ManajemenProfilPengguna314 extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        Gate::authorize('view', $user);

        return view('akun-dan-administrasi-pengguna.manajemen-profil-pengguna-314.index', [
            'user' => $user,
        ]);
    }

    public function update(SimpanManajemenProfilPengguna314 $request): RedirectResponse
    {
        $user = $request->user();
        Gate::authorize('update', $user);
        $validated = $request->validated();

        if ($request->boolean('hapus_foto')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = null;
        } elseif ($request->hasFile('photo')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profil.index')->with('sukses', 'Profil tersimpan.');
    }

    public function sandi(Request $request): RedirectResponse
    {
        $user = $request->user();
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profil.index')->with('sukses', 'Kata sandi berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        Gate::authorize('delete', $user);

        // Administrator terakhir tidak boleh dinonaktifkan
        if ($user->hasRole('administrator') || $user->hasRole('admin')) {
            $activeAdminCount = User314::role(['administrator', 'admin'])
                ->where('is_active', true)
                ->count();

            if ($activeAdminCount <= 1) {
                return redirect()->route('profil.index')->withErrors([
                    'nonaktifkan' => 'Administrator terakhir yang aktif tidak dapat dinonaktifkan.',
                ]);
            }
        }

        // Validasi kata sandi hanya jika diisi (opsional untuk konfirmasi ekstra)
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['current_password'],
            ], [
                'password.current_password' => 'Kata sandi tidak cocok.',
            ]);
        }

        // Soft delete: is_active menjadi 0
        $user->update(['is_active' => false]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('sukses', 'Akun Anda berhasil dinonaktifkan.');
    }
}
