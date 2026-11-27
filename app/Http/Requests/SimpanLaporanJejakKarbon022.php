<?php

namespace App\Http\Requests;

use App\Services\RingkasanLaporan022;
use App\Services\RingkasanTarget022;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

// Dipakai saat menyimpan dan mengubah laporan, serta untuk pratinjau (tanpa judul).
class SimpanLaporanJejakKarbon022 extends FormRequest
{
    private const MAKS_HARI = 366;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => $this->routeIs('laporan.pratinjau') ? ['nullable'] : ['required', 'string', 'max:120'],
            'period_start' => ['bail', 'required', 'date_format:Y-m-d'],
            'period_end' => [
                'bail', 'required', 'date_format:Y-m-d', 'after_or_equal:period_start',
                'before_or_equal:'.RingkasanTarget022::hariIni()->toDateString(),
                function (string $atribut, mixed $nilai, Closure $gagal) {
                    // Tanggal mulai yang belum benar sudah punya galatnya sendiri.
                    if (! Carbon::hasFormat((string) $this->input('period_start'), 'Y-m-d')) {
                        return;
                    }

                    $mulai = Carbon::createFromFormat('!Y-m-d', $this->input('period_start'));

                    if ($mulai->diffInDays(Carbon::createFromFormat('!Y-m-d', $nilai)) + 1 > self::MAKS_HARI) {
                        $gagal('Rentang laporan maksimal satu tahun.');
                    }
                },
            ],
            'kategori' => ['nullable', 'array'],
            'kategori.*' => ['integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul laporan wajib diisi.',
            'title.max' => 'Judul laporan maksimal 120 karakter.',
            'period_start.required' => 'Tanggal mulai wajib diisi.',
            'period_start.date_format' => 'Tanggal mulai belum benar.',
            'period_end.required' => 'Tanggal selesai wajib diisi.',
            'period_end.date_format' => 'Tanggal selesai belum benar.',
            'period_end.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'period_end.before_or_equal' => 'Tanggal selesai tidak boleh melewati hari ini.',
            'kategori.array' => 'Kategori yang dipilih tidak tersedia.',
            'kategori.*.integer' => 'Kategori yang dipilih tidak tersedia.',
            'kategori.*.exists' => 'Kategori yang dipilih tidak tersedia.',
        ];
    }

    /** @return int[] kosong berarti semua kategori */
    public function kategoriIds(): array
    {
        return RingkasanLaporan022::rapikanLingkup($this->validated('kategori') ?? []);
    }

    // Nilai kolom saved_reports yang siap disimpan.
    public function kolom(): array
    {
        return [
            'title' => $this->validated('title'),
            'period_start' => $this->validated('period_start'),
            'period_end' => $this->validated('period_end'),
            'category_scope' => implode(',', $this->kategoriIds()),
        ];
    }
}
