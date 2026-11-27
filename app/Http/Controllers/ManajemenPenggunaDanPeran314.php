<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanPengguna314;
use App\Models\Role314;
use App\Models\User314;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ManajemenPenggunaDanPeran314 extends Controller
{
    /**
     * Menampilkan daftar pengguna dengan fitur pencarian dan penyaringan.
     */
    public function index(Request $request): View
    {
        $cari = $request->query('cari');
        $peranId = $request->query('peran');
        $status = $request->query('status');

        $query = User314::with('role');

        if ($cari) {
            $query->where(function ($q) use ($cari) {
                $q->where('name', 'LIKE', "%{$cari}%")
                  ->orWhere('email', 'LIKE', "%{$cari}%");
            });
        }

        if ($peranId) {
            $query->where('role_id', $peranId);
        }

        if ($status !== null && $status !== '') {
            $isActive = $status === 'aktif' || $status === '1';
            $query->where('is_active', $isActive);
        }

        $pengguna = $query->orderBy('id', 'asc')->paginate(15)->withQueryString();
        $semuaPeran = DB::table('roles')->get();

        return view('akun.manajemen-pengguna-314.index', [
            'daftarPengguna' => $pengguna,
            'semuaPeran' => $semuaPeran,
            'cari' => $cari,
            'peranId' => $peranId,
            'status' => $status,
        ]);
    }

    /**
     * Menambahkan akun pengguna baru dari panel administrator.
     */
    public function store(SimpanPengguna314 $request): RedirectResponse
    {
        $validated = $request->validated();

        // Aturan satu administrator: akun baru selalu pengguna biasa. Peran administrator hanya
        // berpindah lewat serah terima di daftar pengguna.
        if ($this->peranAdmin((int) $validated['role_id'])) {
            return back()->withInput($request->except('password'))
                ->withErrors(['role_id' => 'Akun administrator hanya boleh satu. Serahkan peran administrator lewat daftar pengguna.']);
        }

        $user = User314::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'campus' => $validated['campus'] ?? 'Telkom University',
            'study_program' => $validated['study_program'] ?? 'S1 Rekayasa Perangkat Lunak',
            'is_active' => true,
        ]);

        // Berikan role spatie jika tersedia
        $role = DB::table('roles')->where('id', $validated['role_id'])->first();
        if ($role && method_exists($user, 'assignRole')) {
            $user->assignRole($role->name);
        }

        return redirect()->route('pengguna.index')->with('sukses', 'Akun pengguna berhasil ditambahkan.');
    }

    /**
     * Mengubah peran pengguna.
     */
    public function peran(Request $request, User314 $pengguna): RedirectResponse
    {
        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ], [
            'role_id.required' => 'Peran akun wajib dipilih.',
            'role_id.exists' => 'Peran yang dipilih tidak valid.',
        ]);

        $roleIdBaru = (int) $request->input('role_id');

        // Aturan satu administrator: menjadikan pengguna lain administrator berarti serah terima.
        // Administrator lama otomatis menjadi pengguna biasa, sehingga administrator tetap satu.
        if ($this->peranAdmin($roleIdBaru) && ! $this->peranAdmin((int) $pengguna->role_id) && (int) $pengguna->id !== (int) auth()->id()) {
            return $this->serahkanAdministrator($pengguna, $roleIdBaru);
        }

        // Aturan: Administrator tidak boleh menurunkan perannya sendiri
        if ((int) $pengguna->id === (int) auth()->id()) {
            return back()->withErrors(['peran' => 'Administrator tidak boleh mengubah atau menurunkan perannya sendiri.']);
        }

        // Aturan: Administrator terakhir tidak boleh diturunkan perannya
        $isSaatIniAdmin = (int) $pengguna->role_id === 1 || ($pengguna->role && in_array(strtolower($pengguna->role->name), ['administrator', 'admin']));
        if ($isSaatIniAdmin && $roleIdBaru !== 1) {
            $totalAdminAktif = User314::where('role_id', 1)->where('is_active', true)->count();
            if ($totalAdminAktif <= 1) {
                return back()->withErrors(['peran' => 'Sistem harus punya minimal satu administrator aktif.']);
            }
        }

        $pengguna->role_id = $roleIdBaru;
        $pengguna->save();

        $role = DB::table('roles')->where('id', $roleIdBaru)->first();
        if ($role && method_exists($pengguna, 'syncRoles')) {
            $pengguna->syncRoles([$role->name]);
        }

        return back()->with('sukses', "Peran untuk {$pengguna->name} berhasil diperbarui.");
    }

    /**
     * Mengubah status aktif / nonaktif (blokir) pengguna.
     */
    public function status(Request $request, User314 $pengguna): RedirectResponse
    {
        // Aturan: Administrator tidak boleh menonaktifkan akunnya sendiri
        if ((int) $pengguna->id === (int) auth()->id()) {
            return back()->withErrors(['status' => 'Administrator tidak boleh menonaktifkan akunnya sendiri.']);
        }

        // Aturan: Administrator terakhir tidak boleh dinonaktifkan
        $isSaatIniAdmin = (int) $pengguna->role_id === 1 || ($pengguna->role && in_array(strtolower($pengguna->role->name), ['administrator', 'admin']));
        if ($isSaatIniAdmin && $pengguna->is_active) {
            $totalAdminAktif = User314::where('role_id', 1)->where('is_active', true)->count();
            if ($totalAdminAktif <= 1) {
                return back()->withErrors(['status' => 'Sistem harus punya minimal satu administrator aktif.']);
            }
        }

        $pengguna->is_active = !$pengguna->is_active;
        $pengguna->save();

        $pesan = $pengguna->is_active 
            ? "Akun {$pengguna->name} berhasil diaktifkan kembali." 
            : "Akun {$pengguna->name} berhasil dinonaktifkan.";

        return back()->with('sukses', $pesan);
    }

    // Peran bernama administrator (modul Akun) atau admin (Modul 2) dianggap peran administrator.
    private function peranAdmin(int $roleId): bool
    {
        $nama = DB::table('roles')->where('id', $roleId)->value('name');

        return in_array(strtolower((string) $nama), ['administrator', 'admin'], true);
    }

    private function serahkanAdministrator(User314 $penerima, int $roleIdAdmin): RedirectResponse
    {
        $rolePengguna = DB::table('roles')->whereIn('name', ['pengguna', 'user'])->orderByRaw("name = 'pengguna' desc")->first();
        if (! $rolePengguna) {
            return back()->withErrors(['peran' => 'Peran pengguna belum tersedia, serah terima dibatalkan.']);
        }

        $idPeranAdmin = DB::table('roles')->whereIn(DB::raw('lower(name)'), ['administrator', 'admin'])->pluck('id');
        $namaPeranAdmin = DB::table('roles')->where('id', $roleIdAdmin)->value('name');

        DB::transaction(function () use ($penerima, $roleIdAdmin, $namaPeranAdmin, $rolePengguna, $idPeranAdmin) {
            // Semua administrator lain (termasuk yang sedang masuk) turun menjadi pengguna biasa.
            User314::where('id', '!=', $penerima->id)->whereIn('role_id', $idPeranAdmin)->get()
                ->each(function (User314 $lama) use ($rolePengguna) {
                    $lama->update(['role_id' => $rolePengguna->id]);
                    $lama->syncRoles([$rolePengguna->name]);
                });

            $penerima->update(['role_id' => $roleIdAdmin]);
            $penerima->syncRoles([$namaPeranAdmin]);
        });

        // Yang menyerahkan sudah bukan administrator, jadi tidak bisa kembali ke panel admin.
        return redirect()->route('dashboard.index')
            ->with('sukses', "Peran administrator diserahkan kepada {$penerima->name}. Akunmu sekarang pengguna biasa.");
    }
}
