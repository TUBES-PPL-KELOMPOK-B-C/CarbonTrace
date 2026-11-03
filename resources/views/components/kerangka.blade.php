@props([
    'jenis' => 'teks', // teks, kartu, tabel, angka
    'jumlah' => 1,
])

@if($jenis === 'kartu')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @for($i = 0; $i < $jumlah; $i++)
            <div class="ct-kartu p-5 space-y-3">
                <div class="ct-kerangka h-5 w-2/3"></div>
                <div class="ct-kerangka h-8 w-1/2"></div>
                <div class="ct-kerangka h-4 w-1/3"></div>
            </div>
        @endfor
    </div>
@elseif($jenis === 'tabel')
    <div class="space-y-3">
        <div class="ct-kerangka h-10 w-full"></div>
        @for($i = 0; $i < $jumlah; $i++)
            <div class="ct-kerangka h-12 w-full"></div>
        @endfor
    </div>
@elseif($jenis === 'angka')
    <div class="ct-kerangka h-12 w-32"></div>
@else
    <div class="space-y-2">
        @for($i = 0; $i < $jumlah; $i++)
            <div class="ct-kerangka h-4 w-{{ $i % 2 === 0 ? 'full' : '3/4' }}"></div>
        @endfor
    </div>
@endif
