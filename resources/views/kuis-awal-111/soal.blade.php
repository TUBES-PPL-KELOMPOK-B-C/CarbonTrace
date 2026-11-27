@extends('layouts.aplikasi-111')

@section('judul', 'Kuis Awal')

{{-- KF-25 Kuis Awal (Alvin-111): satu soal per layar, jawaban tersimpan setiap pindah soal --}}
@section('isi')
    <x-notifikasi />

    <div class="ka-wadah">
        <section class="ct-kartu ka-kartu" aria-labelledby="ka-soal">
            <div class="ka-kemajuan">
                <p class="ka-kemajuan__teks">Soal {{ $nomor }} dari {{ $total }}</p>
                <div class="ka-kemajuan__batang" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $nomor }}" aria-label="Kemajuan kuis">
                    <span style="width: {{ round($nomor / $total * 100) }}%"></span>
                </div>
            </div>

            <form method="POST" action="{{ route('kuis-awal.jawab') }}" class="ka-form">
                @csrf
                <input type="hidden" name="soal_id" value="{{ $soal->id }}">

                <fieldset class="ka-pilihan" @error('pilihan') aria-describedby="ka-galat" @enderror>
                    <legend class="ka-soal" id="ka-soal">{{ $soal->question }}</legend>

                    @foreach ($soal->options as $i => $opsi)
                        <label class="ka-opsi">
                            <input type="radio" name="pilihan" value="{{ $i }}" @checked((string) old('pilihan', $terpilih) === (string) $i) required>
                            <span>{{ $opsi['label'] }}</span>
                        </label>
                    @endforeach
                </fieldset>
                @error('pilihan')<p class="ct-galat" id="ka-galat">{{ $message }}</p>@enderror

                <div class="ka-aksi">
                    @if ($nomor > 1)
                        <a href="{{ route('kuis-awal.mulai', ['soal' => $nomor - 1]) }}" class="ct-tombol ct-tombol--sekunder">Kembali</a>
                    @else
                        <span></span>
                    @endif
                    <button type="submit" class="ct-tombol ct-tombol--utama">{{ $nomor < $total ? 'Lanjut' : 'Lihat Garis Dasar' }}</button>
                </div>
            </form>
        </section>

        <form method="POST" action="{{ route('kuis-awal.lewati') }}" class="ka-lewati">
            @csrf
            <p>Jawabanmu dipakai untuk menghitung garis dasar emisi, angka awal pembanding di dashboard.</p>
            <button type="submit" class="ct-tombol ct-tombol--hantu">Lewati kuis untuk sekarang</button>
        </form>
    </div>
@endsection
