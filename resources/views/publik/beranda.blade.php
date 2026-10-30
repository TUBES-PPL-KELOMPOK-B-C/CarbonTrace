@extends('layouts.publik-022')

@section('judul', 'CarbonTrace — Platform Pelacakan dan Pemangkasan Jejak Karbon Harian')

@section('isi')
    @include('publik.beranda._latar-022')
    @include('publik.beranda._navbar-022')

    <main>
        @include('publik.beranda._hero-022')
        @include('publik.beranda._masalah-022')
        @include('publik.beranda._cara-kerja-022')
        @include('publik.beranda._fitur-022')
        @include('publik.beranda._pratinjau-022')
        @include('publik.beranda._angka-022')
        @include('publik.beranda._edukasi-022')
        @include('publik.beranda._cta-022')
    </main>

    @include('publik.beranda._footer-022')
@endsection
