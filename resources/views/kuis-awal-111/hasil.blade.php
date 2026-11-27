@extends('layouts.aplikasi-111')

@section('judul', 'Garis Dasar Emisi')

@use('App\Services\PerhitunganEmisi111')

{{-- KF-25 Kuis Awal (Alvin-111): hasil garis dasar dan rincian jawaban --}}
@section('isi')
    <x-notifikasi />

    <div class="ka-wadah">
        <section class="ct-kartu ka-kartu ka-hasil">
            <p class="ka-hasil__label">Garis dasar emisimu</p>
            <p class="ka-hasil__angka">{{ PerhitunganEmisi111::angka($garis['nilai'], 2, true) }} <span>kg CO2e per hari</span></p>
            <p class="ka-hasil__ket">
                Perkiraan dari {{ $garis['terjawab'] }} jawaban kuis awal. Angka ini menjadi pembanding awal di dashboard.
                @if ($rataCatatan !== null)
                    Rata-rata catatanmu 30 hari terakhir {{ PerhitunganEmisi111::angka($rataCatatan, 2, true) }} kg per hari,
                    {{ $rataCatatan <= $garis['nilai'] ? 'sudah di bawah garis dasar.' : 'masih di atas garis dasar.' }}
                @endif
            </p>
            @if ($garis['terjawab'] < $garis['total'])
                <p class="ka-hasil__ket">Ada {{ $garis['total'] - $garis['terjawab'] }} soal baru yang belum kamu jawab.</p>
            @endif
            <div class="ka-aksi">
                <a href="{{ route('kuis-awal.mulai', ['soal' => 1]) }}" class="ct-tombol ct-tombol--sekunder">Ulangi Kuis</a>
                <a href="{{ route('dashboard.index') }}" class="ct-tombol ct-tombol--utama">Ke Dashboard</a>
            </div>
        </section>

        <section class="ct-kartu ka-kartu" aria-labelledby="ka-rincian">
            <h2 class="ct-kartu__judul" id="ka-rincian">Jawabanmu</h2>
            <table class="ct-tabel">
                <thead><tr><th scope="col">Pertanyaan</th><th scope="col">Jawaban</th><th scope="col" class="angka">Nilai</th></tr></thead>
                <tbody>
                    @foreach ($rincian as ['soal' => $s, 'jawaban' => $j])
                        <tr>
                            <td data-label="Pertanyaan">{{ $s->question }}</td>
                            <td data-label="Jawaban">{{ $j->answer_label ?? 'Belum dijawab' }}</td>
                            <td data-label="Nilai" class="angka">{{ $j ? PerhitunganEmisi111::angka((float) $j->score, 2, true) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
@endsection
