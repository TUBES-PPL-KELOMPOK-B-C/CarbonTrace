// Halaman awal publik CarbonTrace — milik Alvin (022).
// Perilaku: menu hamburger, navbar pekat saat digulir, bagian muncul perlahan,
// dan menu aktif yang mengikuti bagian yang sedang dilihat.

const nav = document.querySelector('[data-nav]');
const burger = document.querySelector('[data-nav-burger]');
const menu = document.querySelector('[data-nav-menu]');

// 1. Menu hamburger di layar sempit
if (nav && burger && menu) {
    const atur = (buka) => {
        nav.classList.toggle('lp-nav--buka', buka);
        burger.setAttribute('aria-expanded', String(buka));
        burger.setAttribute('aria-label', buka ? 'Tutup menu' : 'Buka menu');
    };

    burger.addEventListener('click', () => atur(burger.getAttribute('aria-expanded') !== 'true'));

    menu.addEventListener('click', (e) => {
        if (e.target.closest('a')) atur(false);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && burger.getAttribute('aria-expanded') === 'true') {
            atur(false);
            burger.focus();
        }
    });

    document.addEventListener('click', (e) => {
        if (!nav.contains(e.target)) atur(false);
    });

    window.matchMedia('(min-width: 769px)').addEventListener('change', (e) => {
        if (e.matches) atur(false);
    });
}

// 2. Navbar lebih pekat setelah digulir lebih dari 40px; kisi latar meredup mulus selepas hero
const latar = document.querySelector('.lp-latar');
const hero = document.querySelector('.lp-hero');

if (nav) {
    const periksa = () => {
        nav.classList.toggle('lp-nav--pekat', window.scrollY > 40);
        if (latar && hero) {
            const kemajuan = Math.min(1, window.scrollY / hero.offsetHeight);
            latar.style.setProperty('--lp-latar-terang', (1 - 0.55 * kemajuan).toFixed(3));
        }
    };
    periksa();
    window.addEventListener('scroll', periksa, { passive: true });
}

// 4. Menu aktif mengikuti bagian yang sedang dilihat; penanda pelat meluncur ke menu aktif
const daftar = document.querySelector('[data-nav-tautan]');

if (daftar) {
    const tautan = [...daftar.querySelectorAll('a[href^="#"]')];
    const bagian = tautan.map((a) => document.querySelector(a.getAttribute('href'))).filter(Boolean);
    let aktif = null;
    let terkunci = false;
    let waktuKunci;

    const tandai = (a) => {
        if (!a) return;
        if (a !== aktif) {
            tautan.forEach((t) => t.removeAttribute('aria-current'));
            a.setAttribute('aria-current', 'location');
            aktif = a;
        }
        const x = a.getBoundingClientRect().left - daftar.getBoundingClientRect().left;
        daftar.style.setProperty('--penanda-x', `${x}px`);
        daftar.style.setProperty('--penanda-w', `${a.offsetWidth}px`);
        daftar.classList.add('lp-nav__tautan--siap');
    };

    // bagian terakhir yang bagian atasnya sudah melewati 40% tinggi layar; di dasar halaman, bagian terakhir
    const cari = () => {
        if (terkunci) return;
        const garis = window.innerHeight * 0.4;
        const diDasar = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;
        const urut = [...bagian].sort((a, b) => a.offsetTop - b.offsetTop);
        let kini = urut[0];
        urut.forEach((b) => { if (b.getBoundingClientRect().top <= garis) kini = b; });
        if (diDasar) kini = urut[urut.length - 1];
        tandai(tautan.find((a) => a.getAttribute('href') === `#${kini.id}`));
    };

    let antre = false;
    window.addEventListener('scroll', () => {
        if (antre) return;
        antre = true;
        requestAnimationFrame(() => { cari(); antre = false; });
    }, { passive: true });
    window.addEventListener('resize', () => tandai(aktif));
    document.fonts?.ready.then(() => tandai(aktif));

    // saat diklik: langsung aktif, dan penanda tidak ikut berpindah selama gulir halus berjalan
    tautan.forEach((a) => a.addEventListener('click', () => {
        terkunci = true;
        tandai(a);
        clearTimeout(waktuKunci);
        waktuKunci = setTimeout(() => { terkunci = false; cari(); }, 900);
    }));

    cari();
}

// 3. Bagian muncul perlahan saat masuk layar, dilewati bila pengguna meminta gerak dikurangi
const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!kurangiGerak && 'IntersectionObserver' in window) {
    document.documentElement.classList.add('js-muncul');

    const pengamat = new IntersectionObserver((entri) => {
        entri.forEach((e) => {
            if (!e.isIntersecting) return;
            e.target.classList.add('terlihat');
            pengamat.unobserve(e.target);
        });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.1 });

    document.querySelectorAll('[data-muncul]').forEach((el) => pengamat.observe(el));
}
