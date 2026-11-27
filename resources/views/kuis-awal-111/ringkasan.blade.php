@extends('layouts.aplikasi-111')

@section('judul', 'Hasil Kuis Awal')

@use('App\Services\PerhitunganEmisi111')
@use('Illuminate\Support\Carbon')

{{-- KF-25 Kuis Awal (Alvin-111): ringkasan untuk administrator. Soalnya dikelola di Bank Soal Kuis. --}}
@section('isi')
    <x-notifikasi />

    <p class="ka-pengantar">
        Hasil kuis awal seluruh pengguna. Soalnya diambil dari
        <a href="{{ route('kuis.index') }}">Bank Soal Kuis</a>; hanya soal aktif yang dihitung.
    </p>

    <section class="ka-angka" aria-label="Ringkasan">
        <article class="ct-kartu"><h2 class="ka-angka__label">Pengguna aktif</h2><p class="ka-angka__nilai">{{ $jumlahPengguna }}</p></article>
        <article class="ct-kartu"><h2 class="ka-angka__label">Sudah mengisi</h2><p class="ka-angka__nilai">{{ $sudahMengisi }}</p></article>
        <article class="ct-kartu"><h2 class="ka-angka__label">Melewati</h2><p class="ka-angka__nilai">{{ $melewati }}</p></article>
        <article class="ct-kartu">
            <h2 class="ka-angka__label">Rata-rata garis dasar</h2>
            <p class="ka-angka__nilai">{{ $rataGarisDasar === null ? '-' : PerhitunganEmisi111::angka($rataGarisDasar, 2, true) }} <span>kg/hari</span></p>
        </article>
    </section>

    <section class="ct-kartu ka-kartu" aria-labelledby="ka-sebaran">
        <h2 class="ct-kartu__judul" id="ka-sebaran">Sebaran jawaban per soal</h2>
        @forelse ($soal as $s)
            @php
                $baris = $sebaran->get($s->id, collect())->keyBy('answer_label');
                $jumlahSoal = max(1, $baris->sum('jumlah'));
            @endphp
            <div class="ka-sebaran">
                <p class="ka-sebaran__soal">{{ $loop->iteration }}. {{ $s->question }}</p>
                @foreach ($s->options as $opsi)
                    @php $n = (int) ($baris->get($opsi['label'])->jumlah ?? 0); @endphp
                    <div class="ka-sebaran__baris">
                        <span class="ka-sebaran__label">{{ $opsi['label'] }}</span>
                        <span class="ka-sebaran__batang" aria-hidden="true"><span style="width: {{ round($n / $jumlahSoal * 100) }}%"></span></span>
                        <span class="ka-sebaran__jumlah">{{ $n }} orang</span>
                    </div>
                @endforeach
            </div>
        @empty
            <p class="ka-pengantar">Belum ada soal aktif di Bank Soal Kuis.</p>
        @endforelse
    </section>

    <section class="ct-kartu ka-kartu" aria-labelledby="ka-daftar">
        <h2 class="ct-kartu__judul" id="ka-daftar">Pengguna yang sudah mengisi</h2>
        @if ($daftar->isEmpty())
            <p class="ka-pengantar">Belum ada pengguna yang mengisi kuis awal.</p>
        @else
            <table class="ct-tabel">
                <thead><tr><th scope="col">Pengguna</th><th scope="col" class="angka">Soal dijawab</th><th scope="col" class="angka">Garis dasar (kg/hari)</th><th scope="col">Terakhir diisi</th></tr></thead>
                <tbody>
                    @foreach ($daftar as $d)
                        <tr>
                            <td data-label="Pengguna"><strong>{{ $d->name }}</strong><br><span class="ka-redup">{{ $d->email }}</span></td>
                            <td data-label="Soal dijawab" class="angka">{{ $d->terjawab }} / {{ $soal->count() }}</td>
                            <td data-label="Garis dasar" class="angka">{{ PerhitunganEmisi111::angka((float) $d->garis_dasar, 2, true) }}</td>
                            <td data-label="Terakhir diisi">{{ $d->terakhir ? Carbon::parse($d->terakhir)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H.i') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection
