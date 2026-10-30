@php
    // Kisi karbon heksagonal (struktur grafena) untuk seluruh halaman, termasuk hero.
    // Satu kisi saja supaya garisnya benar-benar menyambung; tetap di layar, isi halaman bergulir di atasnya.
    $lebar = 1920; $tinggi = 1200; $sisi = 50;
    $dx = sqrt(3) * $sisi; $dy = 1.5 * $sisi;
    $acak = fn ($x, $y) => ((int) ($x * 10) * 73856093 ^ (int) ($y * 10) * 19349663) & 0x7fffffff;

    $simpul = [];
    $garis = [];
    for ($baris = -1; $baris <= ceil($tinggi / $dy) + 1; $baris++) {
        for ($kolom = -1; $kolom <= ceil($lebar / $dx) + 1; $kolom++) {
            $cx = $kolom * $dx + ($baris % 2 ? $dx / 2 : 0);
            $cy = $baris * $dy;
            $sudut = [];
            for ($k = 0; $k < 6; $k++) {
                $rad = deg2rad(-90 + 60 * $k);
                $sudut[] = [round($cx + $sisi * cos($rad), 1), round($cy + $sisi * sin($rad), 1)];
            }
            foreach ($sudut as $k => [$x, $y]) {
                $simpul["$x,$y"] = [$x, $y];
                [$x2, $y2] = $sudut[($k + 1) % 6];
                $kunci = $x < $x2 || ($x == $x2 && $y < $y2) ? "$x,$y|$x2,$y2" : "$x2,$y2|$x,$y";
                $garis[$kunci] = [$x, $y, $x2, $y2];
            }
        }
    }

    $jalurGaris = '';
    foreach ($garis as [$x1, $y1, $x2, $y2]) {
        $jalurGaris .= "M{$x1} {$y1}L{$x2} {$y2}";
    }

    // Simpul 3–6px: sebagian besar hijau utama, sekitar 15% aksen dengan halo, beberapa simpul besar bercahaya.
    $simpulBiasa = []; $simpulAksen = []; $simpulBesar = [];
    foreach ($simpul as [$x, $y]) {
        $h = $acak($x, $y) % 100;
        $r = 1.5 + ($acak($y, $x) % 4) * 0.5;
        if ($h >= 97) $simpulBesar[] = [$x, $y];
        elseif ($h < 15) $simpulAksen[] = [$x, $y, $r];
        else $simpulBiasa[] = [$x, $y, $r];
    }

    // Jalur aliran energi menyusuri ikatan kisi, di dalam bidang layar.
    $diBidang = fn ($x, $y) => $x > 40 && $x < $lebar - 40 && $y > 40 && $y < $tinggi - 40;
    $tetangga = [];
    foreach ($garis as [$x1, $y1, $x2, $y2]) {
        if (! $diBidang($x1, $y1) || ! $diBidang($x2, $y2)) continue;
        $tetangga["$x1,$y1"][] = [$x2, $y2];
        $tetangga["$x2,$y2"][] = [$x1, $y1];
    }
    $awal = array_values(array_filter($simpul, fn ($s) => isset($tetangga["{$s[0]},{$s[1]}"]) && $acak($s[0], $s[1]) % 7 === 0));
    $langkah = max(1, intdiv(count($awal), 12));
    $aliran = [];
    for ($i = 0; $i < count($awal) && count($aliran) < 12; $i += $langkah) {
        $kini = $awal[$i]; $sebelum = null; $jalur = [$kini];
        for ($l = 0; $l < 20; $l++) {
            $pilihan = array_values(array_filter($tetangga["{$kini[0]},{$kini[1]}"] ?? [], fn ($t) => $t !== $sebelum));
            if (! $pilihan) break;
            $sebelum = $kini;
            $kini = $pilihan[($acak($kini[0], $kini[1]) + $l) % count($pilihan)];
            $jalur[] = $kini;
        }
        if (count($jalur) > 6) $aliran[] = 'M' . implode('L', array_map(fn ($p) => "{$p[0]} {$p[1]}", $jalur));
    }
@endphp

<div class="lp-latar" aria-hidden="true">
    <svg viewBox="0 0 {{ $lebar }} {{ $tinggi }}" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <radialGradient id="lp-cahaya-latar">
                <stop offset="0" class="lp-kisi__stop-terang"/>
                <stop offset="1" class="lp-kisi__stop-pudar"/>
            </radialGradient>
        </defs>
        <path class="lp-latar__garis" d="{{ $jalurGaris }}"/>
        <g class="lp-latar__simpul">
            @foreach ($simpulBiasa as [$x, $y, $r])
                <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $r }}"/>
            @endforeach
        </g>
        <g class="lp-kisi__aliran">
            @foreach ($aliran as $d)
                <path d="{{ $d }}" pathLength="100"/>
            @endforeach
        </g>
        <g class="lp-kisi__halo">
            @foreach ($simpulAksen as [$x, $y, $r])
                <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $r * 3.5 }}"/>
            @endforeach
        </g>
        <g class="lp-kisi__aksen">
            @foreach ($simpulAksen as [$x, $y, $r])
                <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $r }}"/>
            @endforeach
        </g>
        <g>
            @foreach ($simpulBesar as [$x, $y])
                <g class="lp-kisi__bintang">
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="16" fill="url(#lp-cahaya-latar)"/>
                    <circle class="lp-kisi__inti" cx="{{ $x }}" cy="{{ $y }}" r="4"/>
                </g>
            @endforeach
        </g>
    </svg>
</div>
