{{-- Satu tautan menu sidebar. Rute yang belum terdaftar tampil redup sebagai "segera hadir". --}}
@if (Route::has($rute))
    <a href="{{ route($rute) }}" class="ap-menu__tautan" title="{{ $label }}" @if ($aktifKah) aria-current="page" @endif>
        <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $ikonSvg !!}</svg>
        <span class="ap-menu__label">{{ $label }}</span>
    </a>
@else
    <span class="ap-menu__tautan ap-menu__tautan--nanti" aria-disabled="true" title="{{ $label }} (segera hadir)">
        <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $ikonSvg !!}</svg>
        <span class="ap-menu__label">{{ $label }}</span>
    </span>
@endif
