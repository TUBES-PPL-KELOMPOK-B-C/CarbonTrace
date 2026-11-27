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

// Lonceng notifikasi: panel dibuka-tutup lewat tombol, tertutup saat klik di luar atau tombol Esc.
document.querySelectorAll('[data-notif]').forEach((wadah) => {
    const tombol = wadah.querySelector('[data-notif-tombol]');
    const panel = wadah.querySelector('[data-notif-panel]');
    if (!tombol || !panel) return;

    const atur = (buka) => {
        panel.hidden = !buka;
        tombol.setAttribute('aria-expanded', String(buka));
    };

    tombol.addEventListener('click', () => atur(panel.hidden));
    document.addEventListener('click', (e) => { if (!panel.hidden && !wadah.contains(e.target)) atur(false); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.hidden) {
            atur(false);
            tombol.focus();
        }
    });
});

// Sidebar: posisi gulirnya disimpan sebelum pindah halaman, lalu dipulihkan oleh skrip kecil
// di layouts/aplikasi-111.blade.php, supaya menu tidak melompat kembali ke atas.
const sisi = document.querySelector('.ap-sisi');
if (sisi) {
    window.addEventListener('pagehide', () => {
        try { sessionStorage.setItem('ct-sisi-gulir', String(sisi.scrollTop)); } catch { /* diblokir: abaikan */ }
    });
}

// Akun antartab. Semua tab di satu browser memakai satu cookie login, jadi masuk atau keluar di
// satu tab ikut mengganti akun di tab lain. Tab yang tertinggal tidak diam-diam berganti: ia
// menutup halamannya dengan pemberitahuan dan tombol muat ulang. Kabarnya lewat localStorage
// (kunci "ct-akun"), yang memicu event "storage" di tab lain pada browser yang sama.
const akunTab = { id: document.body.dataset.akunId, nama: document.body.dataset.akunNama };
const kabarkanAkun = (isi) => { try { localStorage.setItem('ct-akun', JSON.stringify({ ...isi, waktu: Date.now() })); } catch { /* diblokir: abaikan */ } };

const tutupTab = (pesan) => {
    if (document.querySelector('.ap-akun-berganti')) return;
    const lapisan = document.createElement('div');
    lapisan.className = 'ap-akun-berganti';
    lapisan.setAttribute('role', 'alertdialog');
    lapisan.setAttribute('aria-modal', 'true');
    lapisan.setAttribute('aria-labelledby', 'ap-akun-berganti-judul');
    lapisan.innerHTML = `
        <div class="ap-akun-berganti__kotak">
            <h2 id="ap-akun-berganti-judul">Akun di tab ini sudah berganti</h2>
            <p></p>
            <p class="ap-akun-berganti__saran">Untuk memakai akun admin dan pengguna bersamaan, buka salah satunya di jendela InPrivate/Incognito.</p>
            <button type="button" class="ct-tombol ct-tombol--utama">Muat ulang</button>
        </div>`;
    lapisan.querySelector('p').textContent = pesan;
    lapisan.querySelector('button').addEventListener('click', () => window.location.reload());
    document.body.append(lapisan);
    lapisan.querySelector('button').focus();
};

if (akunTab.id) {
    kabarkanAkun({ id: akunTab.id, nama: akunTab.nama });

    window.addEventListener('storage', (e) => {
        if (e.key !== 'ct-akun' || !e.newValue) return;
        let baru;
        try { baru = JSON.parse(e.newValue); } catch { return; }
        if (baru.id === akunTab.id) return;
        tutupTab(baru.id
            ? `Di tab lain kamu masuk sebagai ${baru.nama}. Halaman ini masih menampilkan akun ${akunTab.nama} dan tidak bisa dipakai lagi.`
            : `Akun ${akunTab.nama} sudah keluar di tab lain.`);
    });

    document.querySelectorAll('[data-form-keluar]').forEach((form) => {
        form.addEventListener('submit', () => kabarkanAkun({ id: null }));
    });
}
