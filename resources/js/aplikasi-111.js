// Kerangka aplikasi (layouts.aplikasi-111), dimuat di semua halaman setelah masuk.

// Komponen <x-modal> dan <x-panel-geser> disembunyikan dengan tiga cara sekaligus: kelas "hidden",
// atribut hidden, dan style display:none. Atribut hidden dipaksa tersembunyi oleh Tailwind
// (display:none !important), sehingga skrip yang hanya menghapus kelas "hidden" tidak pernah bisa
// memunculkan jendelanya. Di sini atribut dan style mengikuti kelasnya, apa pun skrip pembukanya.
const selaraskan = (latar) => {
    const sembunyi = latar.classList.contains('hidden');
    if (latar.hasAttribute('hidden') !== sembunyi) latar.toggleAttribute('hidden', sembunyi);
    const display = sembunyi ? 'none' : '';
    if (latar.style.display !== display) latar.style.display = display;
};

document.querySelectorAll('.ct-latar-belakang').forEach((latar) => {
    selaraskan(latar);
    new MutationObserver(() => selaraskan(latar)).observe(latar, { attributes: true, attributeFilter: ['class'] });
});
