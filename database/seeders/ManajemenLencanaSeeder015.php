<?php

namespace Database\Seeders;

use App\Models\Badge015;
use Illuminate\Database\Seeder;

// PBI-13: lencana awal supaya galeri pengguna baru tidak kosong dan admin punya contoh bentuk.
class ManajemenLencanaSeeder015 extends Seeder
{
    public function run(): void
    {
        $lencana = [
            // Jalur poin: hadiah dari tantangan dan kuis menumpuk di kolom users.points.
            ['Langkah Pertama', 'Kumpulkan 50 poin pertamamu.', 'bintang', 'poin', 50, true],
            ['Pengumpul Poin', 'Kumpulkan 500 poin.', 'piala', 'poin', 500, true],
            ['Juru Kunci Hijau', 'Kumpulkan 1.000 poin.', 'mahkota', 'poin', 1000, true],

            // Jalur runtutan: hari berturut-turut mencatat aktivitas.
            ['Tujuh Hari Lurus', 'Catat aktivitas tujuh hari berturut-turut.', 'api', 'runtutan', 7, true],
            ['Sebulan Konsisten', 'Catat aktivitas 30 hari berturut-turut.', 'kalender', 'runtutan', 30, true],

            // Jalur aktivitas: banyaknya catatan yang masuk, bukan besar emisinya.
            ['Pencatat Rajin', 'Catat 25 aktivitas.', 'buku', 'aktivitas', 25, true],
            ['Arsip Pribadi', 'Catat 100 aktivitas.', 'kotak', 'aktivitas', 100, true],

            // Jalur emisi: jumlah emisi yang sudah tercatat, tanda pengguna benar-benar memakai alatnya.
            ['Sadar Jejak', 'Catat 100 kg CO2e emisi.', 'jejak', 'emisi', 100, true],
            ['Peta Jejak Lengkap', 'Catat 1.000 kg CO2e emisi.', 'bumi', 'emisi', 1000, true],

            // Contoh lencana nonaktif, supaya admin melihat bentuk keadaan itu di kisi kartu.
            ['Perintis Beta', 'Lencana penguji awal CarbonTrace.', 'roket', 'poin', 10, false],
        ];

        foreach ($lencana as [$nama, $uraian, $ikon, $jenis, $syarat, $aktif]) {
            Badge015::firstOrCreate(
                ['name' => $nama],
                [
                    'description' => $uraian,
                    'icon' => $ikon,
                    'type' => $jenis,
                    'required_value' => $syarat,
                    'is_active' => $aktif,
                ]
            );
        }
    }
}
