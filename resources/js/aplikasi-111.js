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

// Drill-down menu samping: pilihan buka/tutup tiap kelompok diingat di browser ini (kunci "ct-menu-<kelompok>").
// Pilihan tersimpan diterapkan oleh skrip kecil di layouts/aplikasi-111.blade.php sebelum halaman tampil;
// kelompok yang memuat halaman aktif selalu terbuka supaya posisi pengguna tetap terlihat.
const simpan = (kunci, nilai) => { try { localStorage.setItem(kunci, nilai); } catch { /* penyimpanan diblokir: abaikan */ } };

document.querySelectorAll('details.ap-kelompok[data-kelompok]').forEach((kelompok) => {
    const kunci = `ct-menu-${kelompok.dataset.kelompok}`;

    // Disimpan saat summary diklik (juga Enter/Spasi), bukan lewat event "toggle",
    // karena "toggle" ikut terpicu oleh atribut open bawaan saat halaman dimuat.
    kelompok.querySelector(':scope > summary')?.addEventListener('click', () => simpan(kunci, kelompok.open ? '0' : '1'));
});
