@if ($pengguna)
    <a href="{{ route('dashboard.index') }}" class="lp-pelat-tombol lp-pelat-tombol--pengguna" title="Buka dashboard {{ $pengguna->name }}">
        <span class="lp-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($pengguna->name, 0, 1)) }}</span>
        <span class="lp-pengguna__nama">{{ \Illuminate\Support\Str::before($pengguna->name, ' ') ?: $pengguna->name }}</span>
    </a>
    <form method="POST" action="{{ route('auth.keluar') }}" class="lp-nav__keluar">
        @csrf
        <button type="submit" class="lp-pelat-tombol lp-pelat-tombol--hantu">
            <svg class="lp-ikon-kecil" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 16l-4-4 4-4M6 12h10"/></svg>
            Keluar
        </button>
    </form>
@else
    <a href="{{ route('auth.masuk') }}" class="lp-pelat-tombol lp-pelat-tombol--hantu">
        <svg class="lp-ikon-kecil" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></svg>
        Masuk
    </a>
    <a href="{{ route('auth.daftar') }}" class="lp-pelat-tombol lp-pelat-tombol--isi">
        <svg class="lp-ikon-kecil" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 19c0-8 5-14 14-14 0 9-6 14-14 14Z"/><path d="M5 19 13 11"/></svg>
        Daftar Gratis
    </a>
@endif
