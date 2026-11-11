// Modul Target dan Pengingat (Alvin-022): buka panel geser dan modal bersama, pintasan periode,
// tombol kirim terkunci, saklar pengingat, dan roda putar jam.

// Panel geser <x-panel-geser>: latar dimunculkan, panel digeser masuk
const bukaPanel = (id) => {
    const latar = document.getElementById(`${id}-backdrop`);
    const panel = document.getElementById(id);
    if (!latar || !panel) return;
    latar.classList.remove('hidden');
    requestAnimationFrame(() => panel.classList.remove('translate-x-full'));
    panel.querySelector('input:not([type="hidden"]), select')?.focus({ preventScroll: true });
};

// Latar <x-panel-geser> menutup panel pada klik apa pun di dalamnya (penjaga event.target di
// komponennya hanya berlaku untuk perintah pertama). Klik di dalam panel tidak diteruskan ke latar,
// jadi panel hanya tertutup lewat tombol tutup, klik di luar panel, atau Esc.
document.querySelectorAll('.ct-panel-geser').forEach((panel) => {
    panel.addEventListener('click', (e) => e.stopPropagation());
});

document.querySelectorAll('[data-buka-panel]').forEach((tombol) => {
    tombol.addEventListener('click', () => bukaPanel(tombol.dataset.bukaPanel));
});

// Setelah validasi gagal, panel yang tadi dikirim dibuka lagi
const panelAwal = document.querySelector('[data-panel-awal]')?.dataset.panelAwal;
if (panelAwal) bukaPanel(panelAwal);

// Modal <x-modal>
document.querySelectorAll('[data-buka-modal]').forEach((tombol) => {
    tombol.addEventListener('click', () => document.getElementById(tombol.dataset.bukaModal)?.classList.remove('hidden'));
});
document.querySelectorAll('[data-tutup-modal]').forEach((tombol) => {
    tombol.addEventListener('click', () => document.getElementById(tombol.dataset.tutupModal)?.classList.add('hidden'));
});

// Esc menutup panel dan modal yang terbuka
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.ct-latar-belakang:not(.hidden)').forEach((latar) => {
        latar.querySelector('.ct-panel-geser')?.classList.add('translate-x-full');
        setTimeout(() => latar.classList.add('hidden'), 200);
    });
});

// Tombol kirim dikunci selama permintaan berjalan supaya tidak tertekan dua kali
document.querySelectorAll('[data-form-kunci]').forEach((form) => {
    form.addEventListener('submit', () => {
        const tombol = form.querySelector('[type="submit"]');
        tombol.disabled = true;
        tombol.setAttribute('aria-busy', 'true');
        tombol.textContent = tombol.dataset.teksMemuat || tombol.textContent;
    });
});

// PBI-10: pintasan "Bulan ini" dan "Bulan depan" mengisi kedua tanggal periode
document.querySelectorAll('[data-mulai]').forEach((tombol) => {
    const form = tombol.closest('form');
    const mulai = form.querySelector('[data-isian-mulai]');
    const selesai = form.querySelector('[data-isian-selesai]');
    const tandai = () => form.querySelectorAll('[data-mulai]').forEach((t) => {
        t.setAttribute('aria-pressed', String(t.dataset.mulai === mulai.value && t.dataset.selesai === selesai.value));
    });

    tombol.addEventListener('click', () => {
        mulai.value = tombol.dataset.mulai;
        selesai.value = tombol.dataset.selesai;
        tandai();
    });
    mulai.addEventListener('change', tandai);
    selesai.addEventListener('change', tandai);
    tandai();
});

// PBI-11: saklar langsung menyimpan saat diubah
document.querySelectorAll('[data-saklar]').forEach((saklar) => {
    saklar.addEventListener('change', () => saklar.form.requestSubmit());
});

// PBI-11: roda putar jam, selaras dua arah dengan kolom ketik
document.querySelectorAll('[data-roda-jam]').forEach((roda) => {
    const isian = roda.closest('form').querySelector('[data-isian-jam]');
    const kolomJam = roda.querySelector('[data-roda="jam"]');
    const kolomMenit = roda.querySelector('[data-roda="menit"]');

    const butirTengah = (kolom) => {
        const tengah = kolom.scrollTop + kolom.clientHeight / 2;
        return [...kolom.children].reduce((terdekat, b) =>
            Math.abs(b.offsetTop + b.offsetHeight / 2 - tengah) < Math.abs(terdekat.offsetTop + terdekat.offsetHeight / 2 - tengah) ? b : terdekat);
    };
    const tandai = (kolom, butir) => [...kolom.children].forEach((b) => b.setAttribute('aria-selected', String(b === butir)));
    const geserKe = (kolom, butir, halus) => {
        kolom.scrollTo({ top: butir.offsetTop - (kolom.clientHeight - butir.offsetHeight) / 2, behavior: halus ? 'smooth' : 'auto' });
        tandai(kolom, butir);
    };

    // Kolom ketik -> roda (menit dibulatkan ke kelipatan lima terdekat di roda saja)
    const dariIsian = (halus) => {
        const m = /^(\d{1,2})[.:](\d{2})$/.exec(isian.value.trim());
        if (!m) return;
        const jam = kolomJam.querySelector(`[data-nilai="${m[1].padStart(2, '0')}"]`);
        const menit = kolomMenit.querySelector(`[data-nilai="${String(Math.min(55, Math.round(Number(m[2]) / 5) * 5)).padStart(2, '0')}"]`);
        if (jam) geserKe(kolomJam, jam, halus);
        if (menit) geserKe(kolomMenit, menit, halus);
    };

    // Roda -> kolom ketik, setelah gulir berhenti
    let jeda;
    let dariPengguna = false;
    const keIsian = () => {
        const jam = butirTengah(kolomJam);
        const menit = butirTengah(kolomMenit);
        tandai(kolomJam, jam);
        tandai(kolomMenit, menit);
        if (dariPengguna) isian.value = `${jam.dataset.nilai}:${menit.dataset.nilai}`;
    };
    [kolomJam, kolomMenit].forEach((kolom) => {
        kolom.addEventListener('pointerdown', () => { dariPengguna = true; });
        kolom.addEventListener('wheel', () => { dariPengguna = true; }, { passive: true });
        kolom.addEventListener('scroll', () => { clearTimeout(jeda); jeda = setTimeout(keIsian, 120); }, { passive: true });
        kolom.addEventListener('click', (e) => {
            const butir = e.target.closest('[data-nilai]');
            if (!butir) return;
            dariPengguna = true;
            geserKe(kolom, butir, true);
        });
    });
    isian.addEventListener('input', () => { dariPengguna = false; dariIsian(true); });

    // Panel tersembunyi tidak punya ukuran, jadi roda disetel saat panelnya pertama terlihat
    new IntersectionObserver((entri, pengamat) => {
        if (entri.some((e) => e.isIntersecting)) {
            dariIsian(false);
            pengamat.disconnect();
        }
    }).observe(roda);
});
