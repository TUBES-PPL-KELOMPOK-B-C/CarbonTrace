<?php

namespace App\Http\Requests;

use App\Models\Reminder022;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class SimpanPengingatPencatatanHarian022 extends FormRequest
{
    public const MAKS_AKTIF = 3;

    public function authorize(): bool
    {
        return true;
    }

    // "8.30" atau "8:30" dari kolom ketik dirapikan menjadi "08:30".
    protected function prepareForValidation(): void
    {
        $jam = trim((string) $this->input('remind_time'));

        if (preg_match('/^(\d{1,2})[.:](\d{2})$/', $jam, $m)) {
            $this->merge(['remind_time' => sprintf('%02d:%s', $m[1], $m[2])]);
        }
    }

    public function rules(): array
    {
        $pengingat = $this->route('pengingat');

        return [
            'remind_time' => [
                'bail', 'required', 'date_format:H:i',
                // Satu pengguna tidak boleh punya dua pengingat di jam yang sama.
                function (string $atribut, mixed $nilai, Closure $gagal) use ($pengingat) {
                    $kembar = Reminder022::where('user_id', $this->user()->id)
                        ->where('remind_time', $nilai.':00')
                        ->when($pengingat, fn ($q) => $q->whereKeyNot($pengingat->id))
                        ->exists();

                    if ($kembar) {
                        $gagal('Sudah ada pengingat di jam ini.');
                    }
                },
                // Pengingat baru selalu aktif, jadi jumlah yang aktif tidak boleh melebihi tiga.
                function (string $atribut, mixed $nilai, Closure $gagal) use ($pengingat) {
                    if (! $pengingat && self::jumlahAktif($this->user()->id) >= self::MAKS_AKTIF) {
                        $gagal('Maksimal tiga pengingat aktif.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'remind_time.required' => 'Jam pengingat wajib diisi.',
            'remind_time.date_format' => 'Gunakan format jam 24 jam, misalnya 20:00.',
        ];
    }

    public static function jumlahAktif(int $penggunaId): int
    {
        return Reminder022::where('user_id', $penggunaId)->where('is_active', true)->count();
    }
}
