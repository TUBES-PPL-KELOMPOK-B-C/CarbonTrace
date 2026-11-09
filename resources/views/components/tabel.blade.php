@props([
    'kepala' => [], // array of string ['Tanggal', 'Aktivitas', 'Emisi', 'Aksi']
])

<div class="ct-tabel-wadah">
    <table {{ $attributes->merge(['class' => 'ct-tabel']) }}>
        @if(!empty($kepala))
            <thead>
                <tr>
                    @foreach($kepala as $th)
                        <th scope="col" class="{{ str_contains(strtolower($th), 'emisi') || str_contains(strtolower($th), 'jumlah') ? 'angka' : '' }}">
                            {{ $th }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
