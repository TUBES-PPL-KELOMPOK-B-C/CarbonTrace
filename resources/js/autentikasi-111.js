// Halaman masuk dan daftar (KF-01): tombol mata, tombol kirim terkunci, kekuatan sandi, fokus ke galat.

// Tombol mata: perlihatkan/sembunyikan isi kolom kata sandi
document.querySelectorAll('[data-lihat-sandi]').forEach((tombol) => {
    const isian = document.getElementById(tombol.dataset.lihatSandi);
    if (!isian) return;

    tombol.addEventListener('click', () => {
        const lihat = isian.type === 'password';
        isian.type = lihat ? 'text' : 'password';
        tombol.setAttribute('aria-pressed', String(lihat));
        tombol.setAttribute('aria-label', lihat ? 'Sembunyikan kata sandi' : 'Perlihatkan kata sandi');
    });
});

// Tombol kirim dikunci selama permintaan berjalan supaya tidak tertekan dua kali
document.querySelectorAll('[data-form-auth]').forEach((form) => {
    form.addEventListener('submit', () => {
        const tombol = form.querySelector('[type="submit"]');
        if (!tombol) return;
        tombol.disabled = true;
        tombol.setAttribute('aria-busy', 'true');
        tombol.textContent = tombol.dataset.teksMemuat || tombol.textContent;
    });
});

// Penanda kekuatan kata sandi saat mendaftar
const sumber = document.querySelector('[data-kekuatan-sumber]');
const penanda = document.getElementById('kekuatan-sandi');

if (sumber && penanda) {
    const label = ['Belum diisi', 'Lemah', 'Sedang', 'Kuat', 'Sangat kuat'];
    const teks = penanda.querySelector('[data-kekuatan-teks]');

    const nilai = (s) => {
        if (!s) return 0;
        let skor = 0;
        if (s.length >= 8) skor++;
        if (/[a-z]/.test(s) && /[A-Z]/.test(s)) skor++;
        if (/\d/.test(s)) skor++;
        if (/[^A-Za-z0-9]/.test(s) || s.length >= 12) skor++;
        return s.length < 8 ? 1 : Math.max(1, skor);
    };

    const perbarui = () => {
        const n = nilai(sumber.value);
        penanda.dataset.kekuatan = String(n);
        teks.textContent = label[n];
    };

    sumber.addEventListener('input', perbarui);
    perbarui();
}

// Galat formulir: pindahkan fokus ke pesannya supaya pembaca layar membacakannya
const galat = document.querySelector('[data-galat-fokus]') || document.querySelector('.lp-auth__form [aria-invalid="true"]');
galat?.focus();
