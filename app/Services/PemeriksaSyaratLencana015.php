<?php

namespace App\Services;

use App\Models\Badge015;
use App\Models\User314;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// PBI-13: satu-satunya tempat yang memeriksa syarat lencana dan menulis ke badge_user.
// Dipanggil setiap kali poin atau emisi pengguna berubah, dan saat galeri dibuka.
class PemeriksaSyaratLencana015
{
    // Capaian pengguna untuk keempat jenis syarat, dikumpulkan dengan dua kueri saja.
    // Hanya MEMBACA tabel milik anggota lain (users dan activities), tidak pernah mengubahnya.
    public function capaian(User314 $pengguna): array
    {
        $aktivitas = DB::table('activities')
            ->where('user_id', $pengguna->id)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(emission), 0) as emisi')
            ->first();

        return [
            'poin' => (float) $pengguna->points,
            'runtutan' => (float) $pengguna->streak_count,
            'aktivitas' => (float) ($aktivitas->jumlah ?? 0),
            'emisi' => (float) ($aktivitas->emisi ?? 0),
        ];
    }

    // Memeriksa seluruh lencana aktif dan memberikan yang syaratnya sudah terpenuhi.
    // Mengembalikan lencana yang BARU diraih pada pemeriksaan ini, untuk modal perayaan.
    public function periksa(User314 $pengguna): Collection
    {
        $capaian = $this->capaian($pengguna);
        $sudahDiraih = $this->idYangSudahDiraih($pengguna);

        $layak = Badge015::aktif()
            ->whereNotIn('id', $sudahDiraih->all() ?: [0])
            ->get()
            ->filter(fn (Badge015 $lencana) => $this->memenuhi($lencana, $capaian))
            ->values();

        if ($layak->isEmpty()) {
            return $layak;
        }

        $sekarang = now();

        // insertOrIgnore, bukan insert: kalau dua permintaan memeriksa di detik yang sama, baris
        // kedua ditolak kunci unik badge_user tanpa melemparkan galat ke pengguna.
        DB::table('badge_user')->insertOrIgnore(
            $layak->map(fn (Badge015 $lencana) => [
                'user_id' => $pengguna->id,
                'badge_id' => $lencana->id,
                'earned_at' => $sekarang,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ])->all()
        );

        return $layak;
    }

    // Satu lencana memenuhi syarat bila capaian pada jenisnya sudah menyamai required_value.
    // Jenis yang tidak dikenali tidak pernah diberikan, supaya salah ketik tidak membagi lencana.
    public function memenuhi(Badge015 $lencana, array $capaian): bool
    {
        if (! array_key_exists($lencana->type, $capaian)) {
            return false;
        }

        return $capaian[$lencana->type] >= (float) $lencana->required_value;
    }

    // Daftar lencana untuk galeri pengguna: yang sudah diraih beserta tanggalnya, dan yang masih
    // terkunci beserta syarat dan kemajuannya. Lencana nonaktif hilang dari galeri KECUALI bagi
    // pengguna yang sudah meraihnya.
    public function galeri(User314 $pengguna): Collection
    {
        $capaian = $this->capaian($pengguna);

        $tanggalRaih = DB::table('badge_user')
            ->where('user_id', $pengguna->id)
            ->pluck('earned_at', 'badge_id');

        return Badge015::query()
            ->where(fn ($kueri) => $kueri->where('is_active', true)->orWhereIn('id', $tanggalRaih->keys()))
            ->orderBy('type')
            ->orderBy('required_value')
            ->get()
            ->map(function (Badge015 $lencana) use ($capaian, $tanggalRaih) {
                $diraih = $tanggalRaih->has($lencana->id);
                $capaianJenis = $capaian[$lencana->type] ?? 0.0;
                $syarat = (float) $lencana->required_value;

                return [
                    'lencana' => $lencana,
                    'diraih' => $diraih,
                    'earned_at' => $diraih && $tanggalRaih[$lencana->id]
                        ? \Illuminate\Support\Carbon::parse($tanggalRaih[$lencana->id])
                        : null,
                    'capaian' => $capaianJenis,
                    // Persen dibatasi 100 supaya batang kemajuan tidak melimpah keluar kartunya.
                    'persen' => $syarat > 0 ? min(100, (int) round($capaianJenis / $syarat * 100)) : 100,
                ];
            });
    }

    public function idYangSudahDiraih(User314 $pengguna): Collection
    {
        return DB::table('badge_user')->where('user_id', $pengguna->id)->pluck('badge_id');
    }

    // Berapa pengguna yang sudah meraih satu lencana. Dipakai saat admin mencoba menghapusnya.
    public function jumlahPeraih(Badge015 $lencana): int
    {
        return DB::table('badge_user')->where('badge_id', $lencana->id)->count();
    }
}
