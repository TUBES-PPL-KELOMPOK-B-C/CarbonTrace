// Modul Target dan Pengingat (Alvin-022): buka panel geser dan modal bersama, kartu target berjalan
// dari API, pintasan periode, tombol kirim terkunci, saklar pengingat, dan roda putar jam.

// Fokus: selama panel atau modal terbuka, Tab berputar di dalamnya; setelah ditutup, fokus kembali
// ke tombol yang membukanya.
let pembuka = null;
const bisaFokus = 'a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';

document.querySelectorAll('.ct-latar-belakang').forEach((latar) => {
    new MutationObserver(() => {
        if (latar.classList.contains('hidden') && pembuka) {
            pembuka.focus();
            pembuka = null;
        }
    }).observe(latar, { attributes: true, attributeFilter: ['class'] });
});

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Tab') return;
    const terbuka = document.querySelector('.ct-latar-belakang:not(.hidden)');
    if (!terbuka) return;
    const daftar = [...terbuka.querySelectorAll(bisaFokus)].filter((el) => el.offsetParent !== null);
    if (!daftar.length) return;
    const [awal, akhir] = [daftar[0], daftar[daftar.length - 1]];
    if (e.shiftKey && (document.activeElement === awal || !terbuka.contains(document.activeElement))) {
        e.preventDefault();
        akhir.focus();
    } else if (!e.shiftKey && (document.activeElement === akhir || !terbuka.contains(document.activeElement))) {
        e.preventDefault();
        awal.focus();
    }
});

// Panel geser <x-panel-geser>: latar dimunculkan, panel digeser masuk
const bukaPanel = (id) => {
    const latar = document.getElementById(`${id}-backdrop`);
    const panel = document.getElementById(id);
    if (!latar || !panel) return;
    pembuka = document.activeElement;
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
    tombol.addEventListener('click', () => {
        const modal = document.getElementById(tombol.dataset.bukaModal);
        if (!modal) return;
        pembuka = tombol;
        modal.classList.remove('hidden');
        modal.querySelector('[data-tutup-modal]')?.focus();
    });
});

// PBI-10: kartu target berjalan diisi dari GET /api/target/berjalan
const kartuTarget = document.querySelector('[data-target-berjalan]');

if (kartuTarget) {
    const angka = (n) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(n);
    const labelStatus = { aman: 'Aman', hampir: 'Hampir', terlampaui: 'Terlampaui' };
    const cincin = kartuTarget.querySelector('[data-cincin]');
    const pelat = kartuTarget.querySelector('[data-pelat]');
    const teks = kartuTarget.querySelector('[data-teks]');
    const gagal = kartuTarget.querySelector('[data-gagal]');

    const muat = async () => {
        kartuTarget.classList.add('tg-berjalan--memuat');
        kartuTarget.setAttribute('aria-busy', 'true');
        gagal.hidden = true;
        teks.hidden = false;
        teks.textContent = 'Memuat sisa kuota…';

        try {
            const jawaban = await fetch(kartuTarget.dataset.api, { headers: { Accept: 'application/json' } });
            if (!jawaban.ok) throw new Error(String(jawaban.status));
            const d = await jawaban.json();
            if (!d.ada_target) {
                window.location.reload();
                return;
            }

            cincin.className = `ct-cincin tg-cincin tg-cincin--${d.status}`;
            // Animasi cincin dari nol ke nilai sebenarnya (CSS @property --nilai)
            requestAnimationFrame(() => cincin.style.setProperty('--nilai', Math.min(d.persen, 100)));
            cincin.querySelector('[data-persen]').textContent = `${angka(d.persen)}%`;
            pelat.className = `ct-pelat ct-pelat--${d.status}`;
            pelat.textContent = labelStatus[d.status];
            pelat.hidden = false;

            const rincian = d.sisa >= 0
                ? `Sisa ${angka(d.sisa)} kg untuk ${d.sisa_hari} hari.`
                : `Terlampaui ${angka(Math.abs(d.sisa))} kg, masih ${d.sisa_hari} hari lagi.`;
            teks.innerHTML = '';
            teks.append(`${angka(d.terpakai)} dari ${angka(d.batas)} kg CO2e. `);
            const panjang = document.createElement('span');
            panjang.className = 'tg-panjang';
            panjang.textContent = rincian;
            teks.append(panjang);
        } catch {
            teks.hidden = true;
            gagal.hidden = false;
        } finally {
            kartuTarget.classList.remove('tg-berjalan--memuat');
            kartuTarget.removeAttribute('aria-busy');
        }
    };

    kartuTarget.querySelector('[data-coba-lagi]').addEventListener('click', muat);
    muat();
}
document.querySelectorAll('[data-tutup-modal]').forEach((tombol) => {
    tombol.addEventListener('click', () => document.getElementById(tombol.dataset.tutupModal)?.classList.add('hidden'));
});

// Esc menutup panel dan modal yang terbuka. Didengar pada fase capture, karena kolom tanggal
// bawaan peramban menelan Esc sebelum sampai ke dokumen.
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.ct-latar-belakang:not(.hidden)').forEach((latar) => {
        latar.querySelector('.ct-panel-geser')?.classList.add('translate-x-full');
        setTimeout(() => latar.classList.add('hidden'), 200);
    });
}, true);

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

// PBI-11: keadaan memuat. Selama perubahan apa pun dikirim, daftar pengingat diganti tiga kartu
// kerangka sampai halaman baru tampil.
const kisiPengingat = document.querySelector('[data-kisi-pengingat]');
const kerangkaPengingat = document.querySelector('[data-kerangka-pengingat]');

if (kisiPengingat && kerangkaPengingat) {
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented || !e.target.closest('main')) return;
        kisiPengingat.hidden = true;
        kerangkaPengingat.hidden = false;
        kerangkaPengingat.setAttribute('aria-busy', 'true');
    });
}

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

// PBI-12: pratinjau laporan ikut berubah saat rentang tanggal atau kategori diubah
const pratinjau = document.querySelector('[data-pratinjau]');
const formLaporan = document.querySelector('[data-form-laporan]');

if (pratinjau && formLaporan) {
    const angkaId = (n, d = 2) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d }).format(n);
    const isi = (kunci, teks) => { pratinjau.querySelector(`[data-${kunci}]`).textContent = teks; };
    const tampilkan = (keadaan) => pratinjau.querySelectorAll('[data-keadaan]').forEach((el) => { el.hidden = el.dataset.keadaan !== keadaan; });

    const gambar = (d) => {
        isi('total', angkaId(d.total));
        isi('rata', angkaId(d.rata_harian));
        isi('jumlah', d.jumlah_catatan);
        isi('halaman', d.perkiraan_halaman);
        pratinjau.querySelector('[data-tanpa-catatan]').hidden = d.jumlah_catatan > 0;

        const puncak = Math.max(...d.tren.map((t) => t.emisi)) || 1;
        pratinjau.querySelector('[data-garis]').setAttribute('points', d.tren.map((t, i) =>
            `${d.tren.length > 1 ? (i / (d.tren.length - 1)) * 600 : 300},${150 - (t.emisi / puncak) * 140}`).join(' '));

        pratinjau.querySelector('[data-baris]').replaceChildren(...d.per_kategori.map((k) => {
            const baris = document.createElement('tr');
            [['Kategori', k.nama, ''], ['Emisi (kg)', angkaId(k.emisi), 'angka'], ['Porsi', `${angkaId(k.persen, 1)}%`, 'angka']].forEach(([label, teks, kelas]) => {
                const sel = document.createElement('td');
                sel.dataset.label = label;
                sel.className = kelas;
                sel.textContent = teks;
                baris.append(sel);
            });
            return baris;
        }));
    };

    let urutan = 0; // jawaban lama yang datang terlambat diabaikan
    const muat = async () => {
        const ke = ++urutan;
        const data = new FormData(formLaporan);
        formLaporan.querySelectorAll('[data-galat]').forEach((p) => { p.textContent = ''; });
        tampilkan('memuat');

        try {
            const jawaban = await fetch(formLaporan.dataset.api, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': data.get('_token') },
                body: JSON.stringify({ period_start: data.get('period_start'), period_end: data.get('period_end'), kategori: data.getAll('kategori[]') }),
            });
            const d = await jawaban.json();
            if (ke !== urutan) return;

            if (jawaban.status === 422) {
                Object.entries(d.errors).forEach(([kolom, pesan]) => {
                    const galat = formLaporan.querySelector(`[data-galat="${kolom}"]`);
                    if (galat) [galat.textContent] = pesan;
                });
                isi('pesan', 'Perbaiki isian di formulir untuk melihat pratinjau.');
                tampilkan('galat');
                return;
            }
            if (!jawaban.ok) throw new Error(String(jawaban.status));
            gambar(d);
            tampilkan('isi');
        } catch {
            if (ke !== urutan) return;
            isi('pesan', 'Pratinjau gagal dimuat.');
            tampilkan('galat');
        }
    };

    let jeda;
    const tunda = () => { clearTimeout(jeda); jeda = setTimeout(muat, 300); };
    formLaporan.addEventListener('change', (e) => { if (e.target.name !== 'title') tunda(); });
    formLaporan.querySelectorAll('[data-mulai]').forEach((tombol) => tombol.addEventListener('click', tunda));
    pratinjau.querySelector('[data-coba-lagi]').addEventListener('click', muat);
    muat();
}
