@props([
    'label' => '',
    'nama' => '',
    'id' => null,
    'satuan' => null,
    'galat' => null,
    'tipe' => 'text',
    'nilai' => null,
    'wajib' => false,
    'placeholder' => '',
])

@php
    $inputId = $id ?? $nama;
    $adaGalat = $galat || $errors->has($nama);
    $pesanGalat = $galat ?? $errors->first($nama);
@endphp

<div class="ct-isian-grup">
    @if($label)
        <label for="{{ $inputId }}" class="ct-label">
            {{ $label }}
            @if($wajib) <span class="text-rose-500">*</span> @endif
        </label>
    @endif

    <div class="{{ $satuan ? 'ct-isian-satuan' : '' }}">
        @if($tipe === 'textarea')
            <textarea
                name="{{ $nama }}"
                id="{{ $inputId }}"
                placeholder="{{ $placeholder }}"
                {{ $attributes->merge(['class' => 'ct-isian' . ($adaGalat ? ' border-rose-500' : ''), 'aria-invalid' => $adaGalat ? 'true' : 'false']) }}
                @if($wajib) required @endif
            >{{ old($nama, $nilai) }}</textarea>
        @else
            <input
                type="{{ $tipe }}"
                name="{{ $nama }}"
                id="{{ $inputId }}"
                value="{{ old($nama, $nilai) }}"
                placeholder="{{ $placeholder }}"
                {{ $attributes->merge(['class' => 'ct-isian' . ($adaGalat ? ' border-rose-500' : ''), 'aria-invalid' => $adaGalat ? 'true' : 'false']) }}
                @if($wajib) required @endif
            />
        @endif

        @if($satuan)
            <span>{{ $satuan }}</span>
        @endif
    </div>

    @if($adaGalat)
        <span class="ct-galat" role="alert">{{ $pesanGalat }}</span>
    @endif
</div>
