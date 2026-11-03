<?php

namespace Database\Seeders;

use App\Models\QuizQuestion221;
use Illuminate\Database\Seeder;

class ManajemenBankPertanyaanKuisSeeder221 extends Seeder
{
    public function run(): void
    {
        $soalList = [
            [
                'question' => 'Bagaimana caramu bepergian sehari-hari ke kampus atau tempat kerja?',
                'options' => [
                    ['label' => 'Jalan kaki atau bersepeda', 'weight' => 0.0],
                    ['label' => 'Transportasi umum (KRL / Bus / Angkot)', 'weight' => 1.5],
                    ['label' => 'Sepeda motor pribadi', 'weight' => 3.0],
                    ['label' => 'Mobil pribadi bensin', 'weight' => 6.5],
                ],
                'emission_weight' => 2.5000,
                'is_active' => true,
            ],
            [
                'question' => 'Berapa rata-rata jam kamu menyalakan pendingin ruangan (AC) setiap hari?',
                'options' => [
                    ['label' => 'Tidak memakai AC sama sekali', 'weight' => 0.0],
                    ['label' => 'Kurang dari 4 jam', 'weight' => 1.2],
                    ['label' => 'Antara 4 sampai 8 jam', 'weight' => 2.8],
                    ['label' => 'Lebih dari 8 jam', 'weight' => 5.2],
                ],
                'emission_weight' => 2.0000,
                'is_active' => true,
            ],
            [
                'question' => 'Seberapa sering kamu mengonsumsi daging merah (sapi/kambing) dalam seminggu?',
                'options' => [
                    ['label' => 'Jarang atau vegetarian', 'weight' => 0.2],
                    ['label' => '1 - 2 kali seminggu', 'weight' => 2.0],
                    ['label' => '3 - 5 kali seminggu', 'weight' => 4.5],
                    ['label' => 'Hampir setiap hari', 'weight' => 8.0],
                ],
                'emission_weight' => 1.8000,
                'is_active' => true,
            ],
            [
                'question' => 'Berapa banyak orang yang tinggal bersama di rumah atau tempat tinggalmu?',
                'options' => [
                    ['label' => 'Sendiri (kos / studio)', 'weight' => 3.5],
                    ['label' => '2 sampai 3 orang', 'weight' => 2.2],
                    ['label' => '4 sampai 5 orang', 'weight' => 1.5],
                    ['label' => 'Lebih dari 5 orang', 'weight' => 1.0],
                ],
                'emission_weight' => 1.2000,
                'is_active' => true,
            ],
            [
                'question' => 'Bagaimana kebiasaanmu dalam memilah sampah di rumah?',
                'options' => [
                    ['label' => 'Selalu memilah organik dan anorganik untuk didaur ulang', 'weight' => 0.2],
                    ['label' => 'Kadang memilah botol dan kardus saja', 'weight' => 1.8],
                    ['label' => 'Tidak pernah memilah, semua digabung jadi satu', 'weight' => 3.5],
                ],
                'emission_weight' => 1.0000,
                'is_active' => true,
            ],
            [
                'question' => 'Berapa sering kamu membeli minuman kemasan sekali pakai dalam sehari?',
                'options' => [
                    ['label' => 'Selalu bawa botol minum sendiri (tumbler)', 'weight' => 0.1],
                    ['label' => '1 botol per hari', 'weight' => 1.0],
                    ['label' => '2 sampai 3 botol per hari', 'weight' => 2.8],
                    ['label' => 'Lebih dari 3 botol per hari', 'weight' => 4.5],
                ],
                'emission_weight' => 1.0000,
                'is_active' => true,
            ],
        ];

        foreach ($soalList as $soal) {
            QuizQuestion221::firstOrCreate(
                ['question' => $soal['question']],
                $soal
            );
        }
    }
}
